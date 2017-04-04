var MultiListSelect = function(options){
    var self = this;
    self.arrowImg = 'templates/admin/images/select_arrow_closing.png';
    self.items = [];
    self.model = [];
    self.selectedItems = [];
    self.header = '';
    self.options = angular.extend({onChange: function(){}}, options);
    self.allSelected = false;

    self.getSelectedItems = getSelectedItems;
    self.setItems = setItems;
    self.selectItem = selectItem;
    self.selectAll = selectAll;

    function selectItem(index){
        if (self.selectedItems.indexOf(index) == -1){
            self.selectedItems.push(index);
        }else{
            self.selectedItems.splice(self.selectedItems.indexOf(index), 1);
        }
        self.options.onChange();
        self.allSelected = self.selectedItems.length == self.model.length;
    }

    function setItems(items){
        self.items = items;
        self.model = self.items.map(function(item){return false});
    }

    function getSelectedItems(){
        var result=[];
        for(var i=0;i<self.model.length;i++){
            if (self.model[i]){
                result.push(self.items[i]);
            }
        }
        return result;
    }

    function selectAll(value){
        if (typeof value === 'boolean'){self.allSelected = value;}
        if (self.allSelected){
            self.model = self.model.map(function(item){return true;});
            self.selectedItems = self.model.map(function(item,i,a){return i;});
        }
        self.options.onChange();
    }
};

var OrderDocumentsDaysListController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;

    self.onDateChange = onDateChange;
    self.updateList = updateList;
    self.selectDay = selectDay;

    self.filters = {MinDate:null,MaxDate:null,Status:null};
    self.activeCity = {Id:0};
    self.list = [];
    self.orderStatuses = ['Выполнен','Выполняется','Отправлен на кухню'];
    self.listInUpdate = false;

    $scope.$on('Order.Documents.CityChange', onCityChange);
    activate();
    function activate(){
        var startDate = moment(), endDate = moment().date(startDate.date()+7), statusFilter;
        statusFilter = new MultiListSelect({onChange:function(){
            if(statusFilter.selectedItems.length >= 1){
                statusFilter.header = statusFilter.selectedItems.length == statusFilter.model.length ? 'Все элементы' : statusFilter.getSelectedItems().join(', ');
            }else{
                statusFilter.header = 'Не выбрано ни одного статуса';
            }
        }});
        statusFilter.header = 'Все статусы';
        statusFilter.setItems(self.orderStatuses);
        statusFilter.selectAll(true);

        self.filters = {MinDate:startDate,MaxDate:endDate, Status:statusFilter};

    }

    function updateList(){
        self.listInUpdate = true;
        BackendService.get({module:'Food', controller:'Admin\\Documents', action:'getActiveOrderDays', CityId:self.activeCity.Id,
            MinDate:self.filters.MinDate.format('YYYY-MM-DD'),
            MaxDate:self.filters.MaxDate.format('YYYY-MM-DD'),
            Status:angular.toJson(self.filters.Status.getSelectedItems())
        }).then(function(response){
            self.listInUpdate = false;
            var responseData = response.data;
            if (responseData.status === 'OK'){
                self.list = responseData.data;
                for (var i=0;i<self.list.length;i++){
                    self.list[i].Moment = moment(self.list[i].Date);
                }
            }else{
                console.error('Cant load days list', responseData);
            }
        });
    }

    function onDateChange(){
        //updateList();
    }

    function onCityChange(event, data){
        var City = data.City;
        if (self.activeCity.Id == City.Id){
            return;
        }
        self.activeCity = City;
        updateList();
    }

    function selectDay(day){
        $rootScope.$broadcast('Order.Documents.DaysList.DaySelected', {Day:day});
    }

};
adminApp.controller('order.documents.DaysList', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrderDocumentsDaysListController]);
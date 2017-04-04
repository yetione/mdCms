var OrderDocumentsCouriersListController = function($scope, $rootScope, BackendService, EntityFactory, $idialog, Notification) {
    var self = this;
    self.cityManager = EntityFactory('City');
    self.cities = [];
    self.phoneMask = '+7 (999) 999-99-99';

    self.filters = {City:null,Date:{Start:null,End:null}, Status:null};
    self.loading = {Cities:false, List:false};
    self.updateDatesList = updateDatesList;
    self.selectDay = selectDay;
    self.onCitySelect = onCitySelect;

    activate();
    function activate(){
        self.filters.Date.Start = moment();
        self.filters.Date.End = moment().date(self.filters.Date.Start.date()+7);
        self.loading.Cities = true;
        self.cityManager.getAll().then(function(cities){
            self.cities = cities;
            self.filters.City = self.cities[0];
            self.updateDatesList();
            self.loading.Cities = false;
        },function (resp) {self.loading.Cities = false;});
        self.datesList = new ListObject({
            onUpdateItems:function(){

            }
        });
    }

    function onCitySelect($item, $event, $isNull) {
        self.updateDatesList();
    }

    function updateDatesList() {
        self.loading.List = true;
        BackendService.get({module:'Food', controller:'Admin\\Documents', action:'getActiveOrderDays', CityId:self.filters.City.Id,
            MinDate:self.filters.Date.Start.format('YYYY-MM-DD'),
            MaxDate:self.filters.Date.End.format('YYYY-MM-DD'),
            Status:angular.toJson([])
        }).then(function(response){
            var responseData = response.data;
            if (responseData.status === 'OK'){
                self.list = responseData.data;
                for (var i=0;i<self.list.length;i++){
                    self.list[i].Moment = moment(self.list[i].Date);
                }
            }else{
                Notification.error({message:'Ошибка при загрузке списка дней.', delay: 1000, positionY: 'bottom', positionX: 'right'});
                console.error('Cant load days list', responseData);
            }
            self.loading.List = false;
        },function (resp) {self.loading.List = false;});
    }

    function selectDay(day) {
        BackendService.get({module:'Food', controller:'Admin\\Order', action:'selectOrdersToDay', Date:day.Date, CityId:self.filters.City.getActiveItem().Id}).then(function(response){
            var responseData = response.data;
            if(responseData.status == 'OK'){
                var odList  = responseData.data;
                for (var i=0;i<odList.length;++i){
                    if(odList[i].CourierId != 0){
                        odList[i].Courier = self.couriersManager.getById(odList[i].CourierId);
                    }
                }
                self.odList = odList;
            }else{
                Notification.error({message:'Ошибка при загрузке списка заказов.', delay:1000, positionY:'bottom', positionX:'right'});
                console.error(responseData);
            }
        });
    }

    function setActiveCity(city) {
        self.activeCity = city;
    }



};

adminApp.controller('order.manage.daysList', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', 'Notification', OrderDocumentsCouriersListController]);
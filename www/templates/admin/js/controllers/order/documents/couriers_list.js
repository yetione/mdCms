var OrderDocumentsCouriersListController = function($scope, $rootScope, BackendService, EntityFactory, $idialog, Notification) {
    var self = this;
    self.cityManager = EntityFactory('City');
    self.odManager = EntityFactory('OrderDay');
    self.couriersManager = EntityFactory('Courier');
    self.couriersList = [];
    self.cities = [];
    self.odList = [];
    self.activeCity = null;
    self.phoneMask = '+7 (999) 999-99-99';

    self.filters = {City:null,Date:{Start:null,End:null}, Status:null};
    self.updateDatesList = updateDatesList;
    self.selectDay = selectDay;
    self.selectOd = selectOrderDay;
    self.setCourier = setCourier;
    self.printDoc = printDoc;
    self.onCitySelect = onCitySelect;

    activate();
    function activate(){
        self.filters.City = new ListObject({
            onItemSelect: function () {
                setActiveCity(self.filters.City.getActiveItem());
                self.updateDatesList();
            }
        });
        self.filters.Date.Start = moment();
        self.filters.Date.End = moment().date(self.filters.Date.Start.date()+7);
        self.cityManager.getAll().then(function(cities){
            self.cities = cities;
            self.filters.City.setItems(self.cities);
            self.filters.City.setActiveItem(0);
        });
        self.couriersManager.getAll().then(function(couriers){
            self.couriersList = couriers;
        });
        self.datesList = new ListObject({
            onUpdateItems:function(){

            }
        });
    }

    function onCitySelect($item, $event, $isNull) {
        self.updateDatesList();
    }

    function updateDatesList() {
        BackendService.get({module:'Food', controller:'Admin\\Documents', action:'getActiveOrderDays', CityId:self.activeCity.Id,
            MinDate:self.filters.Date.Start.format('YYYY-MM-DD'),
            MaxDate:self.filters.Date.End.format('YYYY-MM-DD'),
            Status:angular.toJson([])
        }).then(function(response){
            self.listInUpdate = false;
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
        });
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
        /*self.odManager.getList({DeliveryDate:day.Date}).then(function(response){
            console.log('OD', response);
            self.odList = response;
        });*/
    }

    function selectOrderDay(od) {

    }

    function setActiveCity(city) {
        self.activeCity = city;
    }

    function setCourier(orderDay) {
        var couriers = self.couriersList.filter(function(item, i, arr){
            return item.CityId == self.activeCity.Id;
        });
        $idialog('order/documents/select_courier.html', {dialogId:'selectCourierDialog', options:{List:couriers, CurrentId:orderDay.CourierId,
            onSelect:function(item, scope){
                if (item === null){
                    delete orderDay.Courier;
                    orderDay.CourierId = 0;
                }else{
                    orderDay.Courier = item;
                    orderDay.CourierId = item.Id;
                }
                self.odManager.saveItem(orderDay).then(function (entity) {
                    Notification.success({message:'Заказ обновлен.', delay:1000, positionY:'bottom', positionX:'right'});
                });
                scope.hide();
            }
        }});
    }

    function printDoc(orderDay) {

    }


};

adminApp.controller('order.documents.CouriersList', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', 'Notification', OrderDocumentsCouriersListController]);
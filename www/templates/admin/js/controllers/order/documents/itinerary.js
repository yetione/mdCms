var OrderDocumentsItineraryItemController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;
    self.activeCity = {Id:0};
    self.activeDay = {Date:''};
    self.couriersManager = EntityFactory('Courier');
    self.citiesManager = EntityFactory('City');
    self.couriersToCity = {};
    self.orders = [];
    self.couriersInUpdate = false;
    self.checkLink = '';

    self.editCourier = editCourier;
    self.addOrder = addOrder;
    self.newList = newList;
    self.generatePDF = generatePDF;
    self.deleteOrder = deleteOrder;

    activate();
    function activate(){
        self.couriersList = new ListObject({
            onItemSelect:function(){
                self.couriersList.header = self.couriersList.getActiveItem().Name;
            },
            onSetNull:function(){
                self.couriersList.header = 'Курьер не выбран';
                self.couriersList.activeItem = -1;
            }
        });
        self.couriersList.header = 'Курьер не выбран';
    }

    $scope.$on('Order.Documents.CityChange', onCityChange);
    function onCityChange(event, data){
        var City = data.City;
        if (self.activeCity.Id == City.Id){return;}
        self.activeCity = City;
        self.activeDay = {Date:''};
        updateCouriersList();
        self.couriersList.activeItem = -1;
        self.couriersList.header = 'Курьер не выбран';
        self.checkLink = '';
    }

    function updateCouriersList(){
        if (!(self.activeCity.Id in self.couriersToCity)){
            self.couriersInUpdate = true;
            self.couriersManager.getList({CityId:self.activeCity.Id}).then(function(list){
                self.couriersInUpdate = false;
                self.couriersToCity[self.activeCity.Id] = list;
                self.couriersList.setItems(self.couriersToCity[self.activeCity.Id]);
            });
        }else{
            self.couriersInUpdate = false;
            self.couriersList.setItems(self.couriersToCity[self.activeCity.Id]);
        }
    }

    $scope.$on('Order.Documents.DaysList.DaySelected', onDaySelected);
    function onDaySelected(event, data){
        var Day = data.Day;
        if (self.activeDay.Date == Day.Date){return;}
        self.activeDay = Day;
        self.checkLink = '';
    }

    function editCourier(entity){
        var citiesList = new ListObject({
            onItemSelect:function(index){
                citiesList.header = citiesList.items[index].Name;
            }
        });
        self.citiesManager.getAll().then(function(list){
            citiesList.setItems(list);
            citiesList.setActiveItem(0);
            if (entity.CityId > 0){
                for (var i=0;i<citiesList.items.length;i++){
                    if (citiesList.items[i].Id == entity.CityId){
                        citiesList.setActiveItem(i);
                        break;
                    }
                }
            }
            citiesList.header = citiesList.getActiveItem().Name
        });
        entity = angular.extend({Id:0,CityId:0}, entity);
        $idialog('edit-courier-dialog',{dialogId:'editCourierDialog',options:{
            entity:entity,
            cities: citiesList,
            formSubmit:function($scope){
                $scope.hide();
                $scope.dialogCtrl.options.entity.CityId = citiesList.getActiveItem().Id;
                var entity = $scope.dialogCtrl.options.entity;
                if (entity.Id == 0){delete  entity.Id;}
                BackendService.send({entity:entity}, {module:'Restful', controller:'Courier', action:'saveItem'}).then(function(response){
                    var responseData = response.data, message;
                    if (responseData.status == 'OK'){
                        message = 'Данные обновлены.';
                        delete self.couriersToCity[responseData.data.CityId];
                        updateCouriersList();
                    }else{
                        message = 'Ошибка при обновлении.';
                        console.error('Cant update courier.', response);
                    }
                    $idialog('message-dialog',{dialogId:'messageDialog', options:{message:message}});
                });
            },
            remove:function($rScope){
                $idialog('confirm-dialog',{
                    dialogId: 'deleteProduct',
                    options:{message:'Удалить курьера?', yesCb:function($scope){
                        BackendService.get({module:'Restful',controller:'Courier',action:'deleteItem', id:$rScope.dialogCtrl.options.entity.Id}).then(function(response){
                            var responseData = response.data;
                            $scope.hide();
                            if (responseData.status == 'OK'){
                                $rScope.hide();
                                delete self.couriersToCity[$rScope.dialogCtrl.options.entity.CityId];
                                updateCouriersList();
                                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Курьер удален.'}});
                            }else{
                                console.error(response);
                                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Ошибка при удалении.'}});
                            }
                        });
                    }}
                });
            },
            hide:function($scope){
                $scope.hide();
            }

        }});
    }

    function addOrder(){
        $idialog('order/documents/orders_list.html', {dialogId:'ordersListDialog', options:{Day:self.activeDay, City:self.activeCity,
            onAdd:function(items){
                self.orders = self.orders.concat(items);
            }
        }});
    }

    function generatePDF(){
        //console.log(self.activeDay);
        /*BackendService.get({module:'Food', controller:'Admin\\Documents', action:'generateTrackList',
            Orders:angular.toJson(self.orders.map(function(item, i,a){
                return item['Id'];
            })),
            Courier:self.couriersList.getActiveItem()['Id'],
            Date:self.activeDay.Moment.format('DD.MM.YYYY'),
            City:self.activeCity['Id']
        }).then(function(response){
            var responseData = response.data;
            if (responseData.status == 'OK'){
                console.log(responseData);
                self.checkLink = responseData.data.Path;
            }
        });*/

        self.checkLink = BackendService.buildUrl('json.php',{module:'Food', controller:'Admin\\Documents', action:'generateTrackListPDF',
            Orders:angular.toJson(self.orders.map(function(item, i,a){
                return item['Id'];
            })),
            Courier:self.couriersList.getActiveItem()['Id'],
            Date:self.activeDay.Moment.format('DD.MM.YYYY'),
            City:self.activeCity['Id']
        });

    }

    function deleteOrder(i){
        self.orders.splice(i, 1);
    }

    function newList(){
        self.orders = [];
        self.checkLink = '';
        self.couriersList.setNull();
    }
};

adminApp.controller('order.documents.ItineraryItem', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrderDocumentsItineraryItemController]);
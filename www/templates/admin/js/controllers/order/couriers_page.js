var OrderCouriersPageController = function($scope, $rootScope, BackendService, EntityFactory, $idialog, Notification, $q, $timeout) {
    var self = this;

    self.citiesManager = EntityFactory('City');
    self.couriersManager = EntityFactory('Courier');
    self.couriersList = [];
    self.emptyItem = {};

    self.cities = [];
    self.table = {
        activeCity:null,
        list:[],
        updating:false,
        text:'',
        onCitySelect:function($item, $event, $isNull){
            this.activeCity = $item;
            this.updateList();
        },
        updateList:function () {
            this.list = self.couriersList.filter(function(item, i, arr){
                return item.CityId == self.table.activeCity.Id;
            });
        }
    };

    self.deleteItem = deleteItem;
    self.saveItem = saveItem;
    self.editItem = editItem;

    activate();
    function activate() {
        self.table.updating = true;
        self.citiesManager.getAll().then(function(cities){
            self.couriersManager.getAll().then(function(couriers){
                self.couriersList = couriers;
                self.cities = cities;
                self.table.onCitySelect(self.cities[0]);
                self.table.updating = false;
                self.couriersManager.getEmpty().then(function (entity) {
                    entity.Id = 0;
                    entity.CityId = 0;
                    entity.Name = '';
                    self.emptyItem = entity;
                });
            });
        });
    }

    function saveItem(entity) {
        var deferred = $q.defer();
        var f = function () {
            self.table.updating = true;
            self.couriersManager.saveItem(entity).then(function(result){
                self.table.updateList();
                self.table.updating = false;
                Notification.success({message:'Данные о курьере сохранены.', delay: 1000, positionY: 'bottom', positionX: 'right'});
                deferred.resolve();
            },function (response) {
                self.table.updating = false;
                Notification.error({message:'Ошибка при сохранении.', delay: 1000, positionY: 'bottom', positionX: 'right'});
                console.error('OrderCouriersPageController::saveActiveItem: error in save stock.', response)
                deferred.reject();
            });
        };
        $timeout(f);
        return deferred.promise;
    }

    function deleteItem(item) {
        var deferred = $q.defer();
        var f = function(){
            $idialog('confirm-dialog',{dialogId:'deleteCourier',options:{
                message:'Удалить курьера?',
                yesCb:function(scope){
                    self.table.updating = true;
                    self.couriersManager.deleteItem(item).then(function(response){
                        self.table.updateList();
                        self.table.updating = false;
                        Notification.success({message:'Курьер удален.', delay: 1000, positionY: 'bottom', positionX: 'right'});
                        deferred.resolve();
                        scope.hide();
                    }, function (response) {
                        self.table.updating = false;
                        console.error('Cant delete courier', response);
                        Notification.error({message:'Во время удаления возникла ошибка.', delay: 1000, positionY: 'bottom', positionX: 'right'});
                        scope.hide();
                    });
                },
                noCb:function(scope){
                    scope.hide();
                },
                onHide:function (scope) {
                    deferred.reject();
                }
            }});
        };
        $timeout(f);
        return deferred.promise;
    }

    function editItem(entity){
        entity = !entity ? angular.copy(self.emptyItem) : angular.copy(entity);
        $idialog('order/couriers/edit',{dialogId:'editCourierDialog',options:{
            entity:entity,
            phoneMask:'+7 (999) 999-99-99',
            updating:false,
            city:{
                list:self.cities,
                activeCity:null,
                form:null,
                nullItem:{Id:0,Name:'Не указан'},
                onCitySelect:function ($item, $event, $isNull) {
                    if ($item === null) $item = this.nullItem;
                    this.activeCity = $item;
                    entity.CityId = $item.Id;
                    this.form.$setDirty();
                }
            },
            formSubmit:function($scope){
                var dialog = this;
                if (dialog.updating){
                    console.log('Форма уже отправляется.');
                    Notification.warning({message:'Запрос обрабатывается...', delay: 1000, positionY: 'top', positionX: 'right'});
                    return false;
                }
                $scope.dialogCtrl.options.entity.CityId = this.city.activeCity.Id;
                var entity = $scope.dialogCtrl.options.entity;
                if (entity.Name.trim() == '' || !entity.CityId){
                    Notification.warning({message:'Не заполнены обязательные поля.', delay: 1000, positionY: 'top', positionX: 'right'});
                    return false;
                }
                dialog.updating = true;
                self.saveItem(entity).then(function(){
                    dialog.updating = false;
                    $scope.EntityEditForm.$setPristine();
                    $scope.hide();
                },function(){
                    dialog.updating = false;
                    $scope.EntityEditForm.$setPristine();
                });
            },
            remove:function($rScope){
                var dialog = this;
                dialog.updating = true;
                self.deleteItem(entity).then(function () {
                    dialog.updating = false;
                    $rScope.hide();
                }, function(){
                    dialog.updating = false;
                });
            },
            cancel:function($scope){
                $scope.EntityEditForm.$setPristine();
                $scope.hide();
            },
            beforeHide: function ($scope, deferred) {
                if (this.city.form.$dirty ){
                    $idialog('confirm-dialog',{dialogId:'resumeWithoutSave',options:{
                        message:'Данные были изменены. Продолжить без сохранения?',
                        yesCb:function(scope){
                            deferred.resolve();
                            scope.hide();
                        }
                    }});
                    return false;
                }
                deferred.resolve();
            },
            onShow:function($scope){
                this.city.form = $scope.EntityEditForm;
                if (!entity.CityId){
                    this.city.onCitySelect(null);
                }
                else {
                    for(var i=0;i<self.cities.length;++i){
                        if (entity.CityId == self.cities[i].Id){
                            this.city.onCitySelect(self.cities[i]);
                        }
                    }
                }
                $scope.EntityEditForm.$setPristine();
            }
        }});
    }
};
adminApp.controller('order.couriersPage', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', 'Notification', '$q', '$timeout', OrderCouriersPageController]);
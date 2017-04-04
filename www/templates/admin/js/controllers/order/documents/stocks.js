var OrderDocumentsStocksController = function($scope, $rootScope, BackendService, EntityFactory, $idialog, Notification, KladrService, GeoLocationService){
    var self = this;

    self.citiesManager = EntityFactory('City');
    self.stocksManager = EntityFactory('Stock');
    self.stocksList = [];

    self.list = [];
    self.activeItem = null;
    self.emptyItem = {};
    self.predictItems = {Street:{Items:[]}, MetroStation:{Items:[], ShowList:true}};
    self.itemsLoading = {Stocks:false};
    self.cityMetroStations = {};
    self.updateList = updateList;
    self.editItem = editItem;
    self.deleteItem = deleteItem;
    self.saveActiveItem = saveActiveItem;
    //self.metroStationsChange = metroStationsChange;
    self.selectMetroStation = selectMetroStation;
    self.selectStreet = selectStreet;
    activate();
    function activate(){
        self.citiesList = new ListObject({
            onItemSelect: function () {
                updateList();
            }
        });
        self.formCitiesList = new ListObject({
            onItemSelect:function(){
                /*var item = self.formCitiesList.getActiveItem();
                if (!(item.Machine in self.cityMetroStations)){
                    self.cityMetroStations[item.Machine] = GeoLocationService.getCityMetroStation(item);
                    self.cityMetroStations[item.Machine].sort();
                }
                console.log('city select', self.activeItem);

                self.activeItem.MetroStationsList = self.cityMetroStations[item.Machine];*/
                if (self.activeItem === null){self.activeItem = {}}
                self.activeItem.MetroStationsList = getCityMetroStations(self.formCitiesList.getActiveItem());
                self.activeItem.MetroStation = '';
                self.activeItem.Street = '';
                self.activeItem.CityId = self.formCitiesList.getActiveItem().Id;
                $scope.StockEditForm.$setDirty();
            }
        });


        self.itemsLoading.Stocks = true;
        self.citiesManager.getAll().then(function (cities) {

            self.stocksManager.getAll().then(function(stocks){
                self.citiesList.setItems(cities);
                self.citiesList.setActiveItem(0);
                self.stocksList = stocks;
                self.itemsLoading.Stocks = false;
                updateList();
                self.formCitiesList.setItems(cities);
                self.stocksManager.getEmpty().then(function(entity){
                    entity = angular.extend(entity, {Id:0, Name:'', CityId:0, Street:'', Building:'', Room:''});
                    self.emptyItem = entity;
                    if (self.activeItem === null){
                        self.editItem(self.emptyItem);
                    }
                });
            });



        });

        self.metroStationsAutocomplete = {
            Opening:false,
            Items:[],
            onTextChange:function(text){
                if (!text || text.length < 2 || self.activeItem == null) return [];
                self.activeItem.MetroStationsList = getCityMetroStations(self.formCitiesList.getActiveItem());
                return self.activeItem.MetroStationsList.filter(function (item, i, arr) {
                    return item.toLowerCase().indexOf(self.activeItem.MetroStation.toLowerCase()) != -1;
                });
            }
        };
        self.streetAutocomplete = {
            Opening:false,
            Items:[],
            onTextChange:function (text) {
                if (!text || text.length < 2) return [];
                var query = KladrService.getQuery({query:text, contentType:KladrService.type.street, cityId:self.formCitiesList.getActiveItem().Okato, withParent:true}),
                    object = this;
                KladrService.execute(query).then(function(response){
                    object.Items = response.result.map(function(item, i, arr){
                        item.Label = item.typeShort+'. '+item.name;
                        if (item.parents && item.parents.length){
                            var l = '';
                            for (var k=item.parents.length-1;k>-1;--k){
                                if (item.parents[k].name && item.parents[k].contentType == 'city') {
                                    l += item.parents[k].typeShort + '. ' + item.parents[k].name;
                                }
                            }
                            item.City = l == '' ? '' : '('+l+')';
                        }
                        return item;
                    });
                });
                return [];
            }
        };
    }

    function editItem(item) {
        if (!item){
            item = self.emptyItem;
        }
        var f = function () {
            if (!item.CityId){self.formCitiesList.setActiveItem(0);}
            else {
                for(var i=0;i<self.formCitiesList.items.length;++i){
                    if (item.CityId == self.formCitiesList.items[i].Id){
                        self.formCitiesList.setActiveItem(i);
                    }
                }
            }
            self.activeItem = angular.copy(item);
            self.metroStationsAutocomplete.Opening = false;
            $scope.StockEditForm.$setPristine();
        };
        if ($scope.StockEditForm.$dirty){
            $idialog('confirm-dialog',{dialogId:'resumeWithoutSave',options:{
                message:'Данные были изменены. Продолжить без сохранения?',
                yesCb:function(scope){
                    f();
                    scope.hide();
                }
            }});
        }else{
            f();
        }
    }

    function saveActiveItem() {
        var entity = angular.copy(self.activeItem);
        if (self.activeItem.Name.trim() == '' || self.activeItem.Street.trim() == '' || !self.activeItem.CityId){
            Notification.warning({message:'Не заполнены обязательные поля.', delay: 1000, positionY: 'top', positionX: 'right'});
            return false;
        }
        delete entity.MetroStationsList;
        delete entity.City;
        self.stocksManager.saveItem(entity).then(function(result){
            $scope.StockEditForm.$setPristine();
            updateList();
            editItem(result);
            Notification.success({message:'Точка самовывоза сохранена.', delay: 1000, positionY: 'bottom', positionX: 'right'});
        },function (response) {
            $scope.StockEditForm.$setPristine();
            Notification.error({message:'Ошибка при сохранении.', delay: 1000, positionY: 'bottom', positionX: 'right'});
            console.error('OrderDocumentsStocksController::saveActiveItem: error in save stock.', response)
        });
        return true;
    }

    function updateList() {
        self.list = self.stocksList.filter(function (item, i, arr) {
            return item.CityId == self.citiesList.getActiveItem().Id;
        });
    }

    function deleteItem(item) {
        self.stocksManager.deleteItem(item).then(function(response){
            updateList();
            Notification.success({message:'Точка самовывоза удалена.', delay: 1000, positionY: 'bottom', positionX: 'right'});
            if (self.activeItem.Id == item.Id){
                self.activeItem = {};
                self.activeItem.MetroStationsList = getCityMetroStations(self.formCitiesList.getActiveItem());
                self.activeItem.MetroStation = '';
                self.activeItem.Street = '';
                self.activeItem.CityId = self.formCitiesList.getActiveItem().Id;
                $scope.StockEditForm.$setPristine();
            }
        });
    }
    
    function selectMetroStation(station) {
        self.activeItem.MetroStation = station;
        self.metroStationsAutocomplete.Opening = false;
        self.metroStationsAutocomplete.Items = [];
    }

    function selectStreet(street) {
        self.activeItem.Street = street.typeShort+'. '+street.name;
        self.streetAutocomplete.Opening = false;
        self.streetAutocomplete.Items = [];
    }

    function getCityMetroStations(item) {
        if (!(item.Machine in self.cityMetroStations)){
            self.cityMetroStations[item.Machine] = GeoLocationService.getCityMetroStation(item);
            self.cityMetroStations[item.Machine].sort();
        }
        return self.cityMetroStations[item.Machine];
    }
};

adminApp.controller('order.documents.stocks', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', 'Notification', 'KladrService', 'GeoLocationService', OrderDocumentsStocksController]);
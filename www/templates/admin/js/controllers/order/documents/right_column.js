var OrderDocumentsRightColumnController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;
    self.citiesManager = EntityFactory('City');
    self.orderManager = EntityFactory('Order');
    self.citiesList = [];
    self.activeCity = {};

    self.citiesFilter = new ListObject({
        onItemSelect:function(){
            self.activeCity = self.citiesFilter.getActiveItem();
            $rootScope.$broadcast('Order.Documents.CityChange', {City:self.activeCity});
        }
    });



    activate();
    function activate(){
        self.citiesManager.getAll().then(function(response){
            self.citiesList = response;
            self.citiesFilter.setItems(self.citiesList);
            self.citiesFilter.setActiveItem(0);
        });
    }
};
adminApp.controller('order.documents.rightColumn', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrderDocumentsRightColumnController]);

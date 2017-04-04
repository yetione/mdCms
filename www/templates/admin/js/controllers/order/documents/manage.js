var OrderDocumentsManagerController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;
    self.citiesManager = EntityFactory('City');
    self.cities = [];
    activate();
    function activate() {
        self.citiesManager.getAll().then(function(cities){
            self.cities = cities;
        });
    }
};
adminApp.controller('order.documents.Manager', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrderDocumentsManagerController]);
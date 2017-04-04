var OrdersManagePage = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
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
adminApp.controller('order.manage.page', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrdersManagePage]);
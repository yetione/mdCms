var OrderOrdersPageController = function($scope, $rootScope, BackendService, EntityFactory, $idialog, Notification, KladrService, GeoLocationService){
    var self = this;

    self.addOrder = addOrder;
    self.editOrder = editOrder;
    self.citiesManager = EntityFactory('City');
    self.citiesList = [];
    self.loading = {cities:false};
    activate();
    function activate() {
        self.loading.cities = true;
        self.citiesManager.getAll().then(function(list){
            self.citiesList = list;
            $scope.$broadcast('page:citiesChanged', self.citiesList);
            self.loading.cities = false;
        }, function (response) {
            Notification.error({message:'Ошибка при загрузке городов', delay: 1000, positionY: 'bottom', positionX: 'right'});
            console.error('order.orders.page: Error when load cities.', response);
            self.loading.cities = false;
        });
    }

    function addOrder() {

    }

    function editOrder(order) {
        $scope.$broadcast('page:edit', {s:'EO', Order:order});
    }
};
adminApp.controller('order.orders.page', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', 'Notification', 'KladrService', 'GeoLocationService', OrderOrdersPageController]);
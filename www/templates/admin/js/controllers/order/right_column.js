var OrderRightColumnController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;
    self.citiesManager = EntityFactory('City');
    self.orderManager = EntityFactory('Order');
    self.citiesList = [];
    self.activeCity = {};
    self.ordersList = [];
    self.ordersloading = false;
    self.selectOrder = selectOrder;
    self.updateOrdersList = updateOrdersList;
    self.citiesFilter = new ListObject({
        onItemSelect:function(){
            self.activeCity = self.citiesFilter.getActiveItem();
            self.updateOrdersList();
        }
    });
    self.addOrder = addOrder;

    $scope.$on('Orders.OrderDeleted', onOrderDeleted);
    $scope.$on('Orders.OrderSaved', onOrderSaved);

    activate();
    function activate(){
        self.citiesManager.getAll().then(function(response){
            self.citiesList = response;
            self.citiesFilter.setItems(self.citiesList);
            self.citiesFilter.setActiveItem(0);
        });
    }

    function updateOrdersList(){
        self.ordersloading = true;
        self.orderManager.getList({CityId:self.activeCity.Id}).then(function(response){
            var list = response.map(function(item, i, arr){
                item.Price = parseFloat(item.Price);
                item.DateCreated = moment(item.DateCreated, 'YYYY-MM-DD HH:mm:ss');
                return item;
            });
            //list.reverse();
            list = list.sort(function (a,b) {
                if (a.DateCreated > b.DateCreated) return -1;
                if (a.DateCreated < b.DateCreated) return 1;
                return 0;
            });
            self.ordersList = list;
            self.ordersloading = false;
        });
    }

    function onOrderDeleted(event, data){
        self.updateOrdersList();
    }

    function onOrderSaved(event, data){
        self.updateOrdersList();
    }

    function addOrder(){

    }

    function selectOrder(order){
        $rootScope.$broadcast('Orders.orderSelect', {Order:order});
    }
};
adminApp.controller('order.rightColumn', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrderRightColumnController]);
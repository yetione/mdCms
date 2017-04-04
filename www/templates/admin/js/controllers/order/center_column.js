var OrderCenterColumnController = function ($scope, dataService) {
    var self = this;
    self.showDocs = showDocs;
    self.showOrders = showOrders;
    self.showManage = showManage;
    self.showStocks = showStocks;
    self.showCouriers = showCouriers;
    self.showOrdersList = showOrdersList;
    self.activeAction = '';

    activate();
    function activate(){
        self.showOrders();
    }

    function showDocs(){
        $scope.rightColumn.show('templates/admin/templates/order/documents.html').then(function(parentScope){
            self.activeAction = 'docs';
        });
    }

    function showOrders(){
        self.activeAction = 'orders';
        $scope.rightColumn.show('templates/admin/templates/order/right_column.html').then(function(parentScope){
            self.activeAction = 'orders';
        });
    }

    function showManage() {
        $scope.rightColumn.show('templates/admin/templates/order/manage.html').then(function(parentScope){
            self.activeAction = 'manage';
        });
    }

    function showStocks() {
        $scope.rightColumn.show('templates/admin/templates/order/stocks.html').then(function(parentScope){
            self.activeAction = 'stocks';
        });
    }

    function showCouriers() {
        $scope.rightColumn.show('templates/admin/templates/order/couriers.html').then(function(parentScope){
            self.activeAction = 'couriers';
        });
    }

    function showOrdersList() {
        $scope.rightColumn.show('templates/admin/templates/order/orders_page.html').then(function(parentScope){
            self.activeAction = 'orders_list';
        });
    }


};

adminApp.controller('order.centerColumn', ['$scope', 'adminDataService', OrderCenterColumnController]);
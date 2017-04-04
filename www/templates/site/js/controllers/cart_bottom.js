var CartBottomController = function($scope, CartService){
    var self = this;
    self.cart = CartService.cart;

    $scope.$on('Cart.loaded', onCartChange);
    $scope.$on('Cart.productAdd', onCartChange);
    $scope.$on('Cart.productRemove', onCartChange);
    $scope.$on('Cart.productChanged', onCartChange);
    $scope.$on('Cart.clear', onCartChange);
    $scope.$on('Cart.promoCodeActivate', onCartChange);
    $scope.$on('Cart.promoCodeDelete', onCartChange);

    activate();
    function activate(){
        self.cart = CartService.cart;
    }

    function onCartChange(event, data){
        self.cart = CartService.cart;
    }
};

siteApp.controller('CartBottomController', ['$scope', 'CartService', CartBottomController]);
var CartController = function($scope, CartService, EntityFactory){
    var self = this;
    self.cart = CartService.cart;
    self.productsManager = EntityFactory('Product');
    self.monthsName = ['января', 'февраля', 'марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    self.daysName = ['воскресенье','понедельник','вторник','среда','четверг','пятниц','суббота'];


    self.getDayStr = getDayStr;
    self.getProduct = getProduct;
    self.removeProduct = removeProduct;


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
        processCart();
    }

    function removeProduct(date, product){
        CartService.removeProduct(date, product, product.amount);
    }

    function getDayStr(date){
        var d = new Date(date);
        return d.getDate()+' '+self.monthsName[d.getMonth()]+' ('+self.daysName[d.getDay()]+')';
    }

    function processCart(){
        var productsToLoad = [];
        for (var date in self.cart.Data){

            for (var pId in self.cart.Data[date].products){
                if (self.productsManager.getById(pId) === false){
                    productsToLoad.push(pId);
                }
            }
        }
        if (productsToLoad.length > 0){
            self.productsManager.getByIds(productsToLoad);
        }
    }

    function getProduct(id){
        return self.productsManager.getById(id);
    }

};

siteApp.controller('CartController', ['$scope', 'CartService', 'EntityFactory', CartController]);
(function (windows, angular) {
    var CartService = function($rootScope, $cookies, EntityFactory, BackendService, GeoLocationService, $q, $timeout){
        var self = this;
        self.config = {
            cartKey:'Food_cart'
        };
        self.cart = {};
        self.updating = false;

        self.addProduct = addProduct;
        self.getDateInfo = getDateInfo;
        self.removeProduct = removeProduct;
        self.setProductAmount = setProductAmount;

        self.getProductPrice = getProductPrice;
        self.clearCart = clearCart;
        self.activatePromoCode = activatePromoCode;
        self.deletePromoCode = deletePromoCode;

        activate();
        function activate(){
            self.updating = true;
            BackendService.get({module:'Food', controller:'Cart', action:'getCart'}).then(function(response){
                var responseData = response.data;
                self.updating = false;
                if (responseData.status === 'OK'){
                    self.cart = responseData.data;
                    $rootScope.$broadcast('Cart.loaded', {Cart:self.cart});
                }else if(responseData.status === 'error'){
                    console.error('CartService: cant load cart.', response)
                }
            });
        }

        function setProductAmount(date, product, amount){
            var deferred = $q.defer();
            var f = function () {
                self.updating = true;
                BackendService.get({module:'Food', controller:'Cart', action:'setProductAmount', Amount:amount, ProductId:product.Id, Date:date})
                    .then(function (response) {
                        var responseData = response.data;
                        self.updating = false;
                        if (responseData.status === 'OK'){
                            self.cart = responseData.data;
                            $rootScope.$broadcast('Cart.productChanged', {Date:date, Product:product, Amount:amount});
                            deferred.resolve(self.cart);
                            return self.cart;
                        }else if(responseData.status === 'error'){
                            console.error('CartService: cant add product to cart', response)
                        }
                        deferred.reject(response);
                        return response;
                    });
            };
            $timeout(f);
            return deferred.promise;
        }

        function addProduct(date, product, amount){
            var deferred = $q.defer();
            var f = function(){
                self.updating = true;
                BackendService.get({module:'Food', controller:'Cart', action:'addProduct', Amount:amount, ProductId:product.Id, Date:date})
                    .then(function (response) {
                        var responseData = response.data;
                        self.updating = false;
                        if (responseData.status === 'OK'){
                            self.cart = responseData.data;
                            $rootScope.$broadcast('Cart.productAdd', {Date:date, Product:product, Amount:amount});
                            deferred.resolve(self.cart);
                            return self.cart;
                        }else if(responseData.status === 'error'){
                            console.error('CartService: cant add product to cart', response)
                        }
                        deferred.reject(response);
                        return response;
                    });
            };
            $timeout(f);
            return deferred.promise;
        }

        function removeProduct(date, product, amount){
            var deferred = $q.defer();
            var f = function () {
                self.updating = true;
                BackendService.get({module:'Food', controller:'Cart', action:'removeProduct', Date:date, ProductId:product.Id, Amount:amount})
                    .then(function(response){
                        var responseData = response.data;
                        self.updating = false;
                        if (responseData.status === 'OK'){
                            self.cart = responseData.data;
                            $rootScope.$broadcast('Cart.productRemove', {Date:date, Product:product, Amount:amount});
                            deferred.resolve(self.cart);
                            return self.cart;
                        }else if(responseData.status === 'error'){
                            console.error('CartService: cant add product to cart', response)
                        }
                        deferred.reject(response);
                        return response;
                    });
            };
            $timeout(f);
            return deferred.promise;
        }

        function clearCart(){
            var deferred = $q.defer();
            var f = function () {
                self.updating = true;
                BackendService.get({module:'Food', controller:'Cart', action:'clearCart'}).then(function(response){
                    var responseData = response.data;
                    self.updating = false;
                    if (responseData.status == 'OK'){
                        self.cart = responseData.data;
                        $rootScope.$broadcast('Cart.clear', {});
                        deferred.resolve(self.cart);
                        return self.cart;
                    }else{
                        console.error('CartService::clearCart: cant clear cart', response);
                    }
                    deferred.reject(response);
                    return response;
                });
            };
            $timeout(f);
            return deferred.promise;
        }

        function getDateInfo(date){
            return date in self.cart.Data ? self.cart.Data[date] : null;
        }

        function getProductPrice(product){
            var machine = GeoLocationService.getCurrentCity().Machine;
            machine = machine[0].toUpperCase()+machine.substr(1);
            return product['Price'+machine];
        }

        function activatePromoCode(code) {
            var deferred = $q.defer();
            var f = function () {
                if (self.cart.PromoCode.Id){
                    deferred.reject({status:'error', error:{code:5, message:'Promo code already set.'}});
                    return self.cart;
                }
                self.updating = true;
                BackendService.get({module:'Food', controller:'Cart', action:'activatePromoCode', Code:code}).then(function(response){
                    var responseData = response.data;
                    self.updating = false;
                    if (responseData.status == 'OK'){
                        self.cart = responseData.data;
                        $rootScope.$broadcast('Cart.promoCodeActivate', {Cart:self.cart});
                        deferred.resolve(self.cart);
                        return self.cart;
                    }else if (responseData.status == 'error'){
                        deferred.reject(responseData);
                        return self.cart;
                    }
                    console.error('CartService::activatePromoCode: cant clear cart', response);
                    deferred.reject(response);
                    return response;
                });
            };
            $timeout(f);
            return deferred.promise;
        }

        function deletePromoCode() {
            var deferred = $q.defer();
            var f = function () {
                if (self.cart.PromoCode.Id == 0){
                    deferred.reject({status:'error', error:{code:5, message:'Promo code already set.'}});
                    return self.cart;
                }
                self.updating = true;
                BackendService.get({module:'Food', controller:'Cart', action:'deletePromoCode'}).then(function(response){
                    var responseData = response.data;
                    self.updating = false;
                    if (responseData.status == 'OK'){
                        self.cart = responseData.data;
                        $rootScope.$broadcast('Cart.promoCodeDelete', {Cart:self.cart});
                        deferred.resolve(self.cart);
                        return self.cart;
                    }else if (responseData.status == 'error'){
                        deferred.reject(responseData);
                        return self.cart;
                    }
                    console.error('CartService::deletePromoCode: cant clear cart', response);
                    deferred.reject(response);
                    return response;
                });
            };
            $timeout(f);
            return deferred.promise;
        }
    };

    angular.module('mdCart', ['ngCookies', 'mdEntity', 'mdBackend', 'mdGeoLocation'])
        .service('CartService', ['$rootScope', '$cookies', 'EntityFactory', 'BackendService', 'GeoLocationService', '$q', '$timeout', CartService]);
})(window, window.angular);
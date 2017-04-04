var CartOrderDataController = function($scope, CartService, EntityFactory, $idialog, UsersService){
    var self = this;
    self.cart = CartService.cart;
    self.daysName = ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'];
    self.productsManager = EntityFactory('Product');
    self.promoCodeInput = '';
    self.itemsInLoading = {PromoCode:false};
    self.currentUser = {Id:0};

    self.getShortDate = getShortDate;
    self.getDayName = getDayName;
    self.getCurrentProductPrice = getCurrentProductPrice;
    self.getProduct = getProduct;
    self.minusProduct = minusProduct;
    self.removeProduct = removeProduct;
    self.addProduct = addProduct;
    self.inputBlur = inputBlur;
    self.activatePromoCode = activatePromoCode;
    self.deletePromoCode = deletePromoCode;


    $scope.$on('Cart.loaded', onCartChange);
    $scope.$on('Cart.productAdd', onCartChange);
    $scope.$on('Cart.productRemove', onCartChange);
    $scope.$on('Cart.productChanged', onCartChange);
    $scope.$on('Cart.clear', onCartChange);
    $scope.$on('Cart.promoCodeActivate', onCartChange);
    $scope.$on('Cart.promoCodeDelete', onCartChange);

    activate();
    function activate(){
        onCartChange();
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
        });
    }

    function addProduct(date, product){
        product.isLoaded = true;
        CartService.addProduct(date, product, 1).then(function(cart){
            product.isLoaded = false;
        }, function (response) {
            product.isLoaded = false;
        });
    }

    function minusProduct(date, product){
        product.isLoaded = true;
        CartService.removeProduct(date, product, 1).then(function(cart){
            product.isLoaded = false;
        }, function (response) {
            product.isLoaded = false;
        });
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

    function inputBlur(date,product){
        var amount = product.amount;
        //console.log(amount);
        if (!parseInt(amount)){
            amount = 1;
        }
        product.amount = amount;
        self.getProduct(product.id).isLoaded = true;
        CartService.setProductAmount(date, getProduct(product.id), amount).then(function(cart){
            self.getProduct(product.id).isLoaded = false;
        }, function (response) {
            self.getProduct(product.id).isLoaded = false;
        });
    }

    function getProduct(id){
        return self.productsManager.getById(id);
    }

    function onCartChange(event, data){
        self.cart = CartService.cart;
        processCart();
    }

    function getCurrentProductPrice(product){
        return CartService.getProductPrice(product);
    }

    function getShortDate(date){
        date = new Date(date);
        return (date.getDate()< 10 ? '0' : '')+date.getDate()+'.'+(date.getMonth()+1 < 10 ? '0' : '')+(date.getMonth()+1);
    }

    function getDayName(date){
        date = new Date(date);
        return self.daysName[date.getDay()];
    }

    function removeProduct(date, product){
        CartService.removeProduct(date, product, product.amount);
    }

    function activatePromoCode() {
        if (!self.promoCodeInput.trim() || self.currentUser.Id <= 0){
            return false;
        }
        self.itemsInLoading.PromoCode = true;
        CartService.activatePromoCode(self.promoCodeInput).then(function(cart){
            self.cart = cart;
            self.itemsInLoading.PromoCode = false;
            self.promoCodeInput = '';
        }, function (response) {
            self.itemsInLoading.PromoCode = false;
            self.promoCodeInput = '';
            if (response.status == 'error'){
                var message = '';
                switch (parseInt(response.error.code)){
                    case 5:
                        message = 'Промо-код уже установлен';
                        break;
                    case 7:
                        message = 'Вы уже использовали этот промо-код';
                        break;
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                        message = 'Код не активен.';
                        break;
                    case 6:
                    default:
                        message = 'Ошибка при установке промо-кода';
                        break;
                }
                $idialog('common/message',{dialogId:'promoCodeError', options:{title:'Ошибка.',message:message}});
            }
        });
    }

    function deletePromoCode() {
        if (self.cart.PromoCode.Id == 0){
            return false;
        }
        self.itemsInLoading.PromoCode = true;
        CartService.deletePromoCode().then(function(cart){
            self.cart = cart;
            self.itemsInLoading.PromoCode = false;
        }, function (response) {
            self.itemsInLoading.PromoCode = false;
            if (response.status == 'error'){
                var message = '';
                switch (parseInt(response.error.code)){
                    case 1:
                        message= 'Вы не зарегистрированы.';
                        break;
                    case 2:
                        return response;
                    default:
                        message = 'Ошибка при удалении промо-кода';
                        break;
                }
                $idialog('common/message',{dialogId:'promoCodeError', options:{title:'Ошибка.',message:message}});
                return response;
            }
        });

    }

};

siteApp.controller('cart.OrderData', ['$scope', 'CartService', 'EntityFactory', '$idialog', 'UsersService',  CartOrderDataController]);
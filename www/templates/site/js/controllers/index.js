var MainController = function($scope, entityFactory, GeoLocationService, CartService, BackendService, $q, $timeout){
    var self = this;
    self.categoryManager = entityFactory('Category');
    self.productManager = entityFactory('Product');
    self.currentMenuItem = {};
    self.menuItems = [];
    self.categories = [];
    self.isLoading = {Categories:false};
    self.processQueue = [];
    self.menuItemsPool = [];
    self.cs = CartService;

    self.getCurrentProductPrice = getCurrentProductPrice;
    self.addToCart = addToCart;

    $scope.$on('Menu.daySelected', onDaySelected);

    activate();
    function activate(){
        self.isLoading.Categories = true;
        self.categoryManager.getAll([['Weight','ASC']]).then(function(list){
            self.categories = list;
            self.isLoading.Categories = false;
            for (var i=0;i<self.processQueue.length;++i){
                processMenuItem(self.processQueue[i]);
            }
        });
    }

    function addToCart(product){
        CartService.addProduct(self.currentMenuItem.getDate(), product, 1);
    }

    function onDaySelected(event, data){
        if (!self.categoryManager._allLoaded){
            self.processQueue.push(data.MenuItem);
            return false;
        }
        processMenuItem(data.MenuItem);
        return true;
    }

    function processMenuItem(item) {
        if (!(item.Entity.Date in self.menuItemsPool)){
            var ent = new MenuEntity(self.categoryManager, self.productManager, $q, $timeout);
            ent.fromEntity(item.Entity).then(function(data){
                self.menuItemsPool[item.Entity.Date] = ent;
                setCurrentMenuItem(ent);
            });
        }else{
            setCurrentMenuItem(self.menuItemsPool[item.Entity.Date]);
        }
    }

    function getCurrentProductPrice(product){
        return CartService.getProductPrice(product);
    }

    function setCurrentMenuItem(item){
        self.currentMenuItem = item;
    }
};

siteApp.controller('IndexController', ['$scope', 'EntityFactory', 'GeoLocationService', 'CartService', 'BackendService', '$q', '$timeout',  MainController]);
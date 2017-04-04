var ProductsController = function($scope, $rootScope, entityFactory){
    var self = this;
    self.currentMenuItem = {};
    self.menuItems = [];
    self.categoresProducts = {

    };
    self.productsManager = entityFactory('Product');
    self.categoriesManager = entityFactory('Category');

    activate();
    function activate(){

    }

    function processMenuItem(item){
        var entitiesToLoad = [], i, menuItemObject;
        for (i=0;i<item.Data.categories.length;i++){
            if (self.categoriesManager.getById(item.Data.categories[i]) === false){
                entitiesToLoad.push(item.Data.categories[i]);
            }
        }
        self.categoriesManager.loadIds(entitiesToLoad).success(function(response){
            entitiesToLoad = [];
            for (i=0;i<item.Data.products.length;i++){
                if (self.productsManager.getById(item.Data.products[i]) === false){
                    entitiesToLoad.push(item.Data.products[i]);
                }
            }
            self.productsManager.loadIds(entitiesToLoad).success(function(response){
                menuItemObject = new MenuItem(item, self.productsManager);
                setCurrentMenuItem(menuItemObject);
                //console.log(menuItemObject);
            });
        });





    }

    function findMenuItem(strDate){
        for (var i=0;i<self.menuItems.length;i++){
            if (self.menuItems[i].Date == strDate){
                return self.menuItems[i];
            }
        }
        return false;
    }

    function setCurrentMenuItem(item){
        self.currentMenuItem = item;
    }
};

siteApp.controller('ProductsController', ['$scope', '$rootScope', 'EntityFactory',  ProductsController]);
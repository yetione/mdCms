var ProductsListController = function($scope, $rootScope, BackendService, EntityFactory){
    var self = this;
    self.list = [];
    self.productsManager = EntityFactory('Product');
    self.categoryManager = EntityFactory('Category');
    self.productTypeManager = EntityFactory('ProductType');
    self.getProductType = getProductType;
    self.filter = filter;
    self.selectProduct = selectProduct;
    self.nameFilter = '';
    self.selectedProduct = {Product:null,Amount:1};
    self.add = add;

    self.categoryFilter = new ListObject({
        onItemSelect:function(){
            var category = self.categoryFilter.getActiveItem();

            var productTypes = self.productTypes.filter(function (item, i, arr) {
                return item.CategoryId == category.Id;
            });
            productTypes.unshift({Id:0,Name:'Все категории'});

            self.productTypeFilter.setItems(productTypes);
            self.productTypeFilter.setActiveItem(0);
            self.productsList.setItems(self.products.filter(function (item, i, arr) {
                return item.CategoryId == category.Id;
            }));
        }
    });
    self.productTypeFilter = new ListObject();
    self.productsList = new ListObject();

    activate();
    function activate(){
        self.productTypeManager.getList().then(function(productTypes){
            self.productTypes = productTypes;
            self.categoryManager.getList().then(function(categories){
                self.categories = categories;
                self.productsManager.getList().then(function(products){
                    self.products = products;
                    self.categoryFilter.setItems(self.categories);
                    self.categoryFilter.setActiveItem(0);
                });
            })

        });
    }

    function getProductType(id){
        for (var i=0;i<self.productTypes.length;i++){
            if (parseInt(id) == parseInt(self.productTypes[i].Id)){
                return self.productTypes[i];
            }
        }
    }

    function filter(item){
        var result = true;
        if (self.nameFilter.length > 0 && item.Name.toLowerCase().indexOf(self.nameFilter.toLowerCase()) == -1 && item.Description.toLowerCase().indexOf(self.nameFilter.toLowerCase()) == -1) result = false;
        if (parseInt(self.productTypeFilter.getActiveItem().Id) > 0 && parseInt(item.TypeId) != parseInt(self.productTypeFilter.getActiveItem().Id)) result = false;
        return result;

    }

    function selectProduct(product){
        self.selectedProduct.Product = product;
        self.selectedProduct.Amount = 1;
    }

    function add(){
        if (self.selectedProduct.Product && parseInt(self.selectedProduct.Amount) > 0){
            $rootScope.$broadcast('ProductSelect.selected', self.selectedProduct);
            $scope.hide();
        }

    }
};

adminApp.controller('order.productsList', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', ProductsListController]);
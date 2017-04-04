var ShopDialogsProductsListController = function($scope, $rootScope, BackendService, EntityFactory, Notification){
    var self = this;
    self.loading = {products:false, category:false, productTypes:false};

    self.productsManager = EntityFactory('Product');
    self.productsList = [];
    self.selectedProducts = [];

    self.categoryManager = EntityFactory('Category');
    self.categories = [];
    self.activeCategory = {};

    self.productTypeManager = EntityFactory('ProductType');
    self.productTypes = [];
    self.PTSelect = [];
    self.nullProductType = {Id:0, Name:'Все категории'};
    self.activeProductType = self.nullProductType;

    self.nameFilter = '';
    self.options = {onSubmit:function(scope,ctrl){}};

    self.onCategorySelect = onCategorySelect;
    self.onProductTypeSelect = onProductTypeSelect;
    self.selectProduct = selectProduct;
    self.clearSelectedProducts = clearSelectedProducts;
    self.add = add;

    activate();
    function activate(){
        angular.extend(self.options, $scope.dialogOptions);
        self.loading.category = true;
        self.categoryManager.getAll().then(function(list){
            self.categories = list;
            self.activeCategory = self.categories[0];
            self.onCategorySelect();
            self.loading.category = false;
        }, function (response) {
            Notification.error({message:'Ошибка при загрузке списка категорий', delay: 1000, positionY: 'bottom', positionX: 'right'});
            console.error('shop.dialogs.productsList::activate : cant load category list', response);
            self.loading.category = false;
        });

        self.loading.productTypes = true;
        self.productTypeManager.getAll().then(function (list) {
            self.productTypes = list;
            if (self.activeCategory.Id){
                updateTypes();
            }
            self.loading.productTypes = false;
        }, function (response) {
            Notification.error({message:'Ошибка при загрузке типов товара', delay: 1000, positionY: 'bottom', positionX: 'right'});
            console.error('shop.dialogs.productsList::activate : cant load product types list', response);
            self.loading.productTypes = false;
        });
    }

    function onCategorySelect($item, $event, $isNull){
        updateTypes();
        self.activeProductType = self.nullProductType;
        self.loading.products = true;
        if (!self.activeCategory.Products || self.activeCategory.Products.length == 0){
            var c = self.activeCategory;
            self.productsManager.getList({CategoryId:c.Id}).then(function(list){
                list.forEach(function (item,i,arr) {
                    item.Category = c;
                    item.ProductType = self.productTypeManager.getById(item.TypeId);
                });
                c.Products = list;
                self.productsList = c.Products;
                self.onProductTypeSelect();
                self.loading.products = false;
            }, function (response) {
                console.error('shop.dialogs.productsList : Error when load products.', response);
            });
        }else{
            self.onProductTypeSelect();
            self.loading.products = false;
        }

    }

    function onProductTypeSelect($item, $event, $isNull) {
        self.productsList = self.activeProductType.Id == 0 ? self.activeCategory.Products : self.activeCategory.Products.filter(function(item, i, arr){
            return item.TypeId == self.activeProductType.Id;
        });
    }

    function updateTypes(){
        self.PTSelect = self.productTypes.filter(function (item, i, arr) {
            return item.CategoryId == self.activeCategory.Id;
        });

    }

    function selectProduct(product) {
        var i = self.selectedProducts.indexOf(product);
        if (~i){
            self.selectedProducts.splice(i,1);
            product.marked = false;
        }else{
            self.selectedProducts.push(product);
            product.marked = true;
        }
    }

    function add(){
        self.options.onSubmit($scope, self);
        //self.selectedProducts.forEach(function (item, i, arr) {item.marked = false;});
        $scope.hide();
    }

    function clearSelectedProducts() {
        self.selectedProducts.forEach(function (item, i, arr) {item.marked = false;});
        self.selectedProducts = [];
    }
};

adminApp.controller('shop.dialogs.productsList', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', 'Notification', ShopDialogsProductsListController]);

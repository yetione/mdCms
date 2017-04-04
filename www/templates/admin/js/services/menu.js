var MenuEntity = function(CategoriesManager, ProductsManager){
    var self = this;

    self.data = {
        Id:0,
        CityId:0,
        Enabled:1,
        Date:'',
        Data:[]
    };

    self.setId = setId;
    self.setData = setData;
    self.setCityId = setCityId;
    self.setEnabled = setEnabled;
    self.setDate = setDate;
    self.addCategory = addCategory;
    self.addProduct = addProduct;
    self.removeProduct = removeProduct;
    self.fromEntity = fromEntity;
    self.toEntity = toEntity;
    self.getProducts = getProducts;

    activate();
    function activate() {
        var categories = CategoriesManager.getLoaded();
        for(var i=0;i<categories.length;++i){
            self.addCategory(categories[i]);
        }
    }

    function fromEntity(entity) {
        var i, j;
        entity.Data = angular.fromJson(entity.Data);
        for(i=0;i<entity.Data.length;++i){
            self.addCategory(CategoriesManager.getById(parseInt(entity.Data[i].CategoryId)));
            for (j=0;j<entity.Data[i].Products.length;++j){
                self.addProduct(ProductsManager.getById(parseInt(entity.Data[i].Products[j])));
            }
        }
        delete entity.Data;
        self.data = angular.extend(self.data, entity);
    }

    function toEntity() {
        var entity = angular.copy(self.data), data, i, j;
        entity.Data = [];
        for(i=0;i<self.data.Data.length;++i){
            data = {CategoryId:self.data.Data[i].Id, Products:[]};
            for (j=0;j<self.data.Data[i].Products.length;++j){
                data.Products.push(self.data.Data[i].Products[j].Id);
            }
            entity.Data.push(data);
        }
        entity.Data = angular.toJson(entity.Data);
        return entity;
    }

    function addCategory(category) {
        var cIndex = getCategoryIndex(category.Id);
        if (cIndex == -1){
            category = angular.copy(category);
            category.Products = [];
            self.data.Data.push(category);
            return self.data.Data.length - 1;
        }
        return false;
    }

    function addProduct(product) {
        var cIndex = getCategoryIndex(product.CategoryId), pId = parseInt(product.Id), category;
        if (cIndex == -1){
            cIndex = self.addCategory(CategoriesManager.getById(pCId));
        }
        var products = self.data.Data[cIndex].Products.filter(function(item, i, arr){return parseInt(item.Id) == parseInt(product.Id);});
        if (products.length == 0){
            self.data.Data[cIndex].Products.push(product);
            return true;
        }
        return false;
    }

    function getProducts() {
        var result = [], i, j;
        for(i=0;i<self.data.Data.length;++i){
            for(j=0;j<self.data.Data[i].Products.length;++j){
                result.push(self.data.Data[i].Products[j]);
            }
        }
        return result;
    }

    function removeProduct(product) {
        var cIndex = getCategoryIndex(product.CategoryId);
        if (cIndex == -1) return false;
        var pIndex = getProductIndex(cIndex, product.Id);
        if (pIndex == -1) return false;
        console.log('rP', pIndex, cIndex);
        self.data.Data[cIndex].Products.splice(pIndex, 1);
        return true;
    }

    function setId(id) {
        self.data.Id = parseInt(id);
    }

    function setData(data) {
        self.data.Data = data;
    }

    function setCityId(id) {
        self.data.CityId = parseInt(id);
    }

    function setEnabled(enabled) {
        self.data.Enabled = parseInt(enabled);
    }

    function setDate(date) {
        self.data.Date = date;
    }

    function getCategoryIndex(categoryId) {
        for(var i=0;i<self.data.Data.length;++i){
            if (self.data.Data[i].Id == categoryId){
                return i;
            }
        }
        return -1;
    }
    function getProductIndex(cIndex, productId) {
        for(var i=0;i<self.data.Data[cIndex].Products.length;++i){
            if (self.data.Data[cIndex].Products[i].Id == productId){
                return i;
            }
        }
        return -1;
    }
};

var MenuEntityService = function(EntityFactory, BackendService, $timeout, $q){
    var self = this;

    self.data = {
        Id:0,
        CityId:0,
        Enabled:1,
        Date:'',
        Data:{}
    };

    self.categoriesManager = EntityFactory('Category');
    self.productsManager = EntityFactory('Product');
    self.menuManager = EntityFactory('Menu');
    self.menuItems = {};
    self.categories = [];
    self.products = [];

    self.run = run;

    self.getMenuToDate = getMenuToDate;
    self.saveMenu = saveMenu;

    function run() {
        self.productsManager.getAll().then(function(list){
            self.products = list;
        });
        self.categoriesManager.getAll().then(function(list){
            self.categories = list;
        });
    }

    function getMenuToDate(date) {
        var deferred = $q.defer();
        var f = function(){
            var strDate = getStrDate(date);
            if (strDate in self.menuItems){
                deferred.resolve(self.menuItems[strDate]);
            }else{
                self.menuManager.getItem({Date:strDate}).then(function(item){
                    var ent = new MenuEntity(self.categoriesManager, self.productsManager);
                    ent.fromEntity(item);
                    deferred.resolve(ent);
                },function (response) {
                    var ent = new MenuEntity(self.categoriesManager, self.productsManager);
                    for (var i=0;i<self.categories.length;++i){
                        ent.addCategory(self.categories[i]);
                    }
                    ent.setDate(strDate);
                    deferred.resolve(ent);

                });
            }
        };
        $timeout(f);
        return deferred.promise;
    }

    function saveMenu(menu) {
        var deferred = $q.defer();
        var f = function () {
            BackendService.send({data:menu.toEntity()}, {module:'Food', controller:'Admin\\Menu', action:'saveItem'}).then(function (response) {
                var responseData = response.data;
                if (responseData.status == 'OK'){
                    var ent = new MenuEntity(self.categoriesManager, self.productsManager);
                    ent.fromEntity(responseData.data);
                    deferred.resolve(ent);
                }else {
                    console.error('MenuEntityService::saveMenu: Error when save menu.', responseData.error);
                    deferred.reject(responseData.error);
                }
            });
        };
        $timeout(f);
        return deferred.promise;
    }

    function getStrDate(date) { return date.getFullYear()+'-'+((date.getMonth()+1) > 9 ? date.getMonth()+1 : '0'+(date.getMonth()+1))+'-'+(date.getDate() > 9 ? date.getDate() : '0'+date.getDate());}

};
adminApp.service('MenuEntityService', ['EntityFactory', 'BackendService', '$timeout', '$q', MenuEntityService]);
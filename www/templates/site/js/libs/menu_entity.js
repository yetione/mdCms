var MenuEntity = function(CategoriesManager, ProductsManager, $q, $timeout){
    var self = this;

    self.data = {
        Id:0,
        CityId:0,
        Enabled:1,
        Date:'',
        Data:[]
    };

    self.getDate = getDate;
    self.fromEntity = fromEntity;
    self.addCategory = addCategory;
    self.addProduct = addProduct;

    activate();
    function activate(){}

    function getDate(){
        return self.data.Date;
    }

    function fromEntity(entity) {
        var deferred = $q.defer();
        var f = function () {
            var i, j, productsToLoad=[], p, emptyProductsIndex={};
            entity = angular.copy(entity);
            entity.Data = angular.fromJson(entity.Data);
            for(i=0;i<entity.Data.length;++i){  //Сначала добавляем все категории
                self.addCategory(CategoriesManager.getById(parseInt(entity.Data[i].CategoryId)));
                for (j=0;j<entity.Data[i].Products.length;++j){
                    p = ProductsManager.getById(parseInt(entity.Data[i].Products[j]));  //Потом ищем товар, если его нету, то записываем false в то место, где
                                                                                        //он должен находиться, а также сохраням его индекс для последующей его
                                                                                        //вствавки в нужную позицию
                    if (p === false){
                        productsToLoad.push(entity.Data[i].Products[j]);
                        emptyProductsIndex['ProductId'+entity.Data[i].Products[j]] = {CategoryIndex:i, ProductIndex:self.data.Data[i].Products.push(false)-1};
                    }else{
                        self.addProduct(p);//Если есть то спокойно записываем
                    }
                }
            }
            delete entity.Data;
            var fixData = function () { //Финальная обработка данных
                var categories = CategoriesManager.getLoaded(), t;
                for (var i=0;i<categories.length;++i){ //Дополняем массив с категориями до полного
                    if (getCategoryIndex(categories[i].Id) == -1){
                        t = angular.copy(categories[i]);
                        t.Products = [];
                        self.data.Data.push(t);
                    }
                }

                self.data = angular.extend(self.data, entity);
                self.data.Data.sort(function (a, b) { //Сортируем категории по весу
                    if (parseInt(a.Weight) > parseInt(b.Weight)) return 1;
                    if (parseInt(a.Weight) < parseInt(b.Weight)) return -1;
                    return 0
                });
                deferred.resolve(self);
            };
            //Загружаем продукты те, которых нету.
            if (productsToLoad.length > 0){
                ProductsManager.getByIds(productsToLoad).then(function(products){
                    var k = '';
                    for (var i=0;i<products.length;++i){
                        k = 'ProductId'+products[i].Id;
                        if (k in emptyProductsIndex){
                            self.data.Data[emptyProductsIndex[k].CategoryIndex].Products[emptyProductsIndex[k].ProductIndex] = products[i]; //Записываем товар, вместо ранее записанного false
                        }
                    }
                    fixData();
                });
            }else{
                fixData();
            }
        };
        $timeout(f);
        return deferred.promise;
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
        var cIndex = getCategoryIndex(product.CategoryId);
        if (cIndex == -1){
            cIndex = self.addCategory(CategoriesManager.getById(product.CategoryId));
        }
        var products = self.data.Data[cIndex].Products.filter(function(item, i, arr){return parseInt(item.Id) == parseInt(product.Id);});
        if (products.length == 0){
            self.data.Data[cIndex].Products.push(product);
            return true;
        }
        return false;
    }

    function getCategoryIndex(categoryId) {
        for(var i=0;i<self.data.Data.length;++i){
            if (self.data.Data[i].Id == categoryId){
                return i;
            }
        }
        return -1;
    }
};
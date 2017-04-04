


var MenuItem = function(data){
    var self = this;

    data = data || {
        Id:0,
        Date:'',
        Data:{
            categories:[],
            products:[]
        },
        //CityId:0,
        Enabled:1
    };

    self.setData = setData;
    self.getProducts = getProducts;
    self.getCategory = getCategory;
    self.addCategory = addCategory;
    self.addProduct = addProduct;
    self.toEntity = toEntity;
    self.setDate = setDate;
    self.removeProduct = removeProduct;

    self.saved = true;

    self.setData(data);

    function getProducts(categoryId){
        /*return self.data.Products.filter(function(item, i, arr){
            //return item.
        });
        if (!(categoryId in self.data.Products)) self.data.Products[categoryId] = [];*/
        return self.data.Products[categoryId] ;
    }

    function setData(data){
        self.data = data;
    }

    function getCategory(id){
        for (var i=0;i<self.data.Data.categories.length;i++){
            if (self.data.Data.categories[i].Id == id){
                return self.data.Data.categories[i];
            }
        }
        return false;
    }

    function addCategory(category){
        category.Products = [];
        self.data.Data.categories.push(category);
    }

    function addProduct(product){
        if (self.data.Data.products.indexOf(product) == -1)
            self.data.Data.products.push(product);
        //getCategory(product.CategoryId).Products
    }

    function removeProduct(product){
        self.data.Data.products.splice(self.data.Data.products.indexOf(product), 1);
        self.saved = false;
    }

    function toEntity(){
        var d = angular.copy(self.data),
        t=[], products = [], i=0;
        console.log('data', d);
        for (i=0;i< d.Data.categories.length;i++){
            t.push(d.Data.categories[i].Id);
        }
        d.Data.categories = t;

        t=[];
        for (i=0;i< d.Data.products.length;i++){
            if (d.Data.products[i] && d.Data.products[i].Id){
                t.push(d.Data.products[i].Id);
            }

        }
        d.Data.products = t;
        d.Data = angular.toJson(d.Data);
        d.Date = d.Date.getFullYear()+'-'+(d.Date.getMonth()+1)+'-'+d.Date.getDate();
        return d;
    }

    function setDate(date){
        self.data.Date = date;
    }
};




var MenuRightColumnController = function($scope, dataService, $idialog, EntityFactory, Notification){
    var self = this;

    self.activeDate = null;
    //Date(year, month, date, hours, minutes, seconds, ms)
    self.minDate = new Date(2016, 0, 1);
    self.cities = [];
    self.productTypes = [];
    self.categories = [];
    self.products = {};

    //self.addProduct = addProduct;
    self.filter = filter;
    self.getProductType = getProductType;
    self.getProduct = getProduct;
    self.getProducts = getProducts;
    self.getMenuCategoryProducts = getMenuCategoryProducts;
    self.selectProduct = selectProduct;
    self.getCategory = getCategory;
    self.setCurrentDateEnable = setCurrentDateEnable;
    self.saveMenu = saveMenu;

    self.onProductMove = onProductMove;



    self.products = [];

    self.currentMenuItem = {};
    self.productsManager = EntityFactory('Product');

    self.categoryFilter = new ListObject({
        onItemSelect:function(){
            var category = self.categoryFilter.getActiveItem();

            var productTypes = getProductTypes(category.Id);
            productTypes.unshift({Id:0,Name:'Все категории'});

            self.productTypeFilter.setItems(productTypes);
            self.productTypeFilter.setActiveItem(0);

            /*loadProducts(category.Id).success(function(response){
                category.Products = response.data;
                self.productsList.setItems(category.Products);
            });*/

            var products = self.getProducts({CategoryId:category.Id});
            if (!products || products.length == 0){
                loadProducts(category.Id).success(function(response){
                    addProducts(response.data);
                    self.productsList.setItems(response.data);
                });
            }else{
                self.productsList.setItems(products);
            }
        }
    });
    self.productTypeFilter = new ListObject();
    self.productsList = new ListObject();

    self.nameFilter = '';


    activate();
    function activate(){
        self.calendarDate = new Date();
        loadProductTypes().success(function(response){
            loadCategories().success(function (response) {
                /*loadAllProducts().success(function(response){
                    self.categoryFilter.setItems(self.categories);
                    self.categoryFilter.setActiveItem(0);
                    self.activeDate = new Date();
                    setCurrentMenuItem(self.activeDate);
                });*/
                self.productsManager.getList().then(function(list){
                    self.products = list;
                });
                self.categoryFilter.setItems(self.categories);
                self.categoryFilter.setActiveItem(0);
                self.activeDate = new Date();
                setCurrentMenuItem(self.activeDate);

            });
        });
        loadCities();
    }

    function onProductMove(product, event, i) {
        console.log(product, event, i, self.currentMenuItem.data.Data.products);

    }

    function filter(item){
        //console.log(item, parseInt(self.type.Id));
        var result = true;
        if (self.nameFilter.length > 0 && item.Name.toLowerCase().indexOf(self.nameFilter.toLowerCase()) == -1 && item.Description.toLowerCase().indexOf(self.nameFilter.toLowerCase()) == -1) result = false;
        if (parseInt(self.productTypeFilter.getActiveItem().Id) > 0 && parseInt(item.TypeId) != parseInt(self.productTypeFilter.getActiveItem().Id)) result = false;
        return result;

    }

    $scope.$on('calendar.daySelect', function(event, data){
        console.log('select date', data);
        if (data.date.getDate() == 99){
            data.deferred.reject(data.date);
        }else{
            var action = function(){
                setCurrentMenuItem(data.date);
            };
            action();
        }

        /*
        if (!self.currentMenuItem.saved){
            $idialog('confirm-dialog',{dialogId:'resumeWithoutSave',options:{
                message:'Данные были изменены. Продолжить без сохранения?',
                yesCb:function($scope){
                    action();
                    $scope.hide();
                }
            }});
        }else{
            action();
        }*/


    });

    function setCurrentMenuItem(date){
        var strDate = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();

        getMenuToDate(strDate).success(function(response){
            var menu = response.data, category, i;
            if (!menu){
                //menu = getEmptyMenu();
                menu = new MenuItem();
                for (i=0;i<self.categories.length;i++){
                    menu.addCategory(self.categories[i]);
                }

            }else{
                menu.Data = angular.fromJson(menu.Data);
                for (i=0;i<menu.Data.categories.length;i++){
                    menu.Data.categories[i] = getCategory(menu.Data.categories[i]);
                }

                for (i=0;i<menu.Data.products.length;i++){
                    menu.Data.products[i] = getProduct({Id:menu.Data.products[i]});
                }
                menu = new MenuItem(menu);
            }
            menu.setDate(date);
            self.currentMenuItem = menu;
            console.log('m', menu);
        });
    }

    $scope.$on('calendar.pageCreated', function(event, params){
        var page = params.page;

    });

    function getMenuCategoryProducts(categoryId){
        return self.currentMenuItem.data.Products.map(function (item, i, arr) {
            return getProduct({CategoryId: categoryId, Id: item});
        });
    }

    function getCategory(id){
        for (var i=0;i<self.categories.length;i++){
            if (self.categories[i].Id == id){
                return self.categories[i];
            }
        }
    }


    //function selectProduct(pId){
    function selectProduct(product){
        //var product = getProduct({Id:pId});
        if (!self.currentMenuItem.getCategory(product.CategoryId)){
            self.currentMenuItem.addCategory(getCategory(product.CategoryId));
        }
        self.currentMenuItem.addProduct(product);
        Notification.success({message: 'Продукт добавлен', delay: 1000, positionY: 'bottom', positionX: 'right'});
        self.currentMenuItem.saved = false;
    }

    function setCurrentDateEnable(enable){
        self.currentMenuItem.data.Enabled = enable;
        saveMenu();
    }


    function saveMenu(){
        var ent = self.currentMenuItem.toEntity();
        console.log('ent', ent);
        dataService.send({item:ent}, {module:'Food', controller:'Menu', action:'saveItem'}).success(function(response){
            $idialog('message-dialog',{
                dialogId: 'messageDialog',
                options:{message:'Меню сохранено.'}
            });
            self.currentMenuItem.saved = true;
        });
    }


    function getMenuToDate(date){
        return dataService.get({module:'Food', controller:'Menu', action:'getItem', date:date});
    }

    function loadAllProducts(){
        return dataService.get({module:'Food', controller:'Products', action:'getList'}).success(function(response){
            addProducts(response.data);
            //self.products = response.data;
            //self.productTypes = response.data;
            return self.products;
        });
    }

    function loadProductTypes(){
        return dataService.get({module:'Food', controller:'ProductTypes', action:'getList'}).success(function(response){
            self.productTypes = response.data;
            return self.productTypes;
        });
    }

    function getProductTypes(categoryId){
        return self.productTypes.filter(function (item, i, arr) {
            return item.CategoryId == categoryId;
        });
        /*
        var result = [];
        for (var i=0;i<self.productTypes.length;i++){
            if (self.productTypes[i].CategoryId == categoryId){
                result.push(self.productTypes[i]);
            }
        }
        return result;*/
    }

    function getProductType(id){
        onProductMove
    }

    function addProducts(products){
        products.forEach(function(item, i, arr){
            if (!getProduct(item.Id)){
                self.products.push(item);
            }
        });
    }

    function loadCities(){
        return dataService.get({module:'Food', controller:'Cities', action:'getList'}).success(function(response){
            self.cities = response.data;
            return self.cities;
        });
    }

    function loadCategories(){
        return dataService.get({module:'Food', controller:'Categories', action:'getList'}).success(function(response){
            self.categories = response.data;
            return self.categories;
        });
    }

    function loadProducts(categoryId){
        return dataService.get({module:'Food', controller:'Products', action:'getList', params:{CategoryId:categoryId}});
    }

    function getProduct(data){
        var result = getProducts(data);
        return result.length == 1 ? result[0] : undefined;
    }

    function getProducts(data){
        return self.products.filter(function(item, i, arr){
            for (x in data){
                if (item[x] != data[x]){
                    return false;
                }
            }
            return true;
            //return item.Id == id;
        });
    }



};

adminApp.controller('menu.rightColumn', ['$scope', 'adminDataService', '$idialog', 'EntityFactory', 'Notification', MenuRightColumnController]);

var MenuDaysController = function($scope){
    var self = this;
    
};
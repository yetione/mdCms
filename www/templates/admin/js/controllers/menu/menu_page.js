var MenuRightColumnPageController = function ($scope, BackendService, $idialog, EntityFactory, Notification, MenuEntityService) {
    var self = this;

    self.activeDay = null;
    self.calendarDate = null;
    self.minDate = new Date(2016, 0, 1);
    self.cities = [];
    self.productTypes = [];
    self.categories = [];
    self.products = [];
    self.currentProducts = [];

    self.isLoading = {
        Data:false,
        MenuItem:false,
    };

    self.productsTypeManager = EntityFactory('ProductType');
    self.categoriesManager = EntityFactory('Category');
    self.productsManager = EntityFactory('Product');
    self.citiesManager = EntityFactory('City');
    self.menuManager = EntityFactory('Menu');
    self.nameFilter = '';

    self.filter = filter;

    self.setCurrentDateEnable = setCurrentDateEnable;
    self.saveCurrentMenuItem = saveCurrentMenuItem;
    self.getProductType = getProductType;
    self.onProductMove = onProductMove;
    self.addProduct = addProduct;
    self.removeProduct = removeProduct;

    activate();
    function activate() {
        self.calendarDate = new Date();
        self.categoryFilter = new ListObject({
            onItemSelect:function(){
                var category = self.categoryFilter.getActiveItem();
                if (self.categoryFilter.previousItem === null || parseInt(category.Id) !== parseInt(self.categoryFilter.previousItem.Id) ){
                    self.productTypeFilter.updateItems();
                    self.productTypeFilter.setActiveItem(0);
                    self.productsList.updateItems();
                    self.productsList.setActiveItem(0);
                }
            }
        });
        self.productTypeFilter = new ListObject({
            onUpdateItems:function () {
                var productTypes = self.productTypes.filter(function(item,i,arr){
                    return parseInt(self.categoryFilter.getActiveItem().Id) === parseInt(item.CategoryId);
                });
                productTypes.unshift({Id:0,Name:'Все категории'});
                self.productTypeFilter.previousItem = null;
                self.productTypeFilter.setItems(productTypes);
            }
        });
        self.productsList = new ListObject({
            onUpdateItems:function () {
                var products = self.currentProducts.filter(function (item,i,arr) {
                    return parseInt(self.categoryFilter.getActiveItem().Id) === parseInt(item.CategoryId);
                });
                self.productsList.previousItem = null;
                self.productsList.setItems(products);
            }
        });
        self.activeDate = new Date();
        loadData();
    }

    $scope.$on('calendar.daySelect',onDaySelect);
    function onDaySelect(event, data) {
        if (data.date.getDate() == 99){
            data.deferred.reject(data.date);
        }else{
            setCurrentMenuItem(data.date);
        }
    }

    function setCurrentMenuItem(date) {
        self.isLoading.MenuItem = true;
        MenuEntityService.getMenuToDate(date).then(function (item) {
            self.currentMenuItem = item;
            self.isLoading.MenuItem = false;
            self.currentProducts = angular.copy(self.products);
            var menuProducts = self.currentMenuItem.getProducts(), t;
            for(var i=0;i<menuProducts.length;++i){
                t = getProductCurrentIndex(menuProducts[i].Id);
                if (t != -1){
                    self.currentProducts.splice(t, 1);
                }
            }
            self.productsList.updateItems();
        });
    }

    function setCurrentDateEnable(enabled) {
        self.currentMenuItem.setEnabled(enabled);
        self.saveCurrentMenuItem();
    }

    function saveCurrentMenuItem(notShowNotify) {
        return MenuEntityService.saveMenu(self.currentMenuItem).then(function (item) {
            self.currentMenuItem = item;
            if (!notShowNotify){Notification.success({message:'Меню сохранено.', delay: 1000, positionY: 'top', positionX: 'right'});}

        }, function (errorData) {
            $idialog('message-dialog', {dialogId:'MenuSaveError', options:{title:'Ошибка', message:'Ошибка при сохранении меню.'}});
        });
    }

    function onProductMove(product, category, index, event) {
        category.Products.splice(index, 1);
        self.saveCurrentMenuItem();
    }

    function addProduct(product) {
        if (self.currentMenuItem.addProduct(product)){
            Notification.primary({message: 'Добавлено.', delay: 1000, positionY: 'bottom', positionX: 'right'});
            var i = getProductCurrentIndex(product.Id);
            if (i != -1){
                self.currentProducts.splice(i, 1);
                self.productsList.updateItems();
            }
        }else{
            Notification.warning({message: 'Товар уже в списке.', delay: 1000, positionY: 'bottom', positionX: 'right'});
        }
    }

    function removeProduct(product) {
        if (self.currentMenuItem.removeProduct(product)){
            var i = getProductCurrentIndex(product.Id);
            if (i == -1){
                self.currentProducts.push(product);
                self.productsList.updateItems();
            }
            Notification.primary({message: 'Удалено.', delay: 1000, positionY: 'bottom', positionX: 'right'});
            return true;
        }
        Notification.warning({message: 'Не удалось удалить.', delay: 1000, positionY: 'bottom', positionX: 'right'});
        return false;
    }

    function getProductCurrentIndex(id) {
        for(var i=0;i<self.currentProducts.length;++i){
            if (self.currentProducts[i].Id == id){return i;}
        }
        return -1;
    }
    function getProductType(productTypeId) {
        for (var i=0;i<self.productTypes.length;i++){
            if (parseInt(productTypeId) == parseInt(self.productTypes[i].Id)){
                return self.productTypes[i];
            }
        }
    }
    function loadData() {
        self.isLoading.Data = true;
        self.productsTypeManager.getAll().then(function(list){
            self.productTypes = list;
            self.categoriesManager.getAll().then(function (list) {
                self.categories = list;
                self.productsManager.getAll().then(function(list){
                    self.products = list;
                    self.currentProducts = angular.copy(self.products);
                    self.categoryFilter.setItems(self.categories);
                    self.categoryFilter.previousItem = null;
                    self.categoryFilter.setActiveItem(0);
                    self.isLoading.Data = false;
                });
            });
        });
        self.citiesManager.getAll().then(function(list){
            self.cities = list;
        });
    }
    function filter(item){
        var result = true;
        if (self.nameFilter.length > 0 && item.Name.toLowerCase().indexOf(self.nameFilter.toLowerCase()) == -1 && item.Description.toLowerCase().indexOf(self.nameFilter.toLowerCase()) == -1) result = false;
        if (parseInt(self.productTypeFilter.getActiveItem().Id) > 0 && parseInt(item.TypeId) != parseInt(self.productTypeFilter.getActiveItem().Id)) result = false;
        return result;
    }
};

adminApp.controller('menu.rightColumn.page', ['$scope', 'BackendService', '$idialog', 'EntityFactory', 'Notification', 'MenuEntityService', MenuRightColumnPageController]);
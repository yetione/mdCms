var OrderItemController = function($scope, $rootScope, BackendService, EntityFactory, $idialog, Notification, $timeout){
    var self = this;
    self.order = {};
    self.orderDays = [];
    self.orderDaysSelect = [];
    self.activeOrderDay = {};
    self.editingProduct = {Product:{},OriginalProduct:{}};

    self.showDatepicker = false;
    self.selectedDate = new Date();


    self.monthsName = ['января', 'февраля', 'марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];


    self.productManager = EntityFactory('Product');

    self.isProductEditing = false;
    self.editProduct = editProduct;
    self.saveProduct = saveProduct;
    self.deleteProduct = deleteProduct;
    self.addProduct = addProduct;
    self.saveOrder = saveOrder;
    self.deleteOrder = deleteOrder;
    self.cancelProduct = cancelProduct;

    self.addDay = addDay;
    self.deleteDay = deleteDay;

    self.dateChange = dateChange;

    self.loading = {stocks:false, orderData:false, couriers:false, orderDayProduct:false};
    self.buffer = {};
    self.phoneMask = '+7 (999) 999-99-99';
    self.orderStatuses = ['Выполнен', 'Выполняется', 'Отменен'];
    self.orderDayStatuses = ['Выполнен','Выполняется','Отправлен на кухню', 'Отменен'];
    self.deliveryTypes = [{Id:1, Name:'Самовывоз'}, {Id:2, Name:'Курьером'}];
    self.selectedOrderDay = undefined;
    self.stocksManager = EntityFactory('Stock');
    self.stocks = [];
    self.nullStock = {Id:0, Name:'Не указана'};
    self.stocksSelect = [];

    self.ordersManager = EntityFactory('Order');

    self.couriersManager = EntityFactory('Courier');
    self.couriers = [];
    self.nullCourier = {Id:0, Name:'Не назначен'};
    self.couriersSelect = [];

    self.cities = [];
    self.orderDaysSelect = [];


    self.onOrderStatusSelect = onOrderStatusSelect;
    self.onOrderDaySelect = onOrderDaySelect;
    self.onOrderDayStatusSelect = onOrderDayStatusSelect;
    self.onOrderDayDeliveryTypeSelect = onOrderDayDeliveryTypeSelect;
    self.onOrderDayCitySelect = onOrderDayCitySelect;
    self.onOrderDayStockSelect = onOrderDayStockSelect;
    self.onOrderDayCourierSelect = onOrderDayCourierSelect;

    self.sendMail = sendMail;
    self.recount = recount;
    self.save = save;

    self.deleteOrderDayProduct = deleteOrderDayProduct;
    self.addOrderDayProduct = addOrderDayProduct;

    self.saveOrderDay = saveOrderDay;
    self.addDay = addDay;

    self.deleteActiveOrderDay = deleteActiveOrderDay;


    $scope.$on('page:citiesChanged', function (e, cities) {
        self.cities = cities;
    });
    $scope.$on('page:edit', onOrderSelect);
    $scope.$on('ProductSelect.selected', onProductSelect);


    activate();
    function activate(){
        self.loading.stocks = true;
        self.stocksManager.getAll().then(function (list) {
            self.stocks = list;
            self.loading.stocks = false;
        }, function(response){
            Notification.error({message:'Ошибка при загрузке точек самовывоза', delay: 1000, positionY: 'bottom', positionX: 'right'});
            console.error('order.orders.item: Error when load stocks.', response);
            self.loading.stocks = false;
        });

        self.loading.couriers = true;
        self.couriersManager.getAll().then(function (list) {
            self.couriers = list;
            self.loading.couriers = false;
        }, function(response){
            Notification.error({message:'Ошибка при загрузке списка курьеров', delay: 1000, positionY: 'bottom', positionX: 'right'});
            console.error('order.orders.item: Error when load couriers.', response);
            self.loading.couriers = false;
        });

    }

    function onOrderSelect(event, data){
        self.loading.orderData = true;
        BackendService.get({module:'Food', controller:'Admin\\Order', action:'getOrderData', OrderId:data.Order.Id}).then(function(response){
            self.loading.orderData = false;
            var responseData = response.data;
            if (responseData.status === 'OK'){
                self.order = angular.extend({}, data.Order);
                self.order.PromoCodeData = angular.fromJson(self.order.PromoCodeData);
                self.order._dateCreated = self.order.DateCreated;
                self.order.DateCreated = self.order.DateCreated.format('DD.MM.YYYY HH:mm:SS');
                //self.order.Price = parseFloat(self.order.Price);
                self.orderDays = response.data.data;
                if (self.orderDays.length == 0){
                    Notification.warning({message:'Ошибка при загрузке данных о заказе', delay: 1000, positionY: 'bottom', positionX: 'right'});
                    //return;
                }
                buildOrdDaySelect();

                //self.activeOrderDay = self.orderDaysSelect[0];
                self.onOrderDaySelect(self.orderDaysSelect[0], {}, false, true);
                //self.onOrderDaySelect();
                $scope.OrderEditForm.$setPristine();
            }else if (responseData.status === 'error'){
                //$idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При загрузке данных заказа возникла ошибка!', title:'Ошибка'}});
                Notification.error({message:'Ошибка при загрузке данных о заказе', delay: 1000, positionY: 'bottom', positionX: 'right'});
                console.error('order.orders.item: Error when load order data.', responseData.error);
            }else{
                //$idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Неизвестная ошибка!', title:'Ошибка'}});
                Notification.error({message:'Ошибка при загрузке данных о заказе', delay: 1000, positionY: 'bottom', positionX: 'right'});
                console.error('order.orders.item: Error when load order data.', response);
            }

        },function (response) {
            Notification.error({message:'Ошибка при загрузке данных о заказе', delay: 1000, positionY: 'bottom', positionX: 'right'});
            console.error('order.orders.item: Error when load order data.', response);
            self.loading.orderData = false;
        });
    }
    
    function onOrderDaySelect($item, $event, $isNull, $notSetDirty) {
        self.selectedOrderDay = $item;
        //console.log($item);
        self.activeOrderDay.editingProduct = undefined;
        self.activeOrderDay = self.orderDays[$item.index];
        //console.log(self.activeOrderDay.DeliveryType, self.deliveryTypes);
        self.activeOrderDay.DeliveryType = self.deliveryTypes.filter(function(item, i, arr){
            return item.Name == self.activeOrderDay.DeliveryType || angular.equals(item, self.activeOrderDay.DeliveryType);
        })[0];
        //console.log(self.activeOrderDay.DeliveryType);
        self.activeOrderDay.City = findById(self.cities, self.activeOrderDay.CityId);
        self.activeOrderDay.Stock = findById(self.stocks, self.activeOrderDay.StockId) || self.nullStock;
        self.activeOrderDay.Courier = findById(self.couriers, self.activeOrderDay.CourierId) || self.nullCourier;

        updateStocksList();
        updateCouriersList();
        if ($notSetDirty !== true){
            $scope.OrderEditForm.$setDirty();
        }
    }

    function onOrderStatusSelect($item, $event, $isNull) {
        $scope.OrderEditForm.$setDirty();
    }

    function onOrderDayStatusSelect($item, $event, $isNull) {
        $scope.OrderEditForm.$setDirty();
    }

    function onOrderDayCitySelect($item, $event, $isNull){
        self.activeOrderDay.CityId = self.activeOrderDay.City.Id;
        $scope.OrderEditForm.$setDirty();
        updateStocksList();
        updateCouriersList();
    }

    function onOrderDayDeliveryTypeSelect($item, $event, $isNull) {
        $scope.OrderEditForm.$setDirty();
    }

    function onOrderDayStockSelect($item, $event, $isNull) {
        self.activeOrderDay.StockId = $item.Id;
        $scope.OrderEditForm.$setDirty();
    }

    function onOrderDayCourierSelect($item, $event, $isNull) {
        self.activeOrderDay.CourierId = $item.Id;
        $scope.OrderEditForm.$setDirty();
    }

    function buildOrdDaySelect() {
        self.orderDaysSelect = [];
        for (var i=0;i<self.orderDays.length;i++){
            self.orderDays[i].Products.forEach(function (item, i, arr) {
                item.Deleted = false;
            });
            self.orderDaysSelect.push({
                value:self.orderDays[i].DeliveryDate,
                label:moment(self.orderDays[i].DeliveryDate).format('D MMMM'),
                index:i
            });
        }
    }

    function findById(arr, id) {
        for (var i=0;i<arr.length;++i){
            if (id == arr[i].Id){
                return arr[i];
            }
        }
        return false;
    }

    function updateStocksList() {
        self.stocksSelect = self.stocks.filter(function(item, i, arr){
            return item.CityId == self.activeOrderDay.CityId;
        });
    }

    function updateCouriersList() {
        self.couriersSelect = self.couriers.filter(function(item, i, arr){
            return item.CityId == self.activeOrderDay.CityId;
        });
    }

    function deleteOrderDayProduct(product, $table, $index) {
        //TODO: Sdelat
        console.log('delete', self.activeOrderDay.Products, product, $index);
        product.Deleted = true;
    }

    function hasProduct(od, p) {
        for (var i=0;i<od.Products.length;++i){
            if (od.Products[i].Product.Id== p.Id){
                return od.Products[i];
            }
        }
        return false;
    }
    function ucfirst( str ) {
        var f = str.charAt(0).toUpperCase();
        return f + str.substr(1, str.length-1);
    }


    function addOrderDayProduct() {
        //TODO: Sdelat
        $idialog('shop/products-list.html',{dialogId:'selectOrderDayProduct', options:{
            onSubmit:function(scope, ctrl){
                //console.log('Select : ', ctrl.selectedProducts, self.activeOrderDay.Products);
                var t;
                for(var i=0;i<ctrl.selectedProducts.length;++i){
                    t = hasProduct(self.activeOrderDay, ctrl.selectedProducts[i]);
                    if (false === t){
                        //console.log(t, ctrl.selectedProducts);
                        self.activeOrderDay.Products.push({
                            Id:null,
                            Amount:1,
                            OrderDayId:self.activeOrderDay.Id,
                            Price:parseFloat(ctrl.selectedProducts[i]['Price'+ucfirst(self.activeOrderDay.City.Machine)]),
                            ProductId:ctrl.selectedProducts[i].Id,
                            Product:ctrl.selectedProducts[i],
                            Deleted:false
                        });
                        //console.log('in products', ctrl.selectedProducts[i]);
                    }else if (t.Deleted){
                        t.Deleted = false;
                        //console.log('not in products', ctrl.selectedProducts[i]);
                    }
                }
                ctrl.clearSelectedProducts();
                $scope.OrderEditForm.$setDirty();
            }
        }});
    }

    function saveOrderDay(od) {
        BackendService.get({module:'Food', controller:'Admin\\Order', action:'saveOrderDay', OrderDay:od});
    }

    function sendMail() {
        BackendService.get({module:'Food', controller:'Admin\\Test', action:'sendMail', OrderId:self.order.Id}).then(function(response){
            console.log(response);
        });
    }

    function recount() {
        BackendService.send({OrderId:self.order.Id, Order:self.order, OrderDay:self.orderDays}, {module:'Food', controller:'Admin\\Test', action:'recountOrder'}).then(function(response){
            console.log(response);
        });
    }

    function save() {
        self.loading.orderData = true;
        var order = angular.copy(self.order), orderDays = angular.copy(self.orderDays);
        order.PromoCodeData = angular.toJson(order.PromoCodeData);
        order.DateCreated = order._dateCreated.format('YYYY-MM-DD HH:mm:SS');

        orderDays.forEach(function (od, i, arr) {
            if (typeof od.DeliveryType == 'object'){
                od.DeliveryType = od.DeliveryType.Name;
            }
            delete od.City;
            delete od.Courier;
            delete od.Stock;
            od.Products.forEach(function(p, i, arr){
                delete p.Product;
            });
        });
        BackendService.send({Order:order, OrderDays:orderDays}, {module:'Food', controller:'Admin\\Test', action:'saveOrder'}).then(function(response){
            var responseData = response.data;
            if (responseData.status == 'OK'){
                var result = responseData.data;
                var products = angular.copy(result.Products);
                delete result.Products;
                self.ordersManager.addEntity(result);
                self.order = angular.extend({},result);
                //console.log(self.order);
                self.order.PromoCodeData = angular.fromJson(self.order.PromoCodeData);
                //self.order.DateCreated = moment(self.order.DateCreated, 'YYYY-MM-DD HH:mm:ss');
                self.order._dateCreated = moment(self.order.DateCreated, 'YYYY-MM-DD HH:mm:ss');
                self.order.DateCreated = self.order._dateCreated.format('DD.MM.YYYY HH:mm:SS');
                //self.order.Price = parseFloat(self.order.Price);
                self.orderDays = products;
                if (self.orderDays.length == 0){
                    Notification.warning({message:'Ошибка при загрузке данных о заказе', delay: 1000, positionY: 'bottom', positionX: 'right'});
                    //return;
                }
                buildOrdDaySelect();

                //self.activeOrderDay = self.orderDaysSelect[0];
                self.onOrderDaySelect(self.orderDaysSelect[0], {}, false, true);
                //console.log(self.activeOrderDay);
                //self.onOrderDaySelect();
                $scope.OrderEditForm.$setPristine();
                $rootScope.$broadcast('order:orders:item:saved', result);
            }else if (responseData.status === 'error'){
                //$idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При загрузке данных заказа возникла ошибка!', title:'Ошибка'}});
                Notification.error({message:'Ошибка при сохранении данных о заказе', delay: 1000, positionY: 'bottom', positionX: 'right'});
                console.error('order.orders.item: Error when load order data.', responseData.error);
            }else{
                //$idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Неизвестная ошибка!', title:'Ошибка'}});
                Notification.error({message:'Ошибка при сохранении данных о заказе', delay: 1000, positionY: 'bottom', positionX: 'right'});
                console.error('order.orders.item: Error when load order data.', response);
            }
            self.loading.orderData = false;
        }, function (response) {self.loading.orderData = false;});
    }

    function addDay(){
        $idialog('add-day',{dialogId: 'addDayDialog', options:{
            title:'Добавление дня',
            date: new Date(),
            add:function($scope){
                var d = this.date.format('YYYY-MM-DD');
                BackendService.get({module:'Food', controller:'Admin\\Test', action:'createOrderDay', OrderId:self.order.Id, Date:d}).then(function(response){
                    var responseData = response.data,t;
                    if (responseData.status == 'OK'){
                        t = new Date(d);
                        var orderDay = responseData.data;
                        console.log('od', orderDay);

                        //responseData.data.Products = [];
                        self.orderDays.push(orderDay);
                        //buildOrdDaySelect();
                        self.orderDaysSelect.push({
                            value:orderDay.DeliveryDate,
                            label:moment(orderDay.DeliveryDate).format('D MMMM'),
                            index:0
                        });
                        self.orderDaysSelect[self.orderDaysSelect.length - 1].index = self.orderDaysSelect.length - 1;
                        //self.orderDaysList.setActiveItem(self.orderDays[self.orderDays.length-1]);
                    }else{
                        $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При добавлении дня возникла ошибка!', title:'Ошибка'}});
                        console.error(responseData.error);
                    }
                    $scope.hide();
                });

            }
        }});
    }

    function deleteActiveOrderDay() {

    }

    //----
    function onProductSelect(event, data){
        BackendService.get({module:'Food', controller:'Admin\\Order', action:'addProduct', OrderDayId:self.activeOrderDay.Id, ProductId:data.Product.Id, Amount:data.Amount, CityId:self.order.CityId}).then(function(response){
            var responseData = response.data;
            if (responseData.status == 'OK'){
                responseData.data.OrderDayProduct.Product = data.Product;
                self.activeOrderDay.Products.push(responseData.data.OrderDayProduct);
                self.activeOrderDay.Price = responseData.data.OrderDay.Price;
                $scope.OrderEditForm.$setDirty();
            }else if (responseData.status === 'error'){
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При добавлении товара возникла ошибка!', title:'Ошибка'}});
                console.error(responseData.error);
            }else{
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Неизвестная ошибка!', title:'Ошибка'}});
            }
        });
    }





    function dateChange(){
        console.log('Change', self.selectedDate.format('YYYY-MM-DD'));

    }

    function deleteDay(){

    }

    function editProduct(product){
        self.temp = product;
        self.editingProduct = {OriginalProduct:product, Product:angular.extend({},product)};
        //self.editingProduct = product;
    }

    function saveProduct(){
        BackendService.send({Product:self.editingProduct.Product}, {module:'Food', controller:'Admin\\Order', action:'saveOrderDayProduct'}).then(function(response){
            var responseData = response.data;
            if (responseData.status == 'OK'){
                self.temp = angular.extend(self.temp, self.editingProduct.Product);
                self.editingProduct.OriginalProduct = self.editingProduct.Product;
                delete responseData.data.Products;
                self.activeOrderDay = angular.extend(self.activeOrderDay, responseData.data);
                console.log(responseData.data);
                self.editingProduct = {Product:{},OriginalProduct:{}};
            }else if (responseData.status === 'error'){
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При сохранении товара возникла ошибка!', title:'Ошибка'}});
                console.error(responseData.error);
            }else{
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Неизвестная ошибка!', title:'Ошибка'}});
            }
        });
    }

    function cancelProduct(){
        self.editingProduct = {Product:{},OriginalProduct:{}};
    }

    function deleteProduct(product){
        var action = function($scope){
            BackendService.get({module:'Food', controller:'Admin\\Order', action:'deleteOrderDayProduct', Id: product.Id}).then(function (response) {
                var responseData = response.data;
                $scope.hide();
                if (responseData.status == 'OK'){
                    self.activeOrderDay.Price = response.data.Price;
                    self.activeOrderDay.Products.splice(self.activeOrderDay.Products.indexOf(product), 1);
                    $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Товар удален из заказа.'}});
                }else if(responseData.status == 'error'){
                    $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При удалении товара возникла ошибка!', title:'Ошибка'}});
                    console.error(responseData.error);
                }else{
                    $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Неизвестная ошибка!', title:'Ошибка'}});
                }
            });
        };
        $idialog('confirm-dialog',{
            dialogId: 'deleteOrder',
            options:{
                title:'Удалить товар?',
                yesCb:function($scope){
                    action($scope);
                }}
        });

    }

    function addProduct(){
        $idialog('products-list', {dialogId:'productsListDialog', options:{}});
    }

    function saveOrder(){
        BackendService.send({Order:self.order, OrderDays:self.orderDays}, {module:'Food', controller:'Admin\\Order', action:'saveOrder'}).then(function(response){
            var responseData = response.data;
            if (responseData.status === 'OK'){
                $rootScope.$broadcast('Orders.OrderSaved', {Order:responseData.data});
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Заказ сохранен!'}});
            }else if (responseData.status === 'error'){
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При сохранении заказа возникла ошибка!', title:'Ошибка'}});
                console.error(responseData.error);
            }else{
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Неизвестная ошибка!', title:'Ошибка'}});
            }
        });
    }

    function deleteOrder(){
        $idialog('confirm-dialog',{
            dialogId: 'deleteOrder',
            options:{
                title:'Удалить заказ?',
                yesCb:function($scope){
                    BackendService.get({module:'Food', controller:'Admin\\Order', action:'deleteOrder', OrderId: self.order.Id}).then(function (response) {
                        var responseData = response.data;
                        $scope.hide();
                        var msg = 'Неизвестная ошибка!', title='Ошибка';
                        if (responseData.status === 'OK'){
                            msg = 'Заказ успешно удален!'; title='Сообщение';
                            self.order = {};
                            self.orderDays = [];
                            self.orderDaysSelect = [];
                            self.activeOrderDay = {};
                            self.editingProduct = {};
                            $rootScope.$broadcast('Orders.OrderDeleted', {});
                        }else if (responseData.status === 'error'){
                            msg = 'При удалении заказа возникла ошибка!';
                            console.error(responseData.error);
                        }
                        $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:msg, title:title}});
                    });
                }}
        });
    }

};

adminApp.controller('order.orders.item', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', 'Notification', '$timeout', OrderItemController]);
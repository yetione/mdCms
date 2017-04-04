var OrderItemController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;
    self.order = {};
    self.orderDays = [];
    self.orderDaysSelect = [];
    self.activeOrderDay = {};
    self.editingProduct = {Product:{},OriginalProduct:{}};

    self.showDatepicker = false;
    self.selectedDate = new Date();

    self.orderStatuses = ['Выполняется', 'Выполнен'];
    self.monthsName = ['января', 'февраля', 'марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    self.orderDayStatuses = ['Выполнен','Выполняется','Отправлен на кухню'];

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


    $scope.$on('Orders.orderSelect', onOrderSelect);
    $scope.$on('ProductSelect.selected', onProductSelect);

    activate();
    function activate(){
        self.orderStatusesList = new CustomSelectBox(self.orderStatuses[0], function(item){
            self.order.Status = item;
            $scope.OrderEditForm.$setDirty();
        });

        self.orderDaysList  = new CustomSelectBox(self.orderDaysSelect[0], function(item){
            self.activeOrderDay = self.orderDays[item.index];
            self.orderDayStatusesList.setActiveItem(self.activeOrderDay.Status);
            $scope.OrderEditForm.$setDirty();
        });

        self.orderDayStatusesList = new CustomSelectBox(self.orderDayStatuses[0], function(item){
            self.activeOrderDay.Status = item;
            $scope.OrderEditForm.$setDirty();
        });
    }

    function onOrderSelect(event, data){
        BackendService.get({module:'Food', controller:'Admin\\Order', action:'getOrderData', OrderId:data.Order.Id}).then(function(response){
            var responseData = response.data;
            if (responseData.status === 'OK'){
                self.order = angular.extend({},data.Order);
                self.orderStatusesList.setActiveItem(self.order.Status);
                self.orderDays = response.data.data;
                if (self.orderDays.length == 0){
                    console.log('nol');
                    //TODO: errors
                    return;
                }
                var t,l;
                self.orderDaysSelect = [];
                for (var i=0;i<self.orderDays.length;i++){
                    t = new Date(self.orderDays[i].DeliveryDate);
                    l = t.getDate()+' '+self.monthsName[t.getMonth()];
                    self.orderDaysSelect.push({value:self.orderDays[i].DeliveryDate, label:l, index:i});
                }
                self.orderDaysList.setActiveItem(self.orderDaysSelect[0]);
                self.orderDayStatusesList.setActiveItem(self.activeOrderDay.Status);
                $scope.OrderEditForm.$setPristine();
            }else if (responseData.status === 'error'){
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При загрузке данных заказа возникла ошибка!', title:'Ошибка'}});
                console.error(responseData.error);
            }else{
                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Неизвестная ошибка!', title:'Ошибка'}});
            }

        });
    }

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

    function addDay(){
        $idialog('add-day',{dialogId: 'addDayDialog', options:{
            title:'Добавление дня',
            date: new Date(),
            add:function($scope){
                var d = this.date.format('YYYY-MM-DD');
                BackendService.get({module:'Food', controller:'Admin\\Order', action:'createOrderDay', OrderId:self.order.Id, Date:d}).then(function(response){
                    var responseData = response.data,t;
                    if (responseData.status == 'OK'){
                        t = new Date(d);
                        responseData.data.Products = [];
                        self.orderDays.push(responseData.data);
                        self.orderDaysSelect.push({value:d, label:t.getDate()+' '+self.monthsName[t.getMonth()], index:self.orderDaysSelect.length});
                        self.orderDaysList.setActiveItem(self.orderDays[self.orderDays.length-1]);
                    }else{
                        $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'При добавлении дня возникла ошибка!', title:'Ошибка'}});
                        console.error(responseData.error);
                    }
                    $scope.hide();
                });

            }
        }});
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

adminApp.controller('order.item', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrderItemController]);
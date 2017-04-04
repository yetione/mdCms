var OrderDocumentsOrdersListController = function($scope, $rootScope, BackendService, EntityFactory){
    var self = this;

    self.date = null;
    self.cityId = null;
    self.orderDayManager = EntityFactory('OrderDay');

    self.orderStatuses = ['Выполнен','Выполняется','Отправлен на кухню'];
    self.deliveryTypes = ['Курьером', 'Самовывоз'];
    self.paymentTypes = ['Оплата курьеру', 'Банковской картой'];
    self.filters = {Status:'',DeliveryType:'',PaymentType:'', Street:''};


    self.options = {};
    self.list = [];
    self.model = [];
    self.ordersList = null;
    self.listIsUpdating = false;



    self.selectOrder = selectOrder;
    self.add = add;

    activate();
    function activate(){
        self.orderStatusesList = new ListObject({
            onItemSelect:function(index){
                self.filters.Status = self.orderStatusesList.getActiveItem();
                self.orderStatusesList.header = self.orderStatusesList.getActiveItem();
            },
            onSetNull:function(){
                self.orderStatusesList.activeItem = -1;
                self.orderStatusesList.header = 'Любой стаус';
                self.filters.Status = '';
            }
        });
        self.orderStatusesList.setItems(self.orderStatuses);
        self.orderStatusesList.header = 'Статус заказа';

        self.deliveryTypesList = new ListObject({
            onItemSelect:function(index){
                self.filters.DeliveryType = self.deliveryTypesList.getActiveItem();
                self.deliveryTypesList.header = self.deliveryTypesList.getActiveItem();
            },
            onSetNull:function(){
                self.deliveryTypesList.activeItem = -1;
                self.deliveryTypesList.header = 'Любой способ';
                self.filters.DeliveryType = '';
            }
        });
        self.deliveryTypesList.setItems(self.deliveryTypes);
        self.deliveryTypesList.header = 'Способ доставки';

        self.paymentTypesList = new ListObject({
            onItemSelect:function(index){
                self.filters.PaymentType = self.paymentTypesList.getActiveItem();
                self.paymentTypesList.header = self.paymentTypesList.getActiveItem();
            },
            onSetNull:function(){
                self.paymentTypesList.activeItem = -1;
                self.paymentTypesList.header = 'Любой тип';
                self.filters.PaymentType = '';
            }
        });
        self.paymentTypesList.setItems(self.paymentTypes);
        self.paymentTypesList.header = 'Тип оплаты';

        self.options = angular.extend({Day:{}, City:{}, onAdd:function(items){}},$scope.dialogOptions);
        updateList();
    }

    function updateList(){
        self.listIsUpdating = true;
        return self.orderDayManager.getList({DeliveryDate:self.options.Day.Date, Order:{CityId:self.options.City.Id}}).then(function(list){
            self.list = list;
            self.model = self.list.map(function(item, i, a){return false});
            self.listIsUpdating = false;
        });
    }

    function selectOrder(order){
        order.Selected = !order.Selected;
        //self.model[index] = !self.model[index];
    }

    function add(){
        var result = [];
        for (var i=0;i<self.list.length;i++){
            if (self.list[i].Selected === true){
                result.push(self.list[i]);
            }
        }
        self.options.onAdd(result);
        $scope.hide();
    }
};

adminApp.controller('order.documents.ordersList', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', OrderDocumentsOrdersListController]);
(function (windows, angular) {
    var OrderDataService = function($rootScope, BackendService, $q, $timeout){
        var self = this;
        self.config = {};

        self.deliveryType = ['Курьером', 'Самовывоз'];
        self.paymentTypes = ['Наличными курьеру', 'Банковской картой'];
        self.orderDayStatuses = ['Выполнен','Выполняется','Отправлен на кухню'];
        self.orderStatuses = ['Выполняется', 'Выполнен'];

        self.run = run;

        function run(configs){
            angular.extend(self.config, configs);
        }

    };


    angular.module('mdShop', ['mdBackend'])
        .service('OrderDataService', ['$rootScope', 'BackendService', '$q', '$timeout', OrderDataService]);
})(window, window.angular);
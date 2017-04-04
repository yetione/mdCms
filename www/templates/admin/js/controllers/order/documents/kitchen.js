var OrderDocumentsKitchenController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;
    self.activeCity = {Id:0};
    self.activeDay = {Date:''};
    self.couriersManager = EntityFactory('Courier');
    self.citiesManager = EntityFactory('City');
    self.productsLink = '';
    self.ordersLink = '';

    self.generate = generate;
    self.generatePDFLinks = generatePDFLinks;

    self.ordersPDFLink = '';
    self.productsPDFLink = '';


    activate();
    function activate(){
        reset();
    }

    $scope.$on('Order.Documents.CityChange', onCityChange);
    function onCityChange(event, data){
        var City = data.City;
        if (self.activeCity.Id == City.Id){return;}
        self.activeCity = City;
        self.activeDay = {Date:''};
        reset();
    }

    $scope.$on('Order.Documents.DaysList.DaySelected', onDaySelected);
    function onDaySelected(event, data){
        var Day = data.Day;
        if (self.activeDay.Date == Day.Date){return;}
        self.activeDay = Day;
        reset();
    }

    function reset(){
        self.productsLink = '';
        self.ordersLink = '';
        self.ordersPDFLink = '';
        self.productsPDFLink = '';
    }

    function generate(){
        BackendService.get({module:'Food', controller:'Admin\\Documents', action:'generateKitchenReports',
            Date:self.activeDay.Moment.format('YYYY-MM-DD'),
            City:self.activeCity['Id']
        }).then(function(response){
            var responseData = response.data;
            if (responseData.status == 'OK'){
                self.ordersLink = responseData.data.ODRPath;
                self.productsLink = responseData.data.ProductsPath;
            }

        });
    }

    function generatePDFLinks(){
        self.ordersPDFLink = BackendService.buildUrl('json.php', {module:'Food', controller:'Admin\\Documents', action:'generateKitchenOrdersPDF',
            Date:self.activeDay.Moment.format('YYYY-MM-DD'),
            City:self.activeCity['Id']
        });

        self.productsPDFLink = BackendService.buildUrl('json.php', {module:'Food', controller:'Admin\\Documents', action:'generateKitchenProductsPDF',
            Date:self.activeDay.Moment.format('YYYY-MM-DD'),
            City:self.activeCity['Id']
        });

    }

};

adminApp.controller('order.documents.Kitchen', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', OrderDocumentsKitchenController]);
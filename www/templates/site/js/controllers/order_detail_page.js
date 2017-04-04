var OrderDetailPageController = function($scope, GeoLocationService){
    var self = this;

    self.orderData = angular.fromJson(OrderEntity());
    self.citiesList = [];

    self.getShortDate = getShortDate;
    self.getCityName = getCityName;

    activate();
    function activate() {
        self.citiesList = GeoLocationService.getCities().then(function(cities){
            self.citiesList = cities;
        });
    }

    function getShortDate(date){
        return moment(date).format('dd,LL');
    }

    function getCityName(cityId) {
        for(var i=0;i<self.citiesList.length;++i){
            if (self.citiesList[i].Id == cityId){
                return self.citiesList[i].Name;
            }
        }
        return '';
    }
};

siteApp.controller('OrderDetailPageController', ['$scope', 'GeoLocationService', OrderDetailPageController]);
var CitiesController = function($scope, $cookies, $idialog, EntityFactory, GeoLocationService){
    var self = this;
    self.tooltipKey = 'Cities.showTooltip';
    self.currentCity = false;
    self.selectCitiDilogOpened = false;
    self.confirmCityDialogOpened = false;

    self.showTooltip = false;

    self.showSelectCityDialog = showSelectCityDialog;
    $scope.$on('GeoLocationService:currentCityIsNotValid', onCurrentCityIsNotValid);
    $scope.$on('iDialogHide', onDialogHide);


    activate();
    function activate(){

        self.currentCity = GeoLocationService.getCurrentCity();
        if (!$cookies.get(self.tooltipKey) && !self.confirmCityDialogOpened && self.showTooltip){
            self.confirmCityDialogOpened = true;
            $idialog('is-detected-city',{dialogId:'isDetectedCityDialog', options:{
                currentCity:GeoLocationService.getCurrentCity(),
                yes:function($scope){
                    self.confirmCityDialogOpened = false;
                    GeoLocationService.checkCurrentCity();
                    $cookies.put(self.tooltipKey, 1);
                    $scope.hide();
                },
                no:function($scope){
                    self.confirmCityDialogOpened = false;
                    $scope.hide();
                    showSelectCityDialog();
                }

            }});
        }
    }

    function showSelectCityDialog(){
        if (!self.selectCitiDilogOpened && !self.confirmCityDialogOpened){
            self.selectCitiDilogOpened = true;
            $idialog('city-select-dialog',{dialogId:'citySelectDialog', class:'select-city', options:{
                cities:GeoLocationService.cities,
                selectCity:function($scope, city){
                    $cookies.put(self.tooltipKey, 1);
                    GeoLocationService.changeCurrentCity(city.Id).then(function(response){
                        self.currentCity = GeoLocationService.getCurrentCity();
                        self.selectCitiDilogOpened = false;
                    });
                    $scope.hide();
                }

            }});
        }

    }


    function onDialogHide(event, dialogId) {
        if (dialogId == 'citySelectDialog'){
            self.selectCitiDilogOpened = false;
        }else if (dialogId == 'isDetectedCityDialog'){
            self.confirmCityDialogOpened = false;
        }
    }

    function onCurrentCityIsNotValid(event, data) {
        showSelectCityDialog();
    }

};

siteApp.controller('CitiesController', ['$scope', '$cookies', '$idialog', 'EntityFactory', 'GeoLocationService',  CitiesController]);
/**
 *
 * @param $scope
 * @param BackendService
 * @param $idialog
 * @param  UsersService
 * @param  KladrService
 * @param  GeoLocationService
 * @constructor
 */
var ProfilePageController = function($scope, BackendService, $idialog, UsersService, KladrService, GeoLocationService){
    var self = this;

    self.currentUser = {};
    self.savingProfileData = false;

    self.ordersList = [];
    self.citiesList = [];
    self.metroStations = {};
    self.phoneMask = '+7 (999) 999-99-99';

    self.saveAddress = saveAddress;
    self.deleteAddress = deleteAddress;
    self.addAddress = addAddress;
    self.editAddress = editAddress;
    self.cancelEdit = cancelEdit;
    self.getShortDate = getShortDate;
    self.streetChange = streetChange;
    self.selectStreet = selectStreet;
    self.metroStationChange = metroStationChange;
    self.selectMetroStation = selectMetroStation;
    self.getCityMachine = getCityMachine;
    self.saveUserData = saveUserData;


    activate();
    function activate(){
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
            self.currentUser.getAddresses().then(function (addresses) {
                self.currentUser.Addresses = addresses.map(function (item) {
                    return angular.extend(item, {IsEdit:false});
                });
            });
        });
        GeoLocationService.getCities().then(function (cities) {
            self.citiesList = cities;
            var dC = GeoLocationService.getCurrentCity();
            for(var i=0;i<self.citiesList.length;++i){
                if (self.citiesList[i].Id == dC.Id){
                    self.currentCity = self.citiesList[i];
                    break;
                }
            }
        });

        UsersService.getOrders().then(function (orders) {
            self.ordersList = orders;
        });
        self.metroStations = GeoLocationService.metroStations;
        var temp;
        for (var m in self.metroStations){
            temp = [];
            for(var j=0;j<self.metroStations[m].length;++j){
                temp = temp.concat(self.metroStations[m][j].stations);
            }
            self.metroStations[m] = temp;
        }
    }

    function editAddress(address) {
        address.IsEdit = true;
        address._original = angular.copy(address);
        address.foundedStreets = [];
        address.showMetroStationsList = false;


    }

    function streetChange(address) {
        if (address.Street.length > 1){
            var city;
            for (var i=0;i<self.citiesList.length;++i){
                if (address.CityId == self.citiesList[i].Id){
                    city = self.citiesList[i];
                    break;
                }
            }
            var query = KladrService.getQuery({query:address.Street, contentType:KladrService.type.street, cityId:city.Okato});
            KladrService.execute(query).then(function(response){
                console.log(response);
                address.foundedStreets = response.result;
            });
        }else{
            address.foundedStreets = [];
        }
    }

    function selectStreet(address, street){
        address.Street = street.typeShort+'. '+street.name;
        address.foundedStreets = [];
    }

    function cancelEdit(address) {
        address.IsEdit = false;
        if (address._original){
            address = angular.copy(address._original, address);
            address.IsEdit = false;
        }
        if ('_index' in address && address._index > -1 && !parseInt(address.Id)){
            self.currentUser.Addresses.splice(address._index, 1);
        }
    }

    function saveAddress(address) {
        if (address.Name.trim() == '' || address.Street.trim() == ''){
            $idialog('common/message', {dialogId:'formNotValid', options:{title:'Ошибка', message:'Не заполнены обязательные поля.'}});
            return;
        }
        console.log('save');
        self.currentUser.saveAddress(address).then(function (addresses) {
            address.IsEdit = false;
            self.currentUser.getAddresses();
            if ('_index' in address){
                delete address._index;
            }
        });
    }

    function deleteAddress(address) {
        $idialog('common/confirm', {dialogId:'confirmAddressDelete', options:{title:'Удаление', message:'Вы подтверждаете удаление адреса?',
        yesCb:function(scope){
            self.currentUser.deleteAddress(address).then(function (addresses) {
                self.currentUser.getAddresses();
            });
            scope.hide();
        },
        noCb:function(scope){
            scope.hide();
        }}});
    }

    function getShortDate(date) {
        return moment(date).format('LL')
    }

    function addAddress() {
        var Address = {
            UserId:0,
            CityId:self.currentCity.Id,
            Name:'',
            Street:'',
            Building:'',
            Room:'',
            MetroStation:'',
            _index:-1,
        };
        self.currentUser.Addresses.push(Address);
        Address._index = self.currentUser.Addresses.length-1;
        editAddress(self.currentUser.Addresses[self.currentUser.Addresses.length-1]);
    }

    function metroStationChange(address) {
        address.showMetroStationsList = address.MetroStation.length > 2;
    }

    function selectMetroStation(address, s) {
        address.MetroStation = s;
        address.showMetroStationsList = false;
    }

    function getCityMachine(cityId) {
        for(var i=0;i<self.citiesList.length;++i){
            if (cityId == self.citiesList[i].Id){
                return self.citiesList[i].Machine;
            }
        }
        return false;
    }
    
    function saveUserData() {
        BackendService.get({module:'Users', controller:'UserService', action:'updateData', Data:{
            Email:self.currentUser.Email,
            Name:self.currentUser.Name,
            Surname:self.currentUser.Surname,
            Patronymic:self.currentUser.Patronymic,
            Phone:self.currentUser.Phone
        }}).then(function (response) {
            //console.log('saved', response);
            var result = response.data;
            if (result.status == 'OK'){
                $idialog('common/message', {dialogId:'savedUserInfo', options:{title:'Сообщение', message:'Данные обновлены.'}});
            }else if (result.status == 'error'){
                var msg = 'Неизвестная ошибка';
                var code = result.data.code;
                switch (code){
                    case 1:
                        msg='Пользователь не найден.';
                        break;
                    case 2:
                        msg='Данныее не переданы.';
                        break;
                    case 3:
                        msg='Не верно указан email.';
                        break;
                    case 4:
                        msg='Не верно указан телефон.';
                        break;
                    case 5:
                        msg='Имя не может быть пустым.';
                        break;
                    case 6:
                        msg='Ошибка при сохранении.';
                        break;
                    default:
                        msg = 'Неизвестная ошибка.';
                        break;
                }
                $idialog('common/message', {dialogId:'savedUserInfoError', options:{title:'Ошибка', message:msg}});
                console.error('Cant save user data', response);
            }
        });
    }
};

siteApp.controller('ProfilePageController', ['$scope', 'BackendService', '$idialog', 'UsersService', 'KladrService', 'GeoLocationService', ProfilePageController]);
var CartOrderFormController = function ($scope, CartService, UsersService, BackendService, $idialog, KladrService, GeoLocationService) {
    var self = this;

    self.phoneMask = '+7 (999) 999-99-99';
    self.deliveryTimeMask = '99:99';
    self.disableSubmit = false;

    self.currentUser = {Id:0};
    self.cities = [];

    self.foundedStreets = [];
    self.showMetroStationsList = false;
    self.activeDeliveryDay = false;
    self.recaptchaWidgetId = '';
    self.loading = {MetroStations:false, Streets:false};

    $scope.$on('Cart.loaded', onCartChange);
    $scope.$on('Cart.productAdd', onCartChange);
    $scope.$on('Cart.productRemove', onCartChange);
    $scope.$on('Cart.productChanged', onCartChange);
    $scope.$on('Cart.clear', onCartChange);
    $scope.$on('Cart.promoCodeActivate', onCartChange);
    $scope.$on('Cart.promoCodeDelete', onCartChange);
    $scope.$on('GeoLocationService:currentCityChange', onCityChange);

    self.daySelected = daySelected;
    self.typeSelected = typeSelected;
    self.doOrder = doOrder;

    self.streetChange = streetChange;
    self.selectStreet = selectStreet;
    self.metroStationChange = metroStationChange;
    self.selectMetroStation = selectMetroStation;
    self.addressSelected = addressSelected;
    self.citySelected = citySelected;
    self.onReCaptchaCreated = onReCaptchaCreated;
    self.metroStationSelected = metroStationSelected;
    self.streetSelected = streetSelected;
    self.refreshStreets = refreshStreets;

    self.changeStreet = changeStreet;

    activate();
    function activate(){
        self.cart = CartService.cart;
        self.orderType = 'ToAllDays';
        self.orderData = {
            Fio:'',
            Name:'',
            Surname:'',
            Email:'',
            Phone:'',
            ToAllDays:getEmptyDay(),
            UniqueAddresses:{},
            OrderType:self.orderType,
            AgreeOffer:false,
            ReCaptcha:''
        };
        self.daysList = [];
        typeSelected();
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
            self.currentUser.getAddresses();
            if (self.currentUser.Id > 0){
                self.orderData.Name = self.currentUser.Name;
                self.orderData.Surname = self.currentUser.Surname;
                self.orderData.Email = self.currentUser.Email;
                self.orderData.Phone = self.currentUser.Phone;
            }
        });
        GeoLocationService.getCities().then(function(cities){
            self.cities = cities;
            self.cities.forEach(function(item, i, arr){
                item.MetroStationsList = GeoLocationService.getCityMetroStation(item);
                item.MetroStationsList.sort();
                if (item.Id == GeoLocationService.getCurrentCity().Id){
                    setActiveCity(item);
                    self.activeDay.ActiveCity = self.activeCity;
                }
            });
        });
    }

    function addressSelected() {
        var address = self.activeDay.UserAddress;
        if (address !== null){
            self.activeDay.MetroStation = address.MetroStation;
            self.activeDay.Street = address.Street;
            self.activeDay.Building = address.Building;
            self.activeDay.Room = address.Room;
        }
    }

    function getEmptyDay(){
        return {Street:'', Building:'', Room:'', Comment:'',DeliveryTime:'', PersonsCount:'', MetroStation:'', MetroStationObject:null, StreetObject:null, UserAddress:null, ActiveCity:self.activeCity/*, AddressDetail:{}*/};
    }

    function streetChange(){
        if (self.activeDay.Street.length > 1){
            var query = KladrService.getQuery({query:self.activeDay.Street, contentType:KladrService.type.street, cityId:self.activeCity.Okato});
            KladrService.execute(query).then(function(response){
                self.foundedStreets = response.result;
            });
        }else{
            self.foundedStreets = [];
        }
    }

    function metroStationChange() {
        self.showMetroStationsList = self.activeDay.MetroStation.length > 2;
    }

    function selectMetroStation(s) {
        self.activeDay.MetroStation = s;
        self.showMetroStationsList = false;
    }

    function selectStreet(street){
        self.activeDay.Street = street.typeShort+'. '+street.name;
        self.foundedStreets = [];
    }

    function citySelected() {

    }

    function setActiveDay(day) {
        self.activeDay = day;
    }

    function daySelected(){
        if (!self.activeDeliveryDay){self.activeDeliveryDay = self.daysList[0];}
        if (!(self.activeDeliveryDay.value in self.orderData.UniqueAddresses)){
            self.orderData.UniqueAddresses[self.activeDeliveryDay.value] = getEmptyDay();
        }
        setActiveDay(self.orderData.UniqueAddresses[self.activeDeliveryDay.value]);
    }

    function metroStationSelected() {
        self.activeDay.MetroStation = self.activeDay.MetroStationObject.name;
    }

    function streetSelected($item, $model) {
        console.log('SS', self.activeDay.StreetObject, $item, $model);
        //self.foundedStreets = [];
        self.loading.Streets = false;
    }

    function changeStreet() {
        self.activeDay.StreetObject = null;
    }

    function refreshStreets(str) {
        console.log('refresh', str);
        var query = KladrService.getQuery({query:str, contentType:KladrService.type.street, cityId:self.activeDay.ActiveCity.Okato, withParent:true});
        self.loading.Streets = true;
        KladrService.execute(query).then(function(response){
            self.foundedStreets = response.result.map(function(item, i, arr){
                item.city = '';
                if (item.parents && item.parents.length){
                    for (var k=item.parents.length-1;k>-1;--k){
                        if (item.parents[k].name && item.parents[k].contentType == 'city') {
                            item.city = item.parents[k].typeShort + '. ' + item.parents[k].name;
                            break;
                        }
                    }
                }
                return item;
            });
            self.loading.Streets = false;
        });
    }

    function typeSelected(){
        self.orderData.OrderType = self.orderType;
        if (self.orderType == 'UniqueAddresses'){
            daySelected();
        }else if (self.orderType == 'ToAllDays'){
            setActiveDay(self.orderData.ToAllDays);
        }
    }

    function onCartChange(event, data){
        self.cart = CartService.cart;
        self.daysList = [];
        for (var date in self.cart.Data){
            self.daysList.push({value:date, label: moment(date).format('D MMMM')});
        }
    }

    function doOrder(){
        var formStatus = checkForm();
        if (formStatus > 0){
            $idialog('common/message',{dialogId:'orderFormError', options:{title:'Ошибка.',message:getFormErrorMessage(formStatus)}});
            return;
        }
        var orderData = angular.copy(self.orderData);
        if (orderData.OrderType == 'ToAllDays'){
            orderData.ToAllDays.CityId = orderData.ToAllDays.ActiveCity.Id;
            orderData.ToAllDays.MetroStation = orderData.ToAllDays.MetroStationObject.name;
            //orderData.ToAllDays.Street = orderData.ToAllDays.MetroStationObject.name;
            delete orderData.ToAllDays.ActiveCity;
            delete orderData.ToAllDays.UserAddress;
            delete orderData.ToAllDays.MetroStationObject;
            delete orderData.ToAllDays.StreetObject;
        }else if(orderData.OrderType == 'UniqueAddresses'){
            for (var day in orderData.UniqueAddresses){
                orderData.UniqueAddresses[day].CityId = orderData.UniqueAddresses[day].ActiveCity.Id;
                orderData.UniqueAddresses[day].MetroStation = orderData.UniqueAddresses[day].MetroStationObject.name;
                delete orderData.UniqueAddresses[day].ActiveCity;
                delete orderData.UniqueAddresses[day].UserAddress;
                delete orderData.UniqueAddresses[day].MetroStationObject;
                delete orderData.UniqueAddresses[day].StreetObject;
            }
        }
        self.disableSubmit = true;
        orderData.Fio = orderData.Name+' '+orderData.Surname;
        BackendService.send({Data:orderData}, {module:'Food', controller:'Order', action:'doOrder'}).then(function(response){
            var responseData = response.data;
            grecaptcha.reset(self.reCaptchaWidgetId);
            self.disableSubmit = false;
            if (responseData.status == 'OK'){
                $idialog('order-created',{dialogId:'orderCreated', options:{
                    hide:function($scope){$scope.hide()}
                }});
                CartService.clearCart();
            }else if(responseData.status == 'error'){
                var message = '';
                console.log('OrderFormController::doOrder : Cant save order.', response);
                switch (responseData.error.code){
                    case 1:
                        message = 'Неизвестный тип доставки.';
                        break;
                    case 2:
                        message = 'Неполные данные о доставке.';
                        break;
                    case 3:
                        message = 'Капча не прошла проверку';
                        break;
                    case 4:
                        message = 'Не заполнены обязательные поля заказа.';
                        break;
                    case 5:
                        message = 'Ошибка при оформлении заказа.';
                        break;
                    case 6:
                        message = 'Не заполнены обязательные поля доставки.';
                        break;
                    case 7:
                        message = 'Ошибка при сохранение дня товара.';
                        break;
                    default:
                        message = 'Неизвестная ошибка.';
                        break;
                }
                $idialog('common/message', {dialogId:'orderSaveError', options:{title:'Ошибка',message:message}})
            }
        });
    }

    function onCityChange(event, data) {
        setActiveCity(data.City);
    }
    function setActiveCity(city) {
        self.activeCity = city;
        //self.activeCity.MetroStationsList = GeoLocationService.getCityMetroStation(self.activeCity);
        self.loading.MetroStations = true;
        GeoLocationService.getMetroStations(self.activeCity).then(function(stations){
            self.loading.MetroStations = false;
            stations.sort(function (a, b) {
                if (a.line > b.line) return 1;
                if (a.line < b.line) return -1;
                return 0;
            });
            self.activeCity.MetroStationsList = stations;
        });
        //self.activeCity.MetroStationsList.sort();
    }

    function onReCaptchaCreated(widgetId) {
        self.recaptchaWidgetId = widgetId;
    }
    function checkForm() {
        if (self.orderData.Name.trim() == '' || self.orderData.Surname.trim() == '' || self.orderData.Email.trim() == '' || self.orderData.Phone.trim() == ''){
            return 1;
        }
        if (!validateEmail(self.orderData.Email.trim())){
            return 2;
        }
        if (!/\d{10}/.test(self.orderData.Phone.trim())){
            return 3;
        }
        var deliveryData = self.orderData.OrderType == 'ToAllDays' ? {ToAllDays: self.orderData.ToAllDays} : self.orderData.UniqueAddresses, t;
        for (var x in deliveryData){
            t = deliveryData[x];
            if (t.Street.trim() == ''){
                return 4;
            }
        }
        return 0;
    }
    function getFormErrorMessage(code) {
        switch (code){
            case 1:
                return 'Не заполнены обязательные поля.';
            case 2:
                return 'Не правильный формат email.';
            case 3:
                return 'Не правильный формат телефона.';
            case 4:
                return 'Не заполнена информация о адресе доставки.';
            default:
                return 'Ошибок нет.';
        }
    }
    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }
};

siteApp.controller('cart.OrderForm', ['$scope', 'CartService', 'UsersService', 'BackendService', '$idialog', 'KladrService', 'GeoLocationService',  CartOrderFormController]);
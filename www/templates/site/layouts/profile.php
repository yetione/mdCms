<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>
</head>
<body ng-app="SektaFoodApp" ng-controller="ProfilePageController as profileCtrl">
<?php require 'blocks/header.php';?>
<form name="profileCtrl.UserDataForm">
    <header>
        <div class="logophone wrap">
            <div class="logo"><a href="<?php echo BASE_URL;?>"><img alt="" src="<?php echo BASE_URL;?>templates/site/images/sektafood-logo.png"  width="125"></a></div>
            <div class="phones">
                <p class="phone"><a href="tel:+79119260798">+7 911 926-07-98</a></p>
                <p class="slogan">ДОСТАВКА ЗДОРОВОЙ ЕДЫ</p>
            </div>
        </div>
        <section class="personalinfo">
            <h3>Личный кабинет <span>(ID {{profileCtrl.currentUser.Id}})</span></h3>
            <p><input placeholder="Имя" ng-model="profileCtrl.currentUser.Name" name="UserName"></p>
            <p><input placeholder="Отчество" ng-model="profileCtrl.currentUser.Patronymic" name="UserPatronymic"></p>
            <p><input placeholder="Фамилия" ng-model="profileCtrl.currentUser.Surname" name="UserSurname"></p>
            <p><input placeholder="Телефон" ng-model="profileCtrl.currentUser.Phone" name="UserPhone" ui-mask="{{profileCtrl.phoneMask}}" ui-mask-placeholder=""></p>
            <p><input placeholder="Email" ng-model="profileCtrl.currentUser.Email" name="UserEmail"></p>
            <p><button ng-click="profileCtrl.saveUserData()">Сохранить личную информацию</button></p>
        </section>
    </header>

    <section class="personalinfo-addresses">
        <div class="text">
            <h3>Ваши адреса</h3>
            <p>Вы можете хранить несколько адресов, и выбирать нужные из них при формировании заказа на один или несколько дней!</p>
        </div>
        <div class="address" ng-repeat="address in profileCtrl.currentUser.Addresses" ng-show="profileCtrl.currentUser.Addresses.length > 0">
            <h4 ng-hide="address.IsEdit">{{address.Name}} <span class="pseudolink" ng-click="profileCtrl.editAddress(address)">Редактировать</span></h4>
            <p ng-show="address.IsEdit"><input placeholder="Название" ng-model="address.Name"><span class="pseudolink" ng-click="profileCtrl.cancelEdit(address)">Отмена</span></p>
            <p ng-show="address.IsEdit">
                <select ng-model="address.CityId"
                ng-options="c2.Id as c2.Name for c2 in profileCtrl.citiesList">
                </select>
            </p>
            <p ng-hide="address.IsEdit">Город: {{address.CityId}}</p>
            <div style="position: relative;"ng-show="address.IsEdit">
                <input placeholder="Станция метро" ng-model="address.MetroStation" ng-change="profileCtrl.metroStationChange(address)">
                <div class="founded-streets" ng-show="address.showMetroStationsList">
                    <ul>
                        <li ng-repeat="s in profileCtrl.metroStations[profileCtrl.getCityMachine(address.CityId)] | filter:address.MetroStation track by $index" ng-click="profileCtrl.selectMetroStation(address, s)">{{s}}</li>
                    </ul>
                </div>
            </div>
            <p ng-hide="address.IsEdit">Станция метро: {{address.MetroStation}}</p>
            <div style="position: relative;" ng-show="address.IsEdit">
                <input placeholder="Улица" ng-model="address.Street" ng-change="profileCtrl.streetChange(address)">
                <div class="founded-streets" ng-show="address.foundedStreets">
                    <ul>
                        <li ng-repeat="street in address.foundedStreets" ng-click="profileCtrl.selectStreet(address, street)">{{street.typeShort}}. {{street.name}}</li>
                    </ul>
                </div>
            </div>
            <p ng-hide="address.IsEdit">Улица: {{address.Street}}</p>
            <p ng-show="address.IsEdit"><input placeholder="Дом" ng-model="address.Building"></p>
            <p ng-hide="address.IsEdit">Дом: {{address.Building}}</p>
            <p ng-show="address.IsEdit"><input placeholder="Квартира/офис" ng-model="address.Room"></p>
            <p ng-hide="address.IsEdit">Квартира/офис: {{address.Building}}</p>
            <p><button ng-click="profileCtrl.saveAddress(address)" ng-show="address.IsEdit">Сохранить адрес</button><button ng-click="profileCtrl.deleteAddress(address)" ng-show="address.Id > 0">Удалить адрес</button></p>
        </div>
        <div class="address" ng-show="profileCtrl.currentUser.Addresses.length == 0">
            <h4>Адресов не добавлено</h4>
        </div>
        <section class="button"><button ng-click="profileCtrl.addAddress()"><i class="fa fa-plus" aria-hidden="true"></i> Добавить адрес</button></section>
    </section>
    <section class="orderhistory">
        <div class="text">
            <h3>История заказов</h3>
        </div>
        <div class="orderslist">
            <p ng-repeat="order in profileCtrl.ordersList track by order.Id"><a ng-href="orders/{{order.Id}}"><span>{{profileCtrl.getShortDate(order.DateCreated) | date: 'm'}}</span><span>{{order.Price}} ₽</span></a></p>
        </div>
        <section class="button"><button>Показать еще</button></section>
    </section>
</form>
<?php require 'blocks/footer.php'?>
</body>
</html>
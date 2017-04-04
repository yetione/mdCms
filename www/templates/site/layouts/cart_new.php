<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>
    <script src="https://www.google.com/recaptcha/api.js?onload=vcRecaptchaApiLoaded&render=explicit" async defer></script>

    <!--    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false"></script>-->

</head>
<body ng-app="SektaFoodApp" ng-controller="cart.OrderData as mainCtrl">
<?php require 'blocks/header.php';?>
<header>
    <div class="logophone wrap">
        <div class="logo"><a href="<?php echo BASE_URL;?>"><img alt="" src="<?php echo BASE_URL;?>templates/site/images/sektafood-logo.png"  width="125"></a></div>
        <div class="phones">
            <p class="phone"><a href="tel:+79119260798">+7 911 926-07-98</a></p>
            <p class="slogan">ДОСТАВКА ЗДОРОВОЙ ЕДЫ</p>
        </div>
    </div>
    <section class="additions">
        <section class="orderdetails" ng-show="mainCtrl.cart.ProductsCount > 0">
            <div>
                <h3>Оформление заказа</h3>
                <p>Заказ на {{mainCtrl.cart.DaysSelected}} дня</p>
                <p> {{mainCtrl.cart.ProductsCount}} наименований</p>
                <p>На сумму {{mainCtrl.cart.TotalPrice}}</p>
                <p ng-show="mainCtrl.cart.PromoCode.Data.Type == 1">Цена со скидкой: {{mainCtrl.cart.CartData.NewPrice}} ₽</p>
            </div>
            <div><button>Перейти к оформлению</button></div>
        </section>
        <section class="orderdetails" ng-show="mainCtrl.cart.ProductsCount == 0">
            <div class="text">
                <h3>Корзина пуста</h3>
            </div>
        </section>

        <section class="promocode" ng-show="mainCtrl.cart.ProductsCount > 0 && mainCtrl.cart.PromoCode.Id == 0 && mainCtrl.currentUser.Id > 0">
            <div>
                <h3>ПРОМО-КОД</h3>
                <p>Введите промо-код для получения дополнительной скидки!</p>
                <p>Обратите внимание, что не все акции суммируются. Подробности уточняйте у оператора.</p>
            </div>
            <div ng-hide="mainCtrl.itemsInLoading.PromoCode"><input placeholder="Ваш код" ng-model="mainCtrl.promoCodeInput"><button ng-click="mainCtrl.activatePromoCode()">Применить код</button></div>
        </section>
        <section class="promocode" ng-show="mainCtrl.cart.ProductsCount > 0 && mainCtrl.cart.PromoCode.Id > 0 && mainCtrl.currentUser.Id > 0">
            <div>
                <h3>ПРОМО-КОД: {{mainCtrl.cart.PromoCode.Code}}</h3>
                <p>{{mainCtrl.cart.PromoCode.Description}}</p>
                <div><button ng-click="mainCtrl.deletePromoCode()" ng-hide="mainCtrl.itemsInLoading.PromoCode">Удалить код</button></div>
            </div>
        </section>
    </section>
</header>

<section class="foodselection foodselection-order" ng-repeat="day in mainCtrl.cart.Data" ng-show="mainCtrl.cart.ProductsCount > 0">
    <div class="header">
        <div class="text">
            <h3>Заказ на {{mainCtrl.getShortDate(day.date)}} ({{mainCtrl.getDayName(day.date)}})</h3>
            <p>Товаров: {{day.productsCount}}. На сумму: {{day.price}} ₽</p>
        </div>
    </div>
    <div class="cards">

        <div class="card anim" ng-repeat="product in day.products">
            <div class="price">{{mainCtrl.getCurrentProductPrice(mainCtrl.getProduct(product.id))}} ₽</div>
            <div class="remove" ng-click="mainCtrl.removeProduct(day.date, mainCtrl.getProduct(product.id))"><i class="fa fa-times" aria-hidden="true"></i></div>
            <div class="about">
                <img alt="" ng-src="{{mainCtrl.getProduct(product.id).Image}}" style="width:220px">
            </div>
            <h4>{{mainCtrl.getProduct(product.id).Name}}</h4>
            <div class="itemresult" ng-hide="mainCtrl.getProduct(product.id).isLoaded">
                <button ng-disabled="product.amount <= 0" ng-click="mainCtrl.minusProduct(day.date, mainCtrl.getProduct(product.id))">&minus;</button>
                <input ng-model="product.amount" ng-blur="mainCtrl.inputBlur(day.date, product)" >
                <button ng-click="mainCtrl.addProduct(day.date, mainCtrl.getProduct(product.id))">+</button>
                <div class="total">{{product.amount*product.price}} ₽</div>

            </div>
        </div>
    </div>
</section>
<form name="OrderForm" ng-controller="cart.OrderForm as orderCtrl" ng-submit="orderCtrl.doOrder()" ng-show="orderCtrl.cart.ProductsCount > 0">
    <section class="customerdetails" >
        <h3>Информация о доставке</h3>
        <p>
            <input placeholder="Имя" ng-model="orderCtrl.orderData.Name" ng-required="true" name="ClientName">
        </p>
        <p>
            <input placeholder="Фамилия" ng-model="orderCtrl.orderData.Surname" ng-required="true" name="ClientSurname">
        </p>
        <p>
            <input placeholder="Телефон" ng-model="orderCtrl.orderData.Phone" ui-mask="{{orderCtrl.phoneMask}}" ui-mask-placeholder="" ng-required="true" name="ClientPhone">
        </p>
        <p>
            <input type="email" placeholder="Email" ng-model="orderCtrl.orderData.Email" ng-required="true" name="ClientEmail">
        </p>
        <div class="shipping-day-block">
            <div class="days-list">
                <div class="day">
                    <p class="day-name">ПН</p>
                    <p class="day-date">2.10.1016</p>
                </div>
                <div class="day">
                    <p class="day-name">ПН</p>
                    <p class="day-date">2.10.1016</p>
                </div>
                <div class="day">
                    <p class="day-name">ПН</p>
                    <p class="day-date">2.10.1016</p>
                </div>
                <div class="day">
                    <p class="day-name">ПН</p>
                    <p class="day-date">2.10.1016</p>
                </div>
                <div class="day">
                    <p class="day-name">ПН</p>
                    <p class="day-date">2.10.1016</p>
                </div>
                <div class="day">
                    <p class="day-name">ПН</p>
                    <p class="day-date">2.10.1016</p>
                </div>
                <div class="day">
                    <p class="day-name">ПН</p>
                    <p class="day-date">2.10.1016</p>
                </div>
                <div class="day">
                    <p class="day-name">ПН</p>
                    <p class="day-date">2.10.1016</p>
                </div>
            </div>
            <div class="to-all-days">
                <p>Единые данные на все дни</p>
            </div>
        </div>
        <p>
            <label><input type="radio" name="type" ng-model="orderCtrl.orderType" value="ToAllDays" ng-change="orderCtrl.typeSelected()"> Единые данные на все дни</label>
            <label><input type="radio" name="type" ng-model="orderCtrl.orderType" value="UniqueAddresses" ng-change="orderCtrl.typeSelected()"> Уникальные данные на каждый день</label>
        </p>
        <div style="position: relative">
            <ui-select ng-model="orderCtrl.activeDeliveryDay" theme="selectize" class="select-box" ng-show="orderCtrl.orderType == 'UniqueAddresses'"
                       on-select="orderCtrl.daySelected()">
                <ui-select-match placeholder="День заказа" class="select-placeholder">{{$select.selected.label}}</ui-select-match>
                <ui-select-choices repeat="day in orderCtrl.daysList | filter:$select.search track by day.value">
                    <span>{{day.label}}</span>
                </ui-select-choices>
            </ui-select>
            <!--<select name="Day" ng-model="orderCtrl.activeDeliveryDay" ng-show="orderCtrl.orderType == 'UniqueAddresses'"
                    ng-options="day as day.label for day in orderCtrl.daysList track by day.value"
                    ng-change="orderCtrl.daySelected()">
            </select>-->
        </div>
        <!--<p>
            <input placeholder="Время доставки" ng-model="orderCtrl.activeDay.DeliveryTime" ui-mask="{{orderCtrl.deliveryTimeMask}}" ui-mask-placeholder="" name="DeliveryTime" ng-required="true">
        </p>-->
        <div style="position: relative">
            <ui-select ng-model="orderCtrl.activeDay.UserAddress" theme="selectize" class="select-box" ng-show="orderCtrl.currentUser.Id > 0 && orderCtrl.currentUser.Addresses.length > 0"
                       on-select="orderCtrl.addressSelected()">
                <ui-select-match placeholder="Сохраненные адреса" class="select-placeholder">{{$select.selected.Name}}</ui-select-match>
                <ui-select-choices repeat="address in orderCtrl.currentUser.Addresses | filter:{Name:$select.search} track by address.Id">
                    <span>{{address.Name}}</span>
                </ui-select-choices>
            </ui-select>
            <!--<select name="Address" ng-model="orderCtrl.activeDay.UserAddress" ng-show="orderCtrl.currentUser.Id > 0 && orderCtrl.currentUser.Addresses.length > 0"
                    ng-options="address as address.Name for address in orderCtrl.currentUser.Addresses"
                    search-enabled="false" reset-search-input="false"
                    ng-change="orderCtrl.addressSelected()">
                <option value="" selected>Сохраненные адреса</option>
            </select>-->
        </div>
        <div style="position: relative">
            <ui-select ng-model="orderCtrl.activeDay.ActiveCity" theme="selectize" class="select-box">
                <ui-select-match placeholder="Город" class="select-placeholder">{{$select.selected.Name}}</ui-select-match>
                <ui-select-choices repeat="city in orderCtrl.cities | filter:$select.search track by city.Id">
                    <span>{{city.Name}}</span>
                </ui-select-choices>
            </ui-select>
            <!--<select name="Address" ng-model="orderCtrl.activeDay.ActiveCity"
                    ng-options="city as city.Name for city in orderCtrl.cities track by city.Id"
                    ng-change="orderCtrl.citySelected()">
            </select>-->
        </div>
        <div style="position: relative;">
            <ui-select ng-model="orderCtrl.activeDay.MetroStationObject" theme="selectize" class="select-box" ng-disabled="orderCtrl.loading.MetroStations"
                       on-select="orderCtrl.metroStationSelected()">
                <ui-select-match placeholder="Станция метро" class="select-placeholder">{{$select.selected.name}}</ui-select-match>
                <ui-select-choices group-by="'line'" repeat="s in orderCtrl.activeDay.ActiveCity.MetroStationsList | filter:{name:$select.search} track by $index">
                    <span>{{s.name}}</span>
                </ui-select-choices>
            </ui-select>
            <!--<input placeholder="Станция метро" ng-model="orderCtrl.activeDay.MetroStation" name="MetroStation" ng-required="false" ng-change="orderCtrl.metroStationChange()" autocomplete="off">
            <div class="founded-streets" ng-show="orderCtrl.showMetroStationsList">
                <ul>
                    <li ng-repeat="s in orderCtrl.activeDay.ActiveCity.MetroStationsList | filter:orderCtrl.activeDay.MetroStation track by $index" ng-click="orderCtrl.selectMetroStation(s)">{{s}}</li>
                </ul>
            </div>-->
        </div>
        <div style="position: relative;">
            <!--<ui-select ng-model="orderCtrl.activeDay.StreetObject" theme="selectize" class="select-box street-select"
                       on-select1="orderCtrl.streetSelected($item, $model)" ng-disabled="orderCtrl.activeDay.StreetObject !== null">
                <ui-select-match placeholder="Улица" class="select-placeholder">{{$select.selected.typeShort}}. {{$select.selected.name}}</ui-select-match>
                <ui-select-choices repeat="s in orderCtrl.foundedStreets track by s.id" refresh="orderCtrl.refreshStreets($select.search)" refresh-delay="500" minimum-input-length="3">
                    <p class="street-row">{{s.typeShort}}. <span ng-bind-html="s.name | highlight: $select.search"></span><span class="city" ng-show="s.city"> ({{s.city}})</span></p>
                </ui-select-choices>
            </ui-select>
            <a ng-click="orderCtrl.changeStreet()" ng-show="orderCtrl.activeDay.StreetObject !== null && !orderCtrl.loading.Street" style="cursor: pointer;position: absolute;top: 7px;left: 450px;">Изменить</a>
            <div style="position: absolute;top:0;left:450px;" class="cssload-loader" ng-show="orderCtrl.loading.Streets">
                <div class="cssload-inner cssload-one"></div>
                <div class="cssload-inner cssload-two"></div>
                <div class="cssload-inner cssload-three"></div>
            </div>-->
            <input placeholder="Улица" ng-model="orderCtrl.activeDay.Street" name="StreetInput" ng-change="orderCtrl.streetChange()" ng-required="true" autocomplete="off">
            <div class="founded-streets" ng-show="orderCtrl.foundedStreets">
                <ul>
                    <li ng-repeat="street in orderCtrl.foundedStreets" ng-click="orderCtrl.selectStreet(street)">{{street.typeShort}}. {{street.name}}</li>
                </ul>
            </div>
        </div>
        <p>
            <input placeholder="Дом" ng-model="orderCtrl.activeDay.Building" style="width: 200px;" name="DeliveryBuilding" ng-required="true" autocomplete="off">
            <input placeholder="Квартира/офис" ng-model="orderCtrl.activeDay.Room" style="width: 235px;" name="DeliveryRoom" autocomplete="off">
        </p>
        <p>
            <input placeholder="Кол-во приборов" ng-model="orderCtrl.activeDay.PersonsCount" name="PersonsCount" ng-required="true" autocomplete="off">
        </p>
        <p>
            <textarea ng-model="orderCtrl.activeDay.Comment" placeholder="Комментарии к заказу" name="ClientComment"></textarea>
        </p>
        <p>
            <label>
                <input type="checkbox" ng-model="orderCtrl.orderData.AgreeOffer" ng-required="true">
                Я согласен с <a ng-href="offer">офертой</a>
            </label>
        </p>
        <div vc-recaptcha key="'6LdwxSkTAAAAAGq-ioSON3OfhZKsR1eXM-9LMsvj'" ng-model="orderCtrl.orderData.ReCaptcha" class="re-captcha" on-create="orderCtrl.onReCaptchaCreated(widgetId)"></div>

    </section>
    <section class="send">
        <button type="submit" ng-disabled="orderCtrl.disableSubmit || orderCtrl.cart.IsBlocked">Сделать заказ</button>
        <span ng-show="orderCtrl.cart.IsBlocked && orderCtrl.cart.BlockingReason.code == 1">В заказе должно быть 3 блюда минимум!</span>
    </section>
</form>

<?php require 'blocks/footer.php';?>

<script type="text/ng-template" id="order-created">
    <div class="modal cityconfirm" ng-controller="mdDialogs.Message as dialogCtrl">
        <p>Заказ сделан</p>
        <div class="buttons">
            <a class="buttonlink" ng-click="dialogCtrl.options.hide(this)">ОК</a>
        </div>
    </div>
</script>
</body>
</html>
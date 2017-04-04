<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>
    <script>
        <?php echo $this->getBlock('OrderEntity');?>
    </script>

</head>
<body ng-app="SektaFoodApp" ng-controller="OrderDetailPageController as orderDetail">
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
        <section class="orderdetails">
            <div>
                <h3>Заказ №{{orderDetail.orderData.Id}}</h3>
                <p>Клиент: {{orderDetail.orderData.ClientName}}</p>
                <p>Заказ на {{orderDetail.orderData.Products.length}} дня</p>
                <p>На сумму {{orderDetail.orderData.Price}} ₽</p>
            </div>
        </section>
        <!--<section class="promocode">
            <div>
                <h3>ПРОМО-КОД</h3>
                <p>Введите промо-код для получения дополнительной скидки!</p>
                <p>Обратите внимание, что не все акции суммируются. Подробности уточняйте у оператора.</p>
            </div>
            <div><input placeholder="Ваш код"><button>Применить код</button></div>
        </section>-->
    </section>
</header>
<section class="foodselection foodselection-order" ng-repeat="day in orderDetail.orderData.Products track by day.Id">
    <div class="header">
        <div class="text">
            <h3>Заказ на {{orderDetail.getShortDate(day.DeliveryDate)}}</h3>
            <p>Товаров: {{day.Products.length}}. На сумму: {{day.Price}} ₽</p>
        </div>
        <div class="text">
            <h3>Доставка</h3>
            <p>Адрес: {{orderDetail.getCityName(orderDetail.orderData.CityId)}}, {{day.Street}}, {{day.Building}} {{day.Room}}. Время: {{day.DeliveryTime}}</p>
        </div>
        <!--        <div class="digit">1</div>-->
    </div>
    <div class="cards">
        <div class="card anim" ng-repeat="product in day.Products">
            <div class="price">{{product.Price}} ₽</div>

            <div class="about">
                <img alt="" ng-src="{{product.Product.Image}}" style="width:220px">
            </div>
            <h4>{{product.Product.Name}}</h4>
            <div class="itemresult">
                <input ng-model="product.Amount" ng-disabled="true" >
                <div class="total">{{product.Amount*product.Price}} ₽</div>
            </div>
        </div>
    </div>
</section>

<?php require 'blocks/footer.php';?>

</body>
</html>
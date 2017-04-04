<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>

</head>
<body ng-app="SektaFoodApp" ng-controller="IndexController as mainCtrl" ng-class="{orderactionexists:mainCtrl.cs.cart.ProductsCount > 0}">
<?php require 'blocks/header.php';?>
<header>
    <div class="logophone wrap">
        <div class="logo"><a href="<?php echo BASE_URL;?>"><img alt="" src="<?php echo BASE_URL;?>templates/site/images/sektafood-logo.png" width="125"></a></div>
        <div class="phones">
            <p class="phone"><a href="tel:+79119260798">+7 911 926-07-98</a></p>
            <p class="slogan">ДОСТАВКА ЗДОРОВОЙ ЕДЫ</p>
        </div>
    </div>
    <div class="slider">
        <div class="slide">
            <h2>Добро пожаловать!</h2>
            <p>#SEKTAFOOD - это доставка наборов полезных и вкусных блюд в г. Москва. Заказ можно сделать сразу на несколько дней!</p>
            <!--a class="buttonlink" href="">УЗНАТЬ БОЛЬШЕ</a-->
        </div>
    </div>
    <section class="daysselection" ng-controller="MenuController as menuCtrl">
        <h3>Выберите день недели</h3>
        <div class="days">
            <div class="day" ng-repeat="day in menuCtrl.days" ng-class="{active:menuCtrl.activeDay == day, orderexists:day.Price > 0}" ng-click="menuCtrl.setActiveDay($index)">
                <div class="orderexistsmark"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                <div class="date">{{day.Title}}</div>
                <div class="weekday anim" style="text-transform: uppercase;">{{day.DayName}}</div>
                <p ng-show="day.Price >= 0">{{day.Price}} ₽</p>
            </div>
        </div>
    </section>
</header>

<section class="foodselection foodselection-main">
    <div class="header">
        <div class="text">
            <h3>Выберите блюда</h3>
            <p>Выберите блюда на этот день (не менее 2 блюд)</p>
        </div>
        <div class="digit">1</div>
    </div>
    <div class="cards">
        <div class="card anim" ng-repeat="product in mainCtrl.currentMenuItem.data.Data[0].Products">
            <div class="price">{{mainCtrl.getCurrentProductPrice(product)}} ₽</div>
            <!--<div class="image">
                <img alt="" ng-src="{{product.Image}}" style="width:220px">
            </div>-->
            <div class="about">
                <img alt="" ng-src="{{product.Image}}" style="width:220px">
                <div class="ingridients">{{product.Description}}</div>
            </div>
            <h4>{{product.Name}}</h4>
            <div class="params">
                <p ng-show="product.Fats > 0"><span>Жиры</span><span>{{product.Fats}}</span></p>
                <p ng-show="product.Proteins > 0"><span>Белки</span><span>{{product.Proteins}}</span></p>
                <p ng-show="product.Carbs > 0"><span>Углеводы</span><span>{{product.Carbs}}</span></p>
                <p ng-show="product.Calorie > 0"><span>Ккал</span><span>{{product.Calorie}}</span></p>
            </div>
            <div class="button">
                <button ng-click="mainCtrl.addToCart(product)">Добавить к заказу</button>
            </div>
        </div>
    </div>
</section>
<section class="foodselection foodselection-main" >
    <div class="header">
        <div class="text">
            <h3>Выберите перекусы</h3>
            <p>Выберите перекусы на этот день</p>
        </div>
        <div class="digit">2</div>
    </div>
    <div class="cards">
        <div class="card anim" ng-repeat="product in mainCtrl.currentMenuItem.data.Data[1].Products">
            <div class="price">{{mainCtrl.getCurrentProductPrice(product)}} ₽</div>
            <div class="image">
                <img alt="" ng-src="{{product.Image}}" style="width:220px">
            </div>
            <h4>{{product.Name}}</h4>
            <div class="params">
                <p ng-show="product.Fats > 0"><span>Жиры</span><span>{{product.Fats}}</span></p>
                <p ng-show="product.Proteins > 0"><span>Белки</span><span>{{product.Proteins}}</span></p>
                <p ng-show="product.Carbs > 0"><span>Углеводы</span><span>{{product.Carbs}}</span></p>
                <p ng-show="product.Calorie > 0"><span>Ккал</span><span>{{product.Calorie}}</span></p>
                <p ng-show="product.Calorie > 0"><span>Ккал</span><span>{{product.Calorie}}</span></p>
            </div>
            <div class="button">
                <button ng-click="mainCtrl.addToCart(product)">Добавить к заказу</button>
            </div>
        </div>
    </div>
</section>
<section class="orderaction" ng-animate="'animate'" ng-show="mainCtrl.cs.cart.ProductsCount > 0">
    <div class="wrap" ng-hide="mainCtrl.cs.cart.updating">
        <!--        <button class="orange">Оформить заказ</button>-->
        <div ng-show="mainCtrl.cs.cart.IsBlocked">
            <a class="buttonlink grey ng-hide" ng-show="mainCtrl.cs.cart.BlockingReason.code == 1 || ainCtrl.cs.cart.BlockingReason.code == 2" style="text-transform: none;"><span ng-show="mainCtrl.cs.cart.BlockingReason.code == 1" style="text-transform: none">Заказ можно сделать, выбрав не менее двух основных блюд на каждый день заказа</span></a>
            <a class="buttonlink grey ng-hide" ng-show="mainCtrl.cs.cart.BlockingReason.code == 3">Корзина пуста</a>
        </div>
        <a ng-href="cart" class="buttonlink orange ng-hide" ng-show="!mainCtrl.cs.cart.IsBlocked">Оформить заказ</a>
    </div>
    <div class="wrap" ng-show="mainCtrl.cs.cart.updating">
        <div id="fountainG">
            <div id="fountainG_1" class="fountainG"></div>
            <div id="fountainG_2" class="fountainG"></div>
            <div id="fountainG_3" class="fountainG"></div>
            <div id="fountainG_4" class="fountainG"></div>
            <div id="fountainG_5" class="fountainG"></div>
            <div id="fountainG_6" class="fountainG"></div>
            <div id="fountainG_7" class="fountainG"></div>
            <div id="fountainG_8" class="fountainG"></div>
        </div>
    </div>
</section>

<?php require 'blocks/footer.php';?>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>
    <script src="https://www.google.com/recaptcha/api.js?onload=vcRecaptchaApiLoaded&render=explicit" async defer></script>
</head>
<body ng-app="SektaFoodApp" ng-controller="LoginPageController as mainCtrl">
<?php require 'blocks/header.php';?>
<header  class="enlarged">
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
</header>
<form name="RegistrationForm" ng-submit="mainCtrl.doLogin()" ng-show="!mainCtrl.currentUser.Id">
    <section class="customerdetails" style="text-align: center;">
        <h3>Вход</h3>
        <p>
            <input placeholder="Email" ng-model="mainCtrl.userData.Email" name="Email">
        </p>
        <p>
            <input type="password" placeholder="Пароль" ng-model="mainCtrl.userData.Password" name="Password">
        </p>
        <p>
            <label>
                <input type="checkbox" ng-model="mainCtrl.userData.RememberMe">
                Запомнить
            </label>
        </p>
    </section>
    <section class="send" style="text-align: center;">
        <button type="submit" ng-class="{disabled:mainCtrl.loginInProgress}" ng-disabled="mainCtrl.loginInProgress">
            <span class="label" ng-hide="mainCtrl.loginInProgress">Войти</span>
            <div class="loading-block" ng-show="mainCtrl.loginInProgress">
                <div class="cssload-jumping">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
            </div>
        </button>
        <a class="button" href="<?php echo $this->getBlock('vkLoginLink');?>">Зайти через VK</a>
    </section>
</form>
<section class="customerdetails" ng-hide="!mainCtrl.currentUser.Id">
    <h3>Привет, {{mainCtrl.currentUser.Name}}</h3>
</section>
<?php require 'blocks/footer.php'?>
</body>
</html>
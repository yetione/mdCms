<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>
    <script src="https://www.google.com/recaptcha/api.js?onload=vcRecaptchaApiLoaded&render=explicit" async defer></script>
</head>
<body ng-app="SektaFoodApp" ng-controller="VKRegistrationPageController as mainCtrl">
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
<form name="RegistrationForm" ng-submit="mainCtrl.doRegistration()" ng-show="!mainCtrl.currentUser.Id">
    <section class="customerdetails" style="text-align: center;">
        <h3>Регистрация</h3>
        <p>
            <input placeholder="Имя" ng-model="mainCtrl.userData.Name">
        </p>
        <p>
            <input placeholder="Фамилия" ng-model="mainCtrl.userData.Surname">
        </p>
        <p>
            <input placeholder="Отчество" ng-model="mainCtrl.userData.Patronymic">
        </p>
        <p>
            <input placeholder="Телефон" ng-model="mainCtrl.userData.Phone" ui-mask="{{mainCtrl.phoneMask}}" ui-mask-placeholder="">
        </p>
        <p>
            <input type="email" placeholder="Email" ng-model="mainCtrl.userData.Email">
        </p>

        <div vc-recaptcha key="'6LdwxSkTAAAAAGq-ioSON3OfhZKsR1eXM-9LMsvj'" ng-model="mainCtrl.userData.ReCaptcha" class="re-captcha" on-create="mainCtrl.onReCaptchaCreated(widgetId)"></div>


    </section>
    <section class="send" style="text-align: center;">
        <button type="submit" ng-class="{disabled:mainCtrl.registrationInProgress}" ng-disabled="mainCtrl.registrationInProgress">
            <span class="label" ng-hide="mainCtrl.registrationInProgress"> Зарегистрироваться</span>
            <div class="loading-block" ng-show="mainCtrl.registrationInProgress">
                <div class="cssload-jumping">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
            </div>
        </button>
    </section>
</form>
<section class="customerdetails" ng-hide="!mainCtrl.currentUser.Id || mainCtrl.successRegistration">
    <h3>Вы уже зарегистрированы!</h3>
</section>
<section class="customerdetails" ng-show="mainCtrl.successRegistration">
    <h3>Поздравляем с регистрацией!</h3>
</section>

<?php require 'blocks/footer.php'?>
</body>
</html>
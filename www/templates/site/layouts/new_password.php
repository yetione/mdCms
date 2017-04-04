<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>
    <script src="https://www.google.com/recaptcha/api.js?onload=vcRecaptchaApiLoaded&render=explicit" async defer></script>

    <!--    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false"></script>-->

</head>
<body ng-app="SektaFoodApp">
<?php require 'blocks/header.php';?>
<header>
    <div class="logophone wrap">
        <div class="logo"><a href="<?php echo BASE_URL;?>"><img alt="" src="<?php echo BASE_URL;?>templates/site/images/sektafood-logo.png"  width="125"></a></div>
        <div class="phones">
            <p class="phone"><a href="tel:+79119260798">+7 911 926-07-98</a></p>
            <p class="slogan">ДОСТАВКА ЗДОРОВОЙ ЕДЫ</p>
        </div>
    </div>
    <form name="NewPasswordForm" method="post" action="/login/new_password/set">
        <section class="customerdetails" >
            <?php
            if (isset($_GET['err'])){
                switch ($_GET['err']){
                    case 1:
                        $msg = 'Пароль и/или подверждение пароля введены некорректно.';
                        break;
                    case 2:
                        $msg = 'Не действительный токен.';
                        break;
                    case 3:
                        $msg = 'Капча не прошла проверку.';
                        break;
                    default:
                        $msg = 'Неизвестная ошибка';
                        break;
                }
                $msgTemplate = '<div class="errors"><p class="error">'.$msg.'</p></div>';
                echo $msgTemplate;
            }?>
            <p>
                <input type="password" placeholder="Пароль" ng-required="true" name="Password">
            </p>
            <p>
                <input type="password" placeholder="Подтверждение пароля" ng-required="true" name="PasswordConfirm">
            </p>
            <input type="hidden" value="<?php echo $this->getBlock('token');?>" name="Token">

            <div vc-recaptcha key="'6LdwxSkTAAAAAGq-ioSON3OfhZKsR1eXM-9LMsvj'" class="re-captcha"></div>

        </section>
        <section class="send">
            <button type="submit" ng-disabled="NewPasswordForm.$invalid">Отправить</button>
        </section>
    </form>
</header>
</body>
</html>

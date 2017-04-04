<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>
    <script src="https://www.google.com/recaptcha/api.js?onload=vcRecaptchaApiLoaded&render=explicit" async defer></script>
</head>
<body ng-app="SektaFoodApp">
    <?php require 'blocks/header.php';?>
    <section class="customerdetails" style="text-align: center;">
        <h3>Выход</h3>
    </section>
<?php require 'blocks/footer.php'?>
</body>
</html>
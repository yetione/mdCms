<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <base href="<?php echo BASE_URL?>">

    <link rel="stylesheet/less" type="text/css" href="<?php echo TEMPLATES_PATH;?>admin/less/login.less" />
    <script src="js/libs/less.min.js"></script>
</head>
<body>
<div class="wrapper">
<div class="login-form">
    <form action="auth/login" method="post">
        <div class="top">
            <div class="left">
                <img src="<?php echo TEMPLATES_PATH;?>admin/images/logo.png">
            </div>
            <div class="right">
                <span class="label">Make-d CMS</span>
                <span class="version">v.<?php echo CMS_VERSION;?></span>
            </div>
        </div>
        <div class="form-block">
            <p>Авторизация</p>
            <input type="text" name="email" placeholder="Логин">
            <input type="password" name="password" placeholder="Пароль">
        </div>
        <div class="footer">
            <input type="submit" value="Войти">
            <span class="message"><?php
                $msgs = [1=>'Пользователь заблокирован!',2=>'Не правильный логин или пароль!',5=>'Логин или пароль не могут быть пустыми!',3=>'Неизвестная ошибка!'];
                if (isset($_GET['err']) && isset($msgs[$_GET['err']])){echo $msgs[$_GET['err']];}?></span>
        </div>
    </form>
</div>
</div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <title>QSpace Установка</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="<?php echo BASE;?>install/templates/style.css" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>
        function formSubmit(form){
            if ($('input[name=user_password]').val() !== $('input[name=user_password_c]').val()){
                alert('Пароли не совпадают');
                return false;
            }
            /*
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.onload = function(e) {
                if (this.status == 200) {
                    console.log(this.response);
                    var result = JSON.parse(this.response);
                    if (result.success === true){
                        $('#install_form').replaceWith('<h2 id="success">Установка завершена</h2>')
                        console.log(result.data);
                    }else{
                        alert(result.error);
                    }
                }
            };
            xhr.send(formData);
            return false;
            */
        }
    </script>
</head>
<body>
<header>
    <div id="head-top">
        <div id="head-top-logo"></div>
    </div>
    <div id="head-status">
    </div>
</header>
<div id="wrapper">
    <h1 id="header">Установка QSpace</h1>
    <?php if (file_exists('../config/config.php')){?>
        <h2 id="success">QSpace уже установлен</h2>
    <?php }else{ ?>
        <h3 id="helper">Заполните форму и нажмите кнопку "Установить"</h3>
        <form id="install_form" action="<?php echo BASE;?>install/install.php" method="post" onsubmit="return formSubmit(this);">
            <label>
                Название сайта<br>
                <input type="text" name="site_name">
            </label><br>
            <br><br>
            <label>
                Логин суперпользователя<br>
                <input type="text" name="user_login">
            </label><br>
            <label>
                Пароль<br>
                <input type="password" name="user_password" >
            </label><br>
            <label>
                Подтверждение пароля<br>
                <input type="password" name="user_password_c">
            </label><br>
            <br><br>

            <h2>База данных</h2>
            <label>
                Адрес сервера базы данных<br>
                <input type="text" name="db_host">
            </label><br>
            <label>
                Имя пользователя базы данных<br>
                <input type="text" name="db_user">
            </label><br>
            <label>
                Пароль пользователя<br>
                <input type="text" name="db_password">
            </label><br>
            <label>
                Имя базы данных<br>
                <input type="text" name="db_database">
            </label><br>
            <br><br>

            <h2>Почтовый сервер (SMTP)</h2>
            <label>
                Адрес сервера<br>
                <input type="text" name="smtp_host">
            </label><br>
            <label>
                Порт сервера<br>
                <input type="text" name="smtp_port">
            </label><br>
            <label>
                Тип шифрования<br>
                <input type="text" name="smtp_secure">
            </label><br>
            <br><br>
            <input type="hidden" name="base" value="<?php echo BASE;?>">
            <input type="submit" id="install" value="Установить">
        </form>
    <?php }?>
</div>
</body>
</html>
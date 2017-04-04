<!DOCTYPE html>
<html>
<head>
    <?php require 'blocks/head.php';?>
    <?php require 'blocks/angular_include.php';?>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC8sAuZK6C3DHJmuQuEyI1nzALdMTFNWow&libraries=places&sensor=false" async defer></script>

    <!--    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false"></script>-->

</head>
<body ng-app="SektaFoodApp">
<?php require 'blocks/header.php';?>
<header class="enlarged">
    <div class="logophone wrap">
        <div class="logo"><a href="<?php echo BASE_URL;?>"><img alt="" src="<?php echo BASE_URL;?>templates/site/images/sektafood-logo.png" width="125"></a></div>
        <div class="phones">
            <p class="phone"><a href="tel:+79119260798">+7 911 926-07-98</a></p>
            <p class="slogan">ДОСТАВКА ЗДОРОВОЙ ЕДЫ</p>
        </div>
    </div>
</header>
<section class="blog-main">
    <div class="header">
        <h2>Доставка и оплата</h2>
    </div>
    <div class="text">
        <p>1. Доставка осуществляется в&nbsp;пределах территориальных зон доставки, определенных Исполнителем. </p>
        <p>2. Наборы готовятся из&nbsp;свежих натуральных продуктов каждое утро, поэтому при заказе на&nbsp;всю неделю доставка будет осуществляться также каждый день (набор по&nbsp;меню понедельника доставляют в&nbsp;понедельник, набор вторника во&nbsp;вторник и&nbsp;т.д.)</p>
        <p>3. Доставка осуществляется с&nbsp;10&nbsp;до&nbsp;16&nbsp;часов. Конкретный срок доставки определяется с&nbsp;учетом удаленности Клиента, дорожной ситуации, погодных условий и&nbsp;других, не&nbsp;зависящих от&nbsp;Исполнителя обстоятельств, и&nbsp;сообщается оператором при приеме заказа. Превышение установленного срока доставки возможно ввиду обстоятельств непреодолимой силы.</p>
        <p>4. Если какие-либо непредвиденные обстоятельства мешают Клиенту получить Заказ, необходимо как можно раньше связаться с&nbsp;операторами Call-центра по&nbsp;телефону и&nbsp;договориться о&nbsp;переносе времени или места доставки.</p>
        <p>В&nbsp;случае отсутствия Клиента/Получателя в&nbsp;назначенное время по&nbsp;адресу доставки Курьер ожидает Клиента/Получателя в&nbsp;течение 5&nbsp;минут. </p>
        <p>5. Если заказ не&nbsp;выполнен Исполнителем в&nbsp;течение указанного при оформлении заказа срока, Клиент должен сообщить об&nbsp;этом или по&nbsp;телефону оператору или по&nbsp;электронной почте: info@sektafood.ru не&nbsp;позднее 24&nbsp;часов после истечения срока доставки заказа.</p>
        <p>Оплата.</p>
        <p>Оплата производится наличными, курьеру, при получении заказа.</p>
    </div>
</section>

<?php require 'blocks/footer.php';?>

</body>
</html>
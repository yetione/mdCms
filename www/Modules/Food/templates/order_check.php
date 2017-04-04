<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
    <base href="/beta/">
    <style>
        @import url(https://fonts.googleapis.com/css?family=Open+Sans:700,400&subset=cyrillic,cyrillic-ext,latin);
        body{
            box-sizing: border-box;
            /*width:210mm;*/
            font-family: 'Open Sans', sans-serif;
            font-size:16px;
        }
        p{margin:0;padding: 0;}
        #top{
            margin-bottom: 40px;
        }
        #order_id{
            text-align: right;
            font-size:1.2em;
            color:#c0c0c0;
        }
        hr{
            border: none;
            background: #c0c0c0;
            height: 1px;
        }
        #top > div{
            display: inline-block;
        }
        #logo{width: 30mm;vertical-align: middle;}
        #logo img{width: 100%;}
        #contacts{width:100mm; vertical-align: top}
        #delivery_data{width:70mm; vertical-align: top;}

        #contacts h2{
            margin:5px 0;
            font-size:1.3em;
        }
        #contacts p{
            margin: 5px 0;
            font-size:0.8em;
        }
        #delivery_data p{
            margin:5px 0;
            font-size: 0.8em;
        }

        #delivery_address{
            width:100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        #delivery_address td,#delivery_address th{border:1px solid #c0c0c0;font-weight: bold;padding:10px;}
        #delivery_address th{font-size: 0.8em;text-align: left;padding:5px 10px;}

        #data_tale{
            margin-top:20px;
            border-collapse: collapse;
            width: 100%;
            text-align: left;
        }
        #data_tale td, #data_tale th{padding: 10px;border:1px solid #c0c0c0}
        #data_tale .itog{text-align: right; font-weight: bold;}
        #data_tale .tovar{width:40%;}
    </style>
</head>
<body>
<div id="top">
    <p id="order_id"><?php echo $order->getId();?></p>
    <hr>
    <div id="logo">
        <img src="<?php echo QS_path(['templates', 'site', 'images', 'logo_black.png'], false);?>">
    </div>
    <div id="contacts">
        <h2>Sektafood</h2>
        <p>Телефон: +7 981 877-94-78 (Мск)</p>
    </div>
    <div id="delivery_data">
<!--        <p><b>Дата добавления: </b>--><?php //echo $delivery/Date->format('d.m.Y');?><!--</p>-->
        <p><b>Способ доставки: </b><?php echo $orderDay->getDeliveryType();?></p>
        <p><b>День доставки: </b><?php echo $wdStr;?>, <?php echo $deliveryDate->format('d.m.Y');?> </p>
    </div>
</div>
<table id="delivery_address">
    <tr><th>Адрес доставки</th></tr>
    <tr><td>
            <p>Клиент: <?php echo $order->getClientName();?></p>
            <?php echo is_null($order->getPhone()) ? '' : '<p>Телефон: '.$order->getPhone().'</p>'?>
            <?php echo is_null($order->getEmail()) ? '' : '<p>Email: '.$order->getEmail().'</p>'?>
            <p><?php echo $order->getCity()->getName();?>, <?php echo $orderDay->getMetroStation();?>, <?php echo $orderDay->getStreet();?> <?php echo $orderDay->getBuilding() == '' ? '' : 'д. '.$orderDay->getBuilding().', ';?>
                <?php echo $orderDay->getRoom() == '' ? '' : 'д. '.$orderDay->getRoom();?></p>
        </td></tr>
</table>
<p>Срок годности при условии хранения в температурном режиме +2 - +6 составляет 12 часов</p>
<table id="data_tale">
    <thead>
    <tr>
        <th class="tovar">Товар</th>
        <th>Модель</th>
        <th>Количество</th>
        <th>Цена за единицу</th>
        <th>Итого</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($orderDay->getProducts() as $odProduct){?>
        <tr>
            <td><?php echo $odProduct->getProduct()->getName();?></td>
            <td></td>
            <td><?php echo $odProduct->getAmount();?></td>
            <td><?php echo $odProduct->getPrice();?>.</td>
            <td><?php echo ((float) $odProduct->getPrice() * (int) $odProduct->getAmount());?>р</td>
        </tr>
    <?php }?>
    <tr>
        <td colspan="4" class="itog">Доставка</td>
        <td>150р</td>
    </tr>
    <tr>
        <td colspan="4" class="itog">Общая сумма</td>
        <td><?php echo $orderDay->getPrice();?></td>
    </tr>
    </tbody>
</table>
</body>
</html>
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
            width:100%;
            box-sizing: border-box;
            padding: 0 10px;
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
        #contacts{width:70mm; vertical-align: top}
        #delivery_data{width:100mm; vertical-align: top;}

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
        #delivery_address td,#delivery_address th{border:1px solid #c0c0c0;font-weight: bold;padding: 10px;}
        #delivery_address th{font-size: 0.8em;text-align: left;padding: 5px;}

        #data_tale{
            margin-top:20px;
            border-collapse: collapse;
            width: 100%;
            text-align: left;
            font-size:12px;
        }
        #data_tale td,
        #data_tale th{padding: 10px;border:1px solid #c0c0c0}
        #data_tale td{padding: 5px;}
        #data_tale .itog{text-align: right; font-weight: bold;}
        #data_tale .tovar{width:40%;}
        #data_tale .order_id{width: 5%;text-align: center;}

        #data_tale .order_data{
            border-collapse: collapse;
            width: 100%;
        }
        #data_tale .order_data th{width:30%;text-align: left;}
        #data_tale .order_data,
        #data_tale .order_data th,
        #data_tale .order_data td{border:none}

        #data_tale .order_data tr.bordered{
            border-bottom: 1px solid #c0c0c0;
        }
    </style>
</head>
<body>
<div id="top">
    <hr>
    <div id="logo">
        <img src="<?php echo QS_path(['templates', 'site', 'images', 'logo_black.png'], false);?>">
<!--        <img src="/beta/templates/site/images/logo_black.png">-->
    </div>
    <div id="contacts">
        <h2>Sektafood</h2>
        <p>Телефон: +7 981 877-94-78(Мск)</p>
    </div>
    <div id="delivery_data">
        <p><b>Дата: </b><?php echo $date;?></p>
        <p><b>Курьер: </b><?php echo $courier->getName();?></p>
        <p><b>Город: </b><?php echo $city->getName();?></p>
    </div>

</div>
<table id="data_tale">
    <thead>
    <tr>
        <th class="order_id">№ заказа</th>
        <th></th>
        <!--<th>Ст. метро</th>
        <th>Адрес</th>
        <th>Телефон</th>
        <th>Сумма</th>
        <th>Время</th>
        <th>Комментарий клиента</th>
        <th>Комментарий менеджера</th>-->
    </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $order) {?>
        <tr>
            <td class="order_id"><?php echo $order->getOrder()->getId(); ?></td>
            <td>
                <p><b>Сумма: </b><?php echo $order->getTotalPrice();?> руб. (Включая доставку 150р.)</p>
                <hr>
                <p><b>Ст. метро: </b><?php echo $order->getMetroStation();?></p>
                <p><b>Адрес: </b><?php echo $order->getStreet().', '.(!empty(strval($order->getBuilding())) ? 'д.'.$order->getBuilding().', ' : '').(!empty(strval($order->getRoom())) ? $order->getRoom() : '')?></p>
                <?php if ($order->getDeliveryTime()){?>
                <p><b>Время доставки: </b><?php echo $order->getDeliveryTime();?></p>
                <?php }?>
                <hr>
                <?php if ($order->getClientComment()){?>
                <p><b>Комментарий клиента:</b><?php echo $order->getClientComment();?></p>
                <?php }?>
                <?php if ($order->getManagerComment()){?>
                <p><b>Комментарий менеджера</b><?php echo $order->getManagerComment();?></p>
                <?php }?>
            </td>

            <!--<td><?php echo $order->getMetroStation();?></td>
            <td><?php echo $order->getStreet().', '.(!empty(strval($order->getBuilding())) ? 'д.'.$order->getBuilding().', ' : '').(!empty(strval($order->getRoom())) ? $order->getRoom() : '')?></td>
            <td><?php echo $order->getOrder()->getPhone();?></td>
            <td><?php echo $order->getPrice();?> руб.</td>
            <td><?php echo $order->getDeliveryTime();?></td>
            <td><?php echo $order->getClientComment();?></td>
            <td><?php echo $order->getManagerComment();?></td>-->
        </tr>
    <?php } ?>

    </tbody>
</table>
</body>
</html>
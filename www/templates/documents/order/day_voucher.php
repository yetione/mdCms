<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <link href="<?php echo QS_path(['templates', 'documents', 'default', 'default.css'], false);?>" type="text/css" rel="stylesheet">
</head>
<body>
<div class="container">
    <p class="top-title">Заказ №<?php echo $order->getId();?></p>
</div>
<div class="container header">
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="logo">
                <img src="<?php echo QS_path(['templates', 'site', 'images', 'sektafood-logo.png'], false);?>">
            </td>
            <td class="contacts">
                <h1>#SEKTAFOOD - это доставка наборов полезных и вкусных блюд по Москве</h1>
            </td>
            <td class="data">
                <p><b>Способ получения:</b> <?php echo $orderDay->getDeliveryType();?></p>
                <p><b>Дата:</b> <?php echo $wdStr;?>, <?php echo $deliveryDate->format('d.m.Y');?> </p>
                <?php if ($order->getPromoCodeName()){?>
                <p><b>Промокод:</b> <?php echo $order->getPromoCodeName();?> </p>
                <?php }?>
            </td>
        </tr>
    </table>
</div>
<div class="container content">
    <table id="delivery_address">
        <tr><th>Адрес доставки</th></tr>
        <tr><td>
                <p>Клиент: <?php echo $order->getClientName();?></p>
                <?php echo empty($order->getPhone()) ? '' : '<p>Телефон: +7'.$order->getPhone().'</p>'?>
                <?php echo empty($order->getEmail()) ? '' : '<p>Email: '.$order->getEmail().'</p>'?>
                <p><?php echo $order->getCity()->getName();?>, <?php echo $orderDay->getMetroStation();?>, <?php echo $orderDay->getStreet();?> <?php echo $orderDay->getBuilding() == '' ? '' : 'д. '.$orderDay->getBuilding().', ';?>
                    <?php echo $orderDay->getRoom() == '' ? '' : 'д. '.$orderDay->getRoom();?></p>
                <?php echo empty($order->getDelieryTime()) ? '' : '<p>Время доставки: '.$order->getDelieryTime().'</p>'?>
            </td></tr>
    </table>
</div>
<div class="container content">
    <p>Срок годности при условии хранения в температурном режиме +2 - +6 составляет 12 часов</p>
    <table id="data_tale">
        <thead>
        <tr>
            <th class="tovar">Товар</th>
            <th>Количество</th>
            <th>Цена за единицу</th>
            <th>Итого</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orderDay->getProducts() as $odProduct){?>
            <tr>
                <td><?php echo $odProduct->getProduct()->getName();?></td>
                <td><?php echo $odProduct->getAmount();?></td>
                <td><?php echo (float) $odProduct->getPrice();?> руб.</td>
                <td><?php echo ((float) $odProduct->getPrice() * (int) $odProduct->getAmount());?> руб.</td>
            </tr>
        <?php }?>
        <tr>
            <td colspan="3" class="itog">Доставка</td>
            <td><?php echo (float) $orderDay->getDeliveryPrice();?> руб.</td>
        </tr>
        <?php if ($orderDay->getDiscountSum() != 0){?>
            <tr>
                <td colspan="3" class="itog">Скидка</td>
                <td><?php echo $orderDay->getDiscountSum();?> руб.</td>
            </tr>
        <?php }?>
        <tr>
            <td colspan="3" class="itog">Общая сумма</td>
            <td><?php echo $orderDay->getTotalPrice();?> руб.</td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
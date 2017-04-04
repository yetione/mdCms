<?php require BASE_PATH.'/templates/emails/default/header.php';?>
<table align="center" width="685" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table width="685" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">ИНФОРМАЦИЯ О ЗАКАЗЕ №:<?php echo $order->getId();?></span>
                    </td>
                </tr>
                <tr><?php $d = new \DateTime($order->getDateCreated());?>
                    <td style="padding-top:15px;padding-bottom:15px;">
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Заказчик: <?php echo $order->getClientName();?></span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Телефон: <a style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;" href="tel:+7<?php echo $order->getPhone();?>">+7<?php echo $order->getPhone();?></a></span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">E-mail: <a style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;" href="mailto:<?php echo  $order->getEmail();?>"><?php echo  $order->getEmail();?></a></span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Дата заказа: <?php echo $d->format('d.m.Y, H:i:s')?></span>
                        <?php if (!empty($order->getPromoCodeName())){?>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Промокод: <?php echo $order->getPromoCode()['Code'];?></span>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                $orderDays = $order->getProducts();

                $equals = true;
                $last = $orderDays[0];
                for ($i=1;$i<count($orderDays);++$i){
                    $cur = $orderDays[$i];
                    if ($cur->getDeliveryType() != $last->getDeliveryType() ||
                        $cur->getStreet() != $last->getStreet() ||
                        $cur->getBuilding() != $last->getBuilding() ||
                        $cur->getRoom() != $last->getRoom() ||
                        $cur->getMetroStation() != $last->getMetroStation()
                    ){
                        $equals = false;
                        break;
                    }
                    $last = $cur;
                }
                ?>
                <?php if ($equals){?>
                <tr>
                    <td style="padding-top:15px;padding-bottom:15px;">
                        <?php if ($last->getDeliveryType() == 'Курьером'){?>
                            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Адрес доставки: <?php echo $last->getStreet();?>, <?php echo $last->getBuilding();?>, Квартира/офис: <?php echo $last->getRoom();?></span>
                            <?php if (!empty($last->getDeliveryTime())){?>
                                <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Время доставки: <?php echo $last->getDeliveryTime();?></span>
                            <?php } ?>
                        <?php } ?>
<!--                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Кол-во приборов: 1</span>-->
<!--                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Оплата: неоплачен</span>-->
<!--                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Способ оплаты: наличными курьеру</span>-->
                    </td>
                </tr>
                <?php } ?>
                <?php
                $totalPrice = 0;
                foreach ($orderDays as $od){
                    $d = new \DateTime($od->getDeliveryDate());
                    $totalPrice += (float) $od->getTotalPrice();
                    ?>
                <tr>
                    <td style="padding-top:15px;padding-bottom:15px;">
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#a2be04; line-height: 25px;font-weight: normal;display: block;">Список продуктов на <?php echo $d->format('d.m.Y')?>:</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table cellpadding="10" cellspacing="0" border="0" align="left">
                            <?php
                            $products = $od->getProducts();
                            foreach ($products as $p){ ?>
                            <tr>
                                <td width="250">
                                    <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;"><?php echo $p->getProduct()->getName();?></span>
                                </td>
                                <td width="100">
                                    <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;"><?php echo $p->getAmount();?> шт.</span>
                                </td>
                                <td>
                                    <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;"><?php echo (float)$p->getPrice();?> руб.</span>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top:15px;padding-bottom:15px;">
                        <?php if ($od->getDiscountSum(true) != 0){?>
                            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Стоимость без скидки: <?php echo (float) $od->getPrice();?> руб</span>
                            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Скидка: <?php echo (float) $od->getDiscountSum();?> руб</span>
                        <?php }?>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Стоимость: <?php echo (float)$od->getDiscountPrice() >= 0 ? (float)$od->getDiscountPrice() : 0;?> руб</span>
                        <?php if ($od->getDeliveryType() == 'Курьером'){?>
                            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Стоимость доставки: <?php echo $od->getDeliveryPrice();?> руб</span>
                            <?php echo $equals ? '' : '<span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Адрес доставки: '. $od->getStreet().', '.$od->getBuilding().', Квартира/офис: '.$od->getRoom().'</span>'.
                                '<span style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">Время доставки:'.$od->getDeliveryTime().'</span>';?>
                        <?php }?>

                    </td>
                </tr>
                <?php }?>
                <tr>
                    <td style="padding-top:20px;padding-bottom:15px;">
<!--                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 35px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">ИТОГО: <span style="color:#92ba00">--><?php //echo $order->getPrice();?><!--</span> руб</span>-->
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 35px; color:#ffffff; line-height: 25px;font-weight: normal;display: block;">ИТОГО: <span style="color:#92ba00"><?php echo $totalPrice;?></span> руб</span>
                    </td>
                </tr>

            </table>
        </td>

</table>
<?php require BASE_PATH.'/templates/emails/default/footer.php';?>

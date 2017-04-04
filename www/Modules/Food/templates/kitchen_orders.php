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
        #delivery_address td,#delivery_address th{border:1px solid #c0c0c0;font-weight: bold;padding: 10px;}
        #delivery_address th{font-size: 0.8em;text-align: left;padding: 5px;}

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
    <hr>
    <div id="logo">
        <img src="<?php echo QS_path(['templates', 'site', 'images', 'logo_black.png'], false);?>">
<!--        <img src="/beta/templates/site/images/logo_black.png">-->
    </div>
    <div id="contacts">
        <h2>Sektafood</h2>
        <p>Телефон: +7 981 877-94-78 (Мск)</p>
    </div>
    <div id="delivery_data">
        <p><b>Дата:</b> <?php echo $date->format('d.m.Y');?></p>
        <p><b>Город:</b> <?php echo $city->getName();?></p>
    </div>
</div>


<table id="data_tale">
    <thead>
    <tbody>
    <?php foreach($orders as $order){ if (count($order->getProducts()) == 0) continue;?>
        <tr>
            <td colspan="2"><b>Заказ <?php echo $order->getId();?></b></td>
        </tr>
        <?php foreach($order->getProducts() as $product){?>
            <tr>
                <td><?php echo $product->getProduct()->getName();?>(<?php echo $product->getProduct()->getType()->getName();?>)</td>
                <td><?php echo $product->getAmount();?>шт</td>
            </tr>
        <?php }?>
    <?php }?>
    </tbody>
</table>
</body>
</html>
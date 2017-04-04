<?php
$ipList = ['46.252.168.212', '192.168.1.101', '192.168.1.100'];

if (!in_array($_SERVER['REMOTE_ADDR'], $ipList)){
    die('Netu nichego');
}
require_once '../definitions.php';
$loadApp = true;
/*
$mail = new \PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.peterhost.ru';
$mail->SMTPAuth = true;
$mail->Username = 'knuzz_1';
$mail->Password = '6EniU8fYZJ';
$mail->Port = 25;
$mail->SMTPSecure = 'tls';

//$mail->Mailer = 'sendmail';

$mail->setFrom('test@sektafood.ru', 'SektaFood');
$mail->addAddress('jub45@yandex.ru');
$mail->Subject = 'Test';
$mail->Body = 'Test Body';

$mail->setLanguage('ru');
var_dump($mail->send(), $mail->ErrorInfo);
*/

if ($loadApp){
    $app = new \Applications\TestApplication('test', $autoloader);
    $app->init();
    $app->route();
    $app->render();
}else{
    exit();
}
$dc = new \Modules\GeoLocation\Map\DirectionsQuery('AIzaSyAOpn4tzHGnahgdOFLx997qN8TnTJMWueo');
$dc->setOrigin('Санкт-Петербург, Чкаловский пр., 38')->setDestination('Санкт-Петербург, метро Петроградская');
var_dump($dc->execute());
//var_dump(QS_validate(json_encode(['Type'=>1, 'Options'=>['Value'=>3,'Units'=>'percents']]),TYPE_JSON));
$discount = '';
$price = 900;
$a = preg_match('/^([-+]?[0-9]*\.?[0-9]+)([%]?)$/', strval($discount), $matches);
if ($a){
    $d = (float) $matches[1];
    if ($matches[2] == '%'){
        $fPrice = $price + ($price*($d/100.0));
    }else{
        $fPrice = $price + $d;
    }
    //var_dump($matches, (float) $matches[1], $fPrice);
}

/*$apiId = 'BBDA2D46-48AC-CAC2-02DC-9672D4EFF8AD';
$phone1 = $phone2 = '+79650350676';
$text1 = $text2 = 'Ваш заказ №12 принят к обработке';

$client = new \SmsRu\Api(new \SmsRu\Auth\ApiIdAuth($apiId));
$sms1 = new \SmsRu\Entity\Sms($phone1, $text1);
$sms1->translit = 1;
$sms2 = new \SmsRu\Entity\Sms($phone2, $text2);

$client->smsSend($sms1);
$client->smsSend($sms2);

//$client->smsSend(new \SmsRu\Entity\SmsPool([$sms1, $sms2]));
var_dump($client->smsStatus($sms1));*/

//var_dump(json_encode($result));

//var_dump($mail->send(), $mail->ErrorInfo);
/*$a1 = 44;
$vars=['a1'=>['logo'=>'aa'],'a2'=>3];
extract($vars, EXTR_OVERWRITE);
var_dump($a1['logo']);*/


/*$core = $app->getCore();
$mail = $core->getMailer()->getSMTPMailer(['Host'=>'smtp.peterhost.ru', 'Port'=>25, 'Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
$mail->setFrom('no-reply@sektafood.ru', 'SektaFood');
$mail->addAddress('jub45@yandex.ru');
$mail->Subject = 'Test';
$mail->Body = 'Test Body';

$mail->setLanguage('ru');
var_dump($mail->send(), $mail->ErrorInfo);*/
//var_dump($app->getCore()->getEntityManager()->getEntityQuery('User')->findByEmail('jub45@yandex.ru')->loadOne(false, true));
/*$font = ['OpenSans-Regular.ttf', 'OpenSans-Italic.ttf', 'OpenSans-BoldItalic.ttf'];
$font = array_map(function($item){
    return QS_path(['Dompdf', 'lib', 'fonts']).$item;
}, $font);
$command = 'php '.QS_path(['Dompdf', 'load_font.php'], false).' OpenSans '.implode(' ', $font);
var_dump($command);
$opt = array();
var_dump(exec($command, $opt));
var_dump($opt);*/

/*
$t = new \Core\Response\Template('admin', 'index');
print_r($t->includeModule('shop'));
*/



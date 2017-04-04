<?php

var_dump(mail('jub45@yandex.ru','Subject!!', 'MSG'));


$mail = new \Core\Mailer\PHPMailer(true);
//var_dump($mail);
$mail->setLanguage('ru');
$mail->setFrom('admin@make-design.ru');
$mail->addAddress('jub45@yandex.ru');     // Add a recipient
//$mail->addAttachment(QS_path(array('uploads', 'blud1.jpg'), false), 'Bludo_1.jpg');

$mail->isHTML(true);

$mail->Subject = 'Тема письма Тра-та-та';
$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

//var_dump($mail->createBody());

var_dump($mail->send());

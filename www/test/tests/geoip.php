<?php
$mysql = $app->getCore()->getDb();
$db = new \Core\GeoIp\GeoIpDatabase();

//$data = $db->getGeobaseData('192.168.1.1');
$data = $db->getGeobaseData($_SERVER['REMOTE_ADDR']);
$sD = serialize($data);
$a = unserialize($sD);
var_dump($a->getCity());
//var_dump(iconv('utf-8', 'windows-1251', (string) $data->ip->city));



<?php
$ipList = ['46.252.168.212', '192.168.1.101', '192.168.1.100'];

if (!in_array($_SERVER['REMOTE_ADDR'], $ipList)){
    die('Netu nichego');
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../PhpUml/UML.php';
$uml = new PHP_UML();
$uml->setInput('../Core/DataBase');               // this defines which files/folders to parse (here, the folder "tests")
$uml->parse('myApp');                  // this starts the parser, and gives the name "myApp" to the generated metamodel
$uml->export('xmi', 'myApp.xmi');      // this serializes the metamodel in XMI code, and saves it to a file "myApp.xmi"
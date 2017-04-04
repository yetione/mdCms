<?php
$base = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], 'install/index.php'));
define('BASE', $base);
require  dirname(__FILE__).DIRECTORY_SEPARATOR.'/templates/index.php';
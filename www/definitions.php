<?php
define('CMS_VERSION', '0.5');

define('BASE_URL', '/');
define('BASE_PATH', dirname(__FILE__));

define('TEMPLATES_DIR', 'templates');
define('TEMPLATES_PATH', BASE_URL.'templates/');


define('TYPE_INT',           0);
define('TYPE_INT8',          1);
define('TYPE_INT10',         2);
define('TYPE_INT16',         3);
define('TYPE_FLOAT',         4);
define('TYPE_BOOL',          5);
define('TYPE_EMAIL',         6);
define('TYPE_IP',            7);
define('TYPE_REGEXP',        8);
define('TYPE_URL',           9);
define('TYPE_RAW',           10);
define('TYPE_JSON',          11);
define('TYPE_STRING',        12);
define('TYPE_IP_V4',        13);
define('TYPE_IP_V6',        14);
define('TYPE_CLEAR',        16);

define('HOOK_MODE_PRE',         10);
define('HOOK_MODE_POST',        11);
define('EVENT_CONTINUE',        20);
define('EVENT_CHANGE',          21);
define('EVENT_HANDLED',         22);
define('FIRE_MODE_PRE',         30);
define('FIRE_MODE_POST',        31);

define('FORMAT_HTML', 'html');
define('FORMAT_JSON', 'json');
define('FORMAT_XML', 'xml');
define('FORMAT_PLAIN', 'plain');

define('GEOBAZA_FILE_PATH', BASE_PATH.DIRECTORY_SEPARATOR.'_data'.DIRECTORY_SEPARATOR.'geobaza'.DIRECTORY_SEPARATOR.'geobaza.dat');
define('GEOBAZA_CACHE_NO', 0);
define('GEOBAZA_CACHE_MEMORY', 1);


$debug_levels=array(
    'QS_ALL'=>0,
    'QS_ERRORS'=>2,
    'QS_NOTICE'=>3,
);
if (file_exists('../vendor/autoload.php')){
    require_once '../vendor/autoload.php';
}
require_once (BASE_PATH.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'functions.php');
require_once (BASE_PATH.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'Autoloader.php');
require_once 'Dompdf/autoload.inc.php';
require_once (BASE_PATH.DIRECTORY_SEPARATOR.'GuzzleHttp'.DIRECTORY_SEPARATOR.'functions_include.php');
$autoloader = new Autoloader();
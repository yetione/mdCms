<?php
$result = array('success'=> false, 'error'=>'');
function fromPost($name, $default=null, $trim=true){
    return isset($_POST[$name]) && ($trim ? trim($_POST[$name]) : $_POST[$name]) != '' ? ($trim ? trim($_POST[$name]) : $_POST[$name]) : $default;
}
$siteName = fromPost('site_name', 'DefaultSite');
$baseUrl = fromPost('base');

$userLogin = fromPost('user_login', 'admin');
$userPassword = fromPost('user_password', '');
$userEmail = fromPost('user_email', 'admin@admin.loc');

//$dbHost = fromPost('db_host', '46.252.168.212');
$dbHost = fromPost('db_host', '192.168.1.101');
$dbUser = fromPost('db_user', 'qspace');
$dbPassword = fromPost('db_password', 'qspass');
$dbDatabase = fromPost('db_database', 'qspace');
$dbPort = fromPost('db_port', '3306');
$dbCharset = fromPost('db_charset', 'utf8');

$smtpHost = fromPost('smtp_host', '');
$smtpPort = fromPost('smtp_port', '');
$smtpSecure = fromPost('smtp_secure', '');

$sessionLifetime = fromPost('session_lifetime', '59min');
$sessionIdLifetime = fromPost('session_id_lifetime', '59min');

$cryptMethodSalt = 'e6090d49e424e7acfc3047';
$cryptSalt = '094797d';
//$cryptMethodSalt = substr(md5($siteName.uniqid()), 0, 22);
//$cryptSalt = substr(md5($siteName.uniqid()), 0, rand(5,7));

$dumperPath = '_cache/dumper';
$dumperSalt = substr(md5($siteName.uniqid()), 0, rand(3,5));

$path = dirname(__FILE__);
$definitionsTemplatePath = $path.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'definition.template.txt';
$definitionsPath = $path.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'definitions.php';

$configsTemplatePath = $path.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'configs.template.txt';
$configsPath = $path.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Configs'.DIRECTORY_SEPARATOR.'config.ini';


$definitionsTemp = file_get_contents($definitionsTemplatePath);
if (false === file_put_contents($definitionsPath, str_replace(array('%BASE_URL%'), array($baseUrl), $definitionsTemp))){
    $result['error'] = 'Error in create definitions';
    exit(json_encode($result));
}

$configsTemp = file_get_contents($configsTemplatePath);
if (false == file_put_contents($configsPath, str_replace(
        array(
            '%DB_HOST%', '%DB_USERNAME%', '%DB_PASSWORD%', '%DB_NAME%', '%DB_PORT%', '%DB_CHARSET%' ,
            '%CRYPT_METHOD_SALT%', '%CRYPT_SALT%',
            '%SESSION_LIFETIME%', '%SESSION_ID_LIFETIME%',
            '%DUMPER_PATH%', '%DUMPER_SALT%',
            '%SMTP_HOST%', '%SMTP_SECURE%', '%SMTP_PORT%'
        ),
        array(
            $dbHost, $dbUser, $dbPassword, $dbDatabase, $dbPort, $dbCharset,
            $cryptMethodSalt, $cryptSalt,
            $sessionLifetime, $sessionIdLifetime,
            $dumperPath, $dumperSalt,
            $smtpHost, $smtpSecure, $smtpPort
        ),
        $configsTemp
    ))){
    $result['error'] = 'Error in create configs';
    exit(json_encode($result));
}
require_once $path.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'definitions.php';

$app = new \install\InstallApp('Install', $autoloader);

$app->install(array('userLogin'=>$userLogin,'userPassword'=>$userPassword,'userIsAdmin'=>1, 'userEmail'=>$userEmail));
$result['data'] = get_object_vars($app);
$result['success'] = true;
http_response_code(200);
echo json_encode($result);

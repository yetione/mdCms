<?php
namespace Modules\Users\Controllers;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Debugger;
use Core\Mailer;
use Core\Module\Base\Controller;
use Modules\Email\Templates\Basic;
use Modules\Users\Entities\User;
use \Modules\Users\Users;

class Auth extends Controller{
    /**
     * @var \Modules\Users\Users
     */
    protected $module;

    /**
     * @var string
     */
    protected $vkRedirectUri = 'http://sektafood.ru/login?service=vk';

    protected $fbRedirectLink = 'http://sektafood.ru/login?service=fb';

    /**
     * @param array $data
     * @return array
     */
    protected function trimInput(array $data){
        foreach ($data as $k=>$v){
            $data[$k] = trim($v);
        }
        return $data;
    }

    public function checkLogin(array $data){
        $input = $this->module->getCore()->getInput();
        $email  = $input->post('email', '', TYPE_STRING);
        $password = $input->post('password', '');
        $rememberMe = $input->post('remember_me', 0, TYPE_INT);
        $result = $this->module->login($email, $password);

        $response = $this->module->getResponse();
        if ($result !== Users::SUCCESS){
            $response->back(['err'=>$result]);
            //$response->redirect(BASE_URL.'login?err='.$result);
        }else{
            $response->back();
            //$this->module->getResponse()->redirect($this->module->getResponse()->getReferer());
        }
    }

    public function login(array $data){
        $result = ['code'=>0,'message'=>'','user'=>[]];
        $userData = json_decode(trim(file_get_contents('php://input')), true);
        $email = QS_validate(trim($userData['Email']), TYPE_EMAIL, null);
        $password = $userData['Password'];
        $rememberMe = (int) $userData['RememberMe'];
        if (is_null($email) || is_null($password) || empty($password)){
            $result['code'] = 1;
            $result['message'] = 'Данные введены неправильно.';
        }
        if ($result['code'] == 0){
            $loginResult = $this->module->login($email, $password, ['loginBy'=>'Email']);
            switch ($loginResult){
                case Users::SUCCESS:
                    $result['code'] = 0;
                    $result['message'] = '';
                    $result['user'] = $this->module->getJSCurrentUser();
                    break;
                case Users::LOGIN_ERROR_EMPTY_DATA:
                    $result['code'] = 1;
                    $result['message'] = 'Данные введены неправильно.';
                    break;
                case Users::LOGIN_ERROR_WRONG_DATA:
                    $result['code'] = 2;
                    $result['message'] = 'Неправильно указан e-mail и/или пароль..';
                    break;
                case Users::ERROR_UNKNOWN:
                    $result['code'] = 3;
                    $result['message'] = 'Неизвестная ошибка.';
                    break;
                default:
                    $result['code'] = 4;
                    $result['message'] = 'Серверная ошибка.';
                    break;
            }
        }
        $view = $this->module->view('Simple');
        $view->render($result);
    }

    public function loginPage(array $data){
        $this->module->getResponse()->setTitle('Вход');
        $service = $this->getInput()->get('service', null, TYPE_STRING);
        if ($service == 'vk'){
            $vkCode = $this->getInput()->get('code', null, TYPE_STRING);
            if (!is_null($vkCode)){
                $vk = $this->module->getVkApi();
                $vk->setCode($vkCode);
                $token = $vk->loadAccessToken($this->vkRedirectUri);
                $userInfo = $vk->request('users.get', ['uids'=>$vk->getAccessToken('user_id'), 'fields'=>'uid,first_name,last_name,sex,bdate']);
                if (isset($userInfo['response'][0]['uid'])){
                    $userInfo = $userInfo['response'][0];
                    $user = $this->getEntityManager()->getEntityQuery('User')->findByVkId($userInfo['uid'])->loadOne(false, true);
                    if (!is_null($user)){
                        $this->module->setCurrentUser($user);
                        $this->module->getResponse()->redirect(BASE_URL);
                    }else{
                        $params = ['Uid'=>$userInfo['uid'], 'Name'=>$userInfo['first_name'], 'Surname'=>$userInfo['last_name'], 'Service'=>$service];
                        $this->module->getResponse()->redirect('login/vk?'.urldecode(http_build_query($params)));
                    }
                }
            }
        }elseif ($service == 'fb'){
            $fbCode = $this->getInput()->get('code', null, TYPE_RAW);
            if (!is_null($fbCode)){
                $fbApi = $this->module->getFbApi();
                $fbApi->setCode($fbCode);
                $token = $fbApi->loadAccessToken($this->fbRedirectLink);
                //var_dump($token);
                $userInfo = $fbApi->getUserInfo();
                //var_dump($userInfo);
                if (false !== $userInfo){
                    $user = $this->getEntityManager()->getEntityQuery('User')->findByFbId($userInfo['id'])->loadOne(false, true);
                    if (!is_null($user)){
                        $this->module->setCurrentUser($user);
                        $this->module->getResponse()->redirect(BASE_URL);
                    }else{
                        $params = ['Uid'=>$userInfo['id'], 'Name'=>$userInfo['first_name'], 'Surname'=>$userInfo['last_name'], 'Service'=>$service];
                        $this->module->getResponse()->redirect('login/vk?'.urldecode(http_build_query($params)));
                    }
                }
            }
        }
        $view = $this->module->view('Login');
        $view->render($this->module->getVkApi()->generateAuthUrl($this->vkRedirectUri));
    }

    public function loginVKPage(array $data){
        $this->module->getResponse()->setTitle('Регистрация');
        $view = $this->module->view('Registration');
        $view->render('registr_vk');
    }

    public function logout(array $data){
        if (!is_null($this->module->getCurrentUser()->getId()) && (int) $this->module->getCurrentUser()->getId() > 0){
            $view = $this->module->view('Logout');
            $view->render($this->module->logout());
            return;
        }
        $this->module->getResponse()->back();
    }

    public  function registrationPage(array $data){
        $this->module->getResponse()->setTitle('Регистрация');
        $view = $this->module->view('Registration');
        $view->render();
    }

    protected function registeredUser($userData, $requiredFields=null){
        $requiredFields = is_null($requiredFields) ? ['Name', 'Email', 'Password', 'PasswordConfirm', 'ReCaptcha'] : $requiredFields;
        $result = ['code'=>0, 'message'=>'', 'user'=>[]];
        $reCaptchaData = $this->loadReCaptchaData($userData['ReCaptcha']);
        if ($this->isEmptyUserData($userData, $requiredFields)){
            $result['code'] = 1;
            $result['message'] = 'Не заполнены обязательные поля.';
        }
        elseif (QS_validate($userData['Email'], TYPE_EMAIL, false) === false){
            $result['code'] = 2;
            $result['message'] = 'Email введен не правильно.';
        }
        elseif ($userData['Password'] !== $userData['PasswordConfirm']){
            $result['code'] = 3;
            $result['message'] = 'Пароль и подтверждение не совпадают.';
        }elseif (!$reCaptchaData['success']){
            $result['code'] = 4;
            $result['message'] = 'Капча не прошла проверку.';
        }
        try{
            $user = $this->getEntityManager()->getEntityQuery('User')->findByEmail($userData['Email'])->loadOne(false, true);
        }catch (StatementExecuteError $e){
            $result['code'] = 5;
            $result['message'] = 'Неизвестная ошибка.';
            $user = null;
        }
        if (!is_null($user)){
            $result['code'] = 6;
            $result['message'] = 'Пользователь с таким e-mail\'ом уже зарегистрирован.';
        }
        if ($result['code'] === 0){
            $userData['Password'] = substr(md5(uniqid('vk_user')), rand(0,3), 7);
            $user = $this->getEntityManager()->getEntity('User');
            $user->fromArray([
                'Login'=>$userData['Email'], 'Email'=>$userData['Email'], 'RegistrDate'=>time(), 'IsAdmin'=>false,
                'Name'=>$userData['Name'], 'Surname'=>$userData['Surname'], 'Patronymic'=>$userData['Patronymic'], 'VkId'=>$userData['VkId'], 'Phone'=>$userData['Phone'], 'FbId'=>$userData['FbId']
            ]);
            $user->setPassword($userData['Password'], false);
            $user->setIsNew(true);
            try{
                $user = $this->getEntityManager()->getEntityQuery('User')->save($user, true);
                $this->module->setCurrentUser($user);
                $result['NewUser'] = $user;
                $result['user'] = $this->module->getJSCurrentUser();
                $result['redirect_url'] = BASE_URL;
            }catch (StatementExecuteError $e){
                $result['code'] = 7;
                $result['message'] = 'Проблемы с регистрацией.';
                Debugger::log('Modules\Users\Controllers\Auth::registeredUser: Cant add user email: '.$userData['Email'].'. '.implode(', ',$e->getErrorData()));
            }
        }
        return $result;
    }

    public function doRegistration(array $data){
        $userData = $this->trimInput(json_decode(trim(file_get_contents('php://input')), true));
        $userData['VkId'] = null;
        $userData['FbId'] = null;
        $result = $this->registeredUser($userData);
        if ($result['code'] == 0){
            $this->sendRegistrEmail($result['NewUser'], $userData['Password'], false);
            unset($result['NewUser']);
        }
        $view = $this->module->view('Simple');
        $view->render($result);
    }

    public function doVkRegistration(array $data){
        $userData = $this->trimInput(json_decode(trim(file_get_contents('php://input')), true));
        $userData['Password'] = $userData['PasswordConfirm'] = substr(md5(uniqid('vk_user')), rand(0,3), 7);
        if (!isset($userData['Service'])){
            $view = $this->module->view('Simple');
            $view->render(['code'=>8, 'message'=>'Не передан сервис']);
            return false;
        }
        $userData['FbId'] = null;
        if ($userData['Service'] == 'fb'){
            $userData['Password'] = $userData['PasswordConfirm'] = substr(md5(uniqid('fb_user')), rand(0,3), 7);
            $userData['FbId'] = $userData['VkId'];
            $userData['VkId'] = null;
        }
        $result = $this->registeredUser($userData);
        if ($result['code'] == 0){
            $this->sendRegistrEmail($result['NewUser'], $userData['Password'], true);
            unset($result['NewUser']);
        }
        $view = $this->module->view('Simple');
        $view->render($result);
    }

    public function sendPassword(array $data){
        $this->module->getResponse()->setTitle('Восстановление пароля');
        $view = $this->module->view('Registration');
        $view->render('send_password');
    }

    public function restorePassword(array $data){
        $input = $this->getInput();
        $reCaptchaData = $input->post('g-recaptcha-response', TYPE_STRING);
        $email = $input->post('ClientEmail', null, TYPE_EMAIL);

        $reCaptchaData = $this->loadReCaptchaData($reCaptchaData);
        if (is_null($email) || !$reCaptchaData['success']){
            //$this->module->getResponse()->back();
            $this->module->getResponse()->redirect('/login/sendpassword?err=1');
            return false;
        }

        $em = $this->getEntityManager();
        $query = $em->getEntityQuery('User');
        $query->findByEmail($email);
        try{
            $user = $query->loadOne(false, true);
            if (is_null($user)){
                throw new StatementExecuteError(['code'=>5,'message'=>'User not found']);
            }
        }catch (StatementExecuteError $e){
            $this->module->getResponse()->redirect('/login/sendpassword?err=2');
            return false;
        }
        $userToken = $em->getEntity('UserToken');
        $userToken->setUserId($user->getId());
        $userToken->setType('forgot_password');
        $userToken->setValue(md5('ForgotPassword'.$email.time().rand(0,1000)));
        $userToken->setExpireTime(time()+60*60*24);
        $userToken->setIsNew(true);
        try{
            $userToken = $em->getEntityQuery('UserToken')->save($userToken, true);
        }catch (StatementExecuteError $e){
            $this->module->getResponse()->redirect('/login/sendpassword?err=3');
            return false;
        }
        if (!$this->sendRestorePasswordEmail($user, $userToken->getValue())){
            $this->module->getResponse()->redirect('/login/sendpassword?err=4');
            return false;
        }
        $this->module->getResponse()->setTitle('Восстановление пароля');
        $view = $this->module->view('Registration');
        $view->render('restore_password');

    }

    protected function sendRestorePasswordEmail($user, $token){
        $mail = new Basic();
        $mail->setSMTP(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        //$mail = Basic::getSMTPMailer(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        $mail->addAddress($user->getEmail());
        $mail->setSubject('Восстановление пароля на сайте #Sektafood!');
        $mail->setVar('header', 'ВОССТАНОВЛЕНИЕ ПАРОЛЯ');
        $mail->render(QS_path(['templates', 'emails','users','restore_passwrod.php'], false), ['user'=>$user, 'url'=>$_SERVER['HTTP_ORIGIN'].'/login/new_password?token='.$token]);
        $result = $mail->send();
        $mail->clear();
        /*$mail = Mailer::getSMTPMailer(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        $mail->setFrom('no-reply@sektafood.ru', 'SektaFood');
        $mail->addAddress($user->getEmail());
        $mail->Subject = "=?utf-8?B?". base64_encode("Восстановление пароля на сайте #SektaFood!"). "?=";
        $mail->addEmbeddedImage(QS_path(['templates','site', 'images', 'logo.png'], false), 'logo');
        $mail->addEmbeddedImage(QS_path(['templates','site', 'images', 'logo_black.png'], false), 'logo_black');
        $varables = [
            'images'=>['logo'=>['src'=>'cid:logo'], 'logo_black'=>['src'=>'cid:logo_black']],
            'user'=>['name'=>$user->getName()], 'url'=>$_SERVER['HTTP_ORIGIN'].'/login/new_password?token='.$token
        ];
        $template = Mailer::getTemplate(QS_path(['templates', 'site', 'email', 'restore_password.php'], false), $varables);
        $mail->isHTML();
        $mail->msgHTML($template);
        $result = $mail->send();
        $mail->clear();*/
        return $result;
    }

    public function newPassword(array $data){
        $this->module->getResponse()->setTitle('Восстановление пароля');

        $input = $this->getInput();
        $token = $input->get('token', null, TYPE_STRING);
        $err = $input->get('err', null, TYPE_INT);
        $msg = '';
        switch ($err){
            case 1:
                $msg = 'Пароль и/или подверждение пароля введены некорректно.';
                break;
            case 2:
                $msg = 'Не действительный токен.';
                break;
            case 3:
                $msg = 'Капча не прошла проверку.';
                break;
            case null:
                $msg = '';
                break;
            default:
                $msg = 'Неизвестная ошибка';
                break;
        }
        $msgTemplate = empty($msg) ? '' : '<div class="errors"><p class="error">'.$msg.'</p></div>';
        if (is_null($token)){
            $view = $this->module->view('Page');
            $view->render('Восстановление пароля', '<p>Восстановление пароля невозможно.</p>');
            return false;
        }
        $t = $this->checkToken($token);
        if (!$t){
            $view = $this->module->view('Page');
            $view->render('Восстановление пароля', '<p>Восстановление пароля невозможно. Не верный токен</p>');
            return false;
        }
        if ($t->getType() != 'forgot_password'){
            $view = $this->module->view('Page');
            $view->render('Восстановление пароля', '<p>Восстановление пароля невозможно. Не верный тип токена</p>');
            return false;
        }

        $view = $this->module->view('NewPassword');
        $view->render($token);
    }

    public function setNewPassword(array $data){
        $this->module->getResponse()->setTitle('Восстановление пароля');
        $input = $this->getInput();
        $reCaptchaData = $input->post('g-recaptcha-response', TYPE_STRING);
        $p = $input->post('Password', null, TYPE_RAW);
        $pC = $input->post('PasswordConfirm', null, TYPE_RAW);
        $token = $input->post('Token', null, TYPE_STRING);

        $reCaptchaData = $this->loadReCaptchaData($reCaptchaData);
        $t = $this->checkToken($token);

        if (is_null($p) || is_null($pC) || $p !== $pC){
            $this->module->getResponse()->redirect('/login/new_password', '?err=1&token='.$token);
            return false;
        }
        if (is_null($token) || $t->getType() != 'forgot_password'){
            $this->module->getResponse()->redirect('/login/new_password', '?err=2&token='.$token);
            return false;
        }
        if (!$reCaptchaData['success']){
            $this->module->getResponse()->redirect('/login/new_password', '?err=3&token='.$token);
            return false;
        }
        $query = $this->getEntityManager()->getEntityQuery('User');
        $query->findById($t->getUserId());
        try{
            $user = $query->loadOne(false, true);
            if (is_null($user)){
                throw new StatementExecuteError(['code'=>1, 'message'=>'User not found']);
            }
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Page');
            $view->render('Восстановление пароля', '<p>Восстановление пароля невозможно. Пользователь не найден</p>');
            return false;
        }
        /**
         * @var User $user
         */
        $user->setPassword($p, false);
        $query->reset();
        try{
            $user = $query->save($user, true);
            $this->getEntityManager()->getEntityQuery('UserToken')->findById($t->getId())->delete(false, true);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Page');
            $view->render('Восстановление пароля', '<p>Восстановление пароля невозможно. Ошибка при установке нового пароля</p>');
            return false;
        }
        $this->module->setCurrentUser($user);
        $view = $this->module->view('Page');
        $view->render('Восстановление пароля', '<p>Восстановление пароля прошло успешно.</p>');
    }

    protected function checkToken($token){
        $query = $this->getEntityManager()->getEntityQuery('UserToken');
        $query->findByValue($token);
        try{
            $t = $query->loadOne(false, true);
            if (is_null($t)){
                throw new StatementExecuteError(['code'=>1, 'message'=>'Token not found']);
            }
            if ((int) $t->getExpireTime() < time()){
                //$query->clear();
                $query->reset();
                $query->findById($t->getId());
                $query->delete(true, true);
            }
            return $t;
        }catch (StatementExecuteError $e){
            return false;
        }
    }

    /**
     * @param $user
     * @param $password
     * @param bool $sendPassword
     * @return bool
     */
    protected function sendRegistrEmail($user, $password, $sendPassword=false){

        $mail = new Basic();
        $mail->setSMTP(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        //$mail = Basic::getSMTPMailer(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        $mail->addAddress($user->getEmail());
        $mail->setSubject('Регистрация на сайте #Sektafood!');
        $mail->setVar('header', 'РЕГИСТРАЦИЯ ЗАВЕРШЕНА');
        $mail->render(QS_path(['templates', 'emails','users','registr_success.php'], false), ['user'=>$user, 'password'=>($sendPassword ? $password : '')]);
        $result = $mail->send();
        $mail->clear();

        /*$mail = Mailer::getSMTPMailer(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        $mail->setFrom('no-reply@sektafood.ru', 'SektaFood');
        $mail->addAddress($user->getEmail());
        $mail->Subject = "=?utf-8?B?". base64_encode("Регистрация на сайте #SektaFood!"). "?=";
        //$mail->Subject = iconv('UTF-8', 'Windows-1251','Регистрация на сайте #SektaFood!');
        $mail->addEmbeddedImage(QS_path(['templates','site', 'images', 'logo.png'], false), 'logo');
        $mail->addEmbeddedImage(QS_path(['templates','site', 'images', 'logo_black.png'], false), 'logo_black');
        $varables = [
            'images'=>['logo'=>['src'=>'cid:logo'], 'logo_black'=>['src'=>'cid:logo_black']],
            'user'=>['name'=>$user->getName(), 'login'=>$user->getEmail(), 'password'=>($sendPassword ? $password : '')]
        ];

        $template = Mailer::getTemplate(QS_path(['templates', 'site', 'email', 'registr_success.php'], false), $varables);
        $mail->isHTML();
        $mail->msgHTML($template);
        $result = $mail->send();
        $mail->clear();
        */
        return $result;
    }

    /**
     * @param $data
     * @param array $requiredFields
     * @return int
     */
    protected function isEmptyUserData($data, $requiredFields){
        foreach ($data as $field => $value){
            if (in_array($field, $requiredFields) && empty($value)){
                return 1;
            }
        }
        return 0;
    }

    /**
     * @param string $response
     * @return array
     */
    protected function loadReCaptchaData($response){
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $secret = '6LdwxSkTAAAAAGTpk9vLh3nUZABH4k7Ny_kk54c-';
        $ip = $this->getInput()->getIp();
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['secret'=>$secret,'response'=>$response,'remoteip'=>strval($ip)])
        ]);
        $data = curl_exec($curl);
        return json_decode($data, true);
    }


} 
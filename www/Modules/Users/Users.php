<?php
namespace Modules\Users;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Debugger;
use Core\Event\EventVar;
use Core\Module\Base\Module;
use Core\Core;
use Core\Meter;
use Modules\Users\Classes\FBApi;
use Modules\Users\Classes\VKApi;
use Modules\Users\Entities\User;

class Users extends Module{

    protected $moduleName = 'Users';

    /**
     * @var Core
     */
    protected $core;

    protected $accessGroups = array();

    /**
     * @var Entities\User
     */
    protected $currentUser;

    /**
     * @var VKApi
     */
    protected $vkApi;

    /**
     * @var FBApi
     */
    protected $fbApi;

    const SUCCESS = 0;
    const ERROR_UNKNOWN = 3;

    const LOGIN_ERROR_BANNED = 1;
    const LOGIN_ERROR_WRONG_DATA = 2;

    const LOGIN_ERROR_NO_ACCESS = 4;
    const LOGIN_ERROR_EMPTY_DATA = 5;

    const CURRENT_USER_KEY = 'Users.current_user';

    /**
     * @return User
     */
    public function getCurrentUser(){
        return $this->currentUser;
    }


    protected function init(array $configs){
        $this->vkApi = new VKApi(5623516, 'MfQvfGiowiGQD0W4YZ4x');
        $this->fbApi = new FBApi(1856860901215224, '31ad9f7640724ef48e869f0483777f38');
        $this->core->getEventManager()->hook('Application.Load', array($this, 'onAppLoad'));
        $this->core->getEventManager()->hook('Application.Close', array($this, 'onAppClose'));
        $this->core->getEventManager()->hook('Session.regenerate_id', array($this, 'onRegenerateId'));
        $this->core->getEventManager()->hook('Session.start', array($this, 'onSessionStart'), HOOK_MODE_PRE);
    }

    public function getBrowser(){
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown UA';
    }

    public function onAppClose(EventVar $ev){
        $user = $this->currentUser->toArray();
        $this->getCore()->getSession()->set(self::CURRENT_USER_KEY, $user);
    }

    public function onSessionStart(EventVar $ev){
        return EVENT_CONTINUE;
    }

    public function onAppLoad(EventVar $ev){
        /**
         * @var User $user
         */
        $user = $this->getCore()->getEntityManager()->getEntity('User');
        if (!is_null($sessionUser = $this->getCore()->getSession()->get(self::CURRENT_USER_KEY, null))){

            if ($sessionUser instanceof User){
                $user->_setEntityManager($this->getCore()->getEntityManager());
                $user->_setEntityMetadata($this->getCore()->getEntityManager()->getEntityMetadata('User'));
            }else{
                $user->fromArray($sessionUser);
            }

        }
        $this->setCurrentUser($user);
        $h = $this->getCore()->getCrypt()->hashStr('RememberMe'.$this->getBrowser());
        if (!is_null($data = $this->getCore()->getInput()->cookie($h))){
            //TODO:Remember me
        }
    }

    public function onRegenerateId(EventVar $ev){
        //Выход во время регенерации
        //$ev->get('session')->set(self::CURRENT_USER_KEY, $this->getCore()->getEntityManager()->getEntity('User')->toArray());

    }

    public function getAccessGroup($name){
        if (isset($this->accessGroups[$name])){
            return $this->accessGroups[$name];
        }
        return null;
    }

    public function hasFlag($group, $flag){
        if (isset($this->accessGroups[$group]) && (in_array($flag, $this->accessGroups[$group]['flags']) || in_array('z', $this->accessGroups[$group]['flags']))){
            return true;
        }
        return false;
    }

    public function hasFlags($group, array $flags){
        foreach ($flags as $flag){
            if (!$this->hasFlag($group, $flag)){
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $flag
     * @return bool
     */
    public function addAccessFlag($name, $description, $flag){
        $ent = $this->getCore()->getEntityManager()->getEntity('AccessFlag');
        $ent->setName($name);
        $ent->setDescription($description);
        $ent->setFlag($flag);
        try{
            $this->getCore()->getEntityManager()->getEntityQuery('AccessFlag')->save($ent, true);
            return true;
        }catch (StatementExecuteError $e){
            Debugger::log('Users::addAccessFlag: Cant add access flag: '.$name.'('.$flag.') '.implode(', ', $e->getErrorData()));
            return false;
        }
    }

    /**
     * @param User|int $user
     * @param string $flag
     * @return bool
     */
    public function addAccessFlagToUser($user, $flag){
        if (is_a($user, 'Modules\\Users\\Entities\\User')){
            $userId = $user->getId();
        }elseif(ctype_digit($user)){
            $userId = (int) $user;
        }else{
            throw new \RuntimeException('Users::addAccessFlagToUser: First argument must be int or user entity');
        }
        $ent = $this->getCore()->getEntityManager()->getEntityQuery('AccessFlag')->findByFlag($flag)->loadOne(false,true);
        if (!is_null($ent)){
            $userFlag = $this->getCore()->getEntityManager()->getEntity('UserFlags');
            $userFlag->setUserId($userId);
            $userFlag->setFlagId($ent->getId());
            $userFlag->setIsNew(true);
            try{
                $this->getCore()->getEntityManager()->getEntityQuery('UserFlags')->save($userFlag, true);
            }catch (StatementExecuteError $e){
                Debugger::log('Users::addAccessFlagToUser: Cant add flag '.$flag.' to user id '.$userId.' '.implode(', ',$e->getErrorData()));
                return false;
            }
            return true;
        }
        return false;
    }

    public function login($login, $password, array $option = array('loginBy'=>'Login')){
        $meter = new Meter('Функци логина');
        $meter->dir(array('logs','app_work_time', 'login'))->run();

        if (empty($login) || empty($password)){
            return self::LOGIN_ERROR_EMPTY_DATA;
        }
        $event = $this->getCore()->getEventManager()->event('Users.login');
        $event->set('login', $login)->set('password', $password);
        if (!$event->preFire()){
            return $event->get('reason', self::ERROR_UNKNOWN);
        }
        $query = $this->getCore()->getEntityManager()->getEntityQuery('User');
        switch ($option['loginBy']){
            case 'Login':
                $query->findByLogin($login);
                break;
            case 'Email':
                $query->findByEmail($login);
                break;
            default:
                Debugger::log('Users::login: not valid loginBy value: '.$option['loginBy']);
                return self::LOGIN_ERROR_WRONG_DATA;
        }
        if (is_null($user = $query->loadOne())){
            return self::LOGIN_ERROR_WRONG_DATA;
        }
        if (!$this->isPasswordsEqual($user->getPassword(), $password)){
            return self::LOGIN_ERROR_WRONG_DATA;
        }
        $event->postFire();
        $this->setCurrentUser($user);

        $meter->end(true);
        return self::SUCCESS;
    }

    public function logout(){
        $event = $this->getCore()->getEventManager()->event('Users.logout');
        $event->set('user', $this->currentUser);
        if (!$event->preFire()){
            return $event->get('reason', self::ERROR_UNKNOWN);
        }
        $user = $this->getCore()->getEntityManager()->getEntity('User');
        $this->setCurrentUser($user);
        $event->postFire();
        return self::SUCCESS;

    }

    public function getToken(){

    }

    /**
     * @param User $user
     * @throws \Core\Session\Exception\StateError
     */
    public function setCurrentUser(User $user){
        $this->getCore()->getSession()->set(self::CURRENT_USER_KEY, $user);
        $this->currentUser = $user;
    }

    public function isPasswordsEqual($hashedPass, $notHashedPass){
        $salt = substr($hashedPass, 0, 22);
        $hashed = $this->getCore()->getCrypt()->blowfish($notHashedPass, 11, $salt, true);
        return $hashedPass === $hashed;
    }

    public function hashPassword($password, $salt=null){
        return $this->getCore()->getCrypt()->blowfish($password, 11, is_null($salt) ? 'UNIQUE' : $salt, true);
    }

    /**
     * @param $login
     * @param $email
     * @param $password
     * @param $isAdmin
     * @return User|bool
     */
    public function createUser($login, $email, $password, $isAdmin){
        $password = $this->hashPassword($password);
        $newUser = $this->getCore()->getEntityManager()->getEntity('User');
        $newUser->setLogin($login);
        $newUser->setEmailt($email);
        $newUser->setPassword($password);
        $newUser->setRegistrDate(time());
        $newUser->setIsAdmin($isAdmin);
        try{
            $ent = $this->getCore()->getEntityManager()->getEntityQuery('User')->save($newUser, true);
        }catch (StatementExecuteError $e){
            Debugger::log('Users::createUser: Cant add user login: '.$login.' email:'.$email.' '.implode(', ',$e->getErrorData()));
            return false;
        }
        return $ent;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getJSCurrentUser($fields=['Id','Login','Email','Name','Surname', 'Patronymic', 'Phone']){
        return $this->getCurrentUser()->toArray(true,$fields);
    }

    /**
     * @return VKApi
     */
    public function getVkApi(){
        return $this->vkApi;
    }

    /**
     * @param VKApi $vkApi
     */
    public function setVkApi($vkApi){
        $this->vkApi = $vkApi;
    }

    /**
     * @return FBApi
     */
    public function getFbApi(){
        return $this->fbApi;
    }

    /**
     * @param FBApi $fbApi
     */
    public function setFbApi($fbApi){
        $this->fbApi = $fbApi;
    }
} 
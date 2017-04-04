<?php
namespace Core\Session;


use Core\DataBase\Model\Entity;
use Core\Event\EventManager;
use Core\Input;
use Modules\Food\Classes\Cart;

class Session {

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var mixed[]
     */
    protected $data;

    /**
     * @var bool
     */
    protected $changed;

    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var string
     */
    protected $variablePrefix = 'session.';

    /**
     * @var EventManager
     */
    protected $eventManger;

    /**
     * @var StorageInterface
     */
    protected $storage;

    const STATE_ACTIVE = 'active';
    const STATE_INACTIVE = 'inactive';
    const STATE_EXPIRE = 'expire';

    const CHECK_OK = 'check_ok';
    const CHECK_EXPIRE = 'check_expire';
    const CHECK_ID_EXPIRE = 'check_id_expire';
    const CHECK_NOT_SECURITY = 'check_not_security';


    const STATE_RESTART = 'restart';
    const STATE_DESTROY = 'destroy';
    const STATE_ERROR = 'error';
    const STATE_REGENERATE_ID = 'regenerate_id';


    /**
     * @param array $options
     * @param Input $input
     * @param EventManager $eventManager
     */
    public function __construct(array $options=array(), Input $input, EventManager $eventManager){
        $this->setIniParams();
        $this->options = array_merge($this->getDefaultOptions(), $options);
        $this->setOptions($this->options);
        $this->setInput($input);
        $this->eventManger = $eventManager;
        $this->changed = false;
        $this->data = array();
        $this->id = null;
        $this->name = 'Default';

        $this->state = self::STATE_INACTIVE;
    }

    public function __destruct(){
        $this->write();
    }

    /**
     * @param bool $isActivity
     * @return bool
     * @throws Exception\StartError
     */
    public function start($isActivity=true){
        if ($this->state == self::STATE_ACTIVE) return true;
        if (!$this->startPhpSession()){
            throw new Exception\StartError(__CLASS__.': Can\'t start session.');
        }
        $this->state = self::STATE_ACTIVE;
        $this->data = $_SESSION;
        $this->setCounters($isActivity);
        $state = $this->check();
        switch ($state){
            case self::CHECK_ID_EXPIRE:
                $oldId = $this->getId();
                $this->restart();
                $event = $this->eventManger->event('Session.regenerate_id');
                $event->set('session', $this)->set('old_id', $oldId);
                $event->fire();
                break;
            case self::CHECK_NOT_SECURITY:
            case self::CHECK_EXPIRE:
                $oldId = $this->getId();
                $this->destroy();
                $this->start($isActivity);
                $event = $this->eventManger->event('Session.'.substr($state,6));
                $event->set('session', $this)->set('old_id', $oldId);
                $event->fire();
                break;
            case self::CHECK_OK:
                //Это нужно для предотвращения блокировки сесии
                session_write_close();
                $this->changed = false;
                break;
            default:
                throw new Exception\StartError(__CLASS__.': Unprocessed state: '.$state);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function destroy(){
        // Проверяем статус сессии
        if ($this->state === self::STATE_DESTROY){
            return false;
        }
        //Для полного закрытия сесси надо проверить ее наличие в куках т.к. по умолчанию она хранится там
        $session_name = $this->getName();
        if ($this->input->cookie($session_name)){
            $cookieOptions = $this->getOption('cookie');
            setcookie($session_name, '', time() - 42000, $cookieOptions['path'], $cookieOptions['domain']);
        }
        session_unset();
        session_destroy();
        $this->state = self::STATE_DESTROY;
        return true;
    }

    /**
     * @return bool
     * @throws Exception\StateError
     */
    public function restart(){
        session_regenerate_id(true);
        $start = time();
        $this->set($this->getVariablePrefix().'timer.last', $start);
        $this->set($this->getVariablePrefix().'timer.now', $start);

        $this->set($this->getVariablePrefix().'client.address', $this->input->server('REMOTE_ADDR', null, TYPE_RAW));
        $this->set($this->getVariablePrefix().'client.browser', $this->input->server('HTTP_USER_AGENT', null, TYPE_RAW));
        $this->id = session_id();
        return true;
    }

    public function write(){
        if ($this->changed){
            $this->startPhpSession();
            foreach ($this->data as $key=>&$value){
                if ($value instanceof Entity){
                    $value->_setEntityManager(null);
                    $value->_setEntityMetadata(null);
                }elseif ($value instanceof Cart){
                    $value->unsetCity();
                    $value->unsetDb();
                }
            }
            unset($value);
            $_SESSION = $this->data;
            session_write_close();
        }
    }

    /**
     * @return bool|string
     * @throws Exception\StateError
     */
    protected function check(){
        $curTime = $this->get($this->getVariablePrefix().'timer.now', 0);
        if ($this->getOption('session_lifetime')){
            //Проверяем не истекла ли сессия, с момента последнего действия
            $maxTime = (int) $this->get($this->getVariablePrefix().'timer.last', 0) + $this->getOption('session_lifetime');
            if ($maxTime < $curTime){
                return self::CHECK_EXPIRE;
            }
        }

        if ($this->getOption('id_lifetime')){
            //Проверяем, не истек ли период действия идентификатора сессии
            $maxTime = (int) $this->get($this->getVariablePrefix().'timer.last', 0) + $this->getOption('id_lifetime');
            if ($maxTime < $curTime){
                return self::CHECK_ID_EXPIRE;
            }
        }

        if ( !is_null($from = $this->input->server('HTTP_X_FORWARDED_FOR', null, TYPE_RAW)) ) {
            $this->set($this->getVariablePrefix().'client.forwarded', $from);
        }

        if (in_array('ip', $this->getOption('security')) && !is_null($ipS = $this->input->server('REMOTE_ADDR', null, TYPE_RAW)) ){
            $ip = $this->get($this->getVariablePrefix().'client.address');

            if ($ip === null){
                $this->set($this->getVariablePrefix().'client.address', $ipS);
            }
            elseif ($ipS !== $ip){
                return self::CHECK_NOT_SECURITY;
            }
        }

        if (in_array('browser', $this->getOption('security')) && !is_null($browserS = $this->input->server('HTTP_USER_AGENT', null, TYPE_RAW))){
            $browser = $this->get($this->getVariablePrefix().'client.browser');

            if ($browser === null){
                $this->set($this->getVariablePrefix().'client.browser', $browserS);
            }
            elseif ($browserS !== $browser){
                return self::CHECK_NOT_SECURITY;
            }
        }

        return self::CHECK_OK;
    }

    /**
     * @param bool $isActivity
     * @return bool
     * @throws Exception\StateError
     */
    protected function setCounters($isActivity=true){
        $counter = $this->get($this->getVariablePrefix().'counter', 0);
        ++$counter;
        $this->set($this->getVariablePrefix().'counter', $counter);
        $now = time();
        if (!$this->has($this->getVariablePrefix().'timer.start')){
            $this->set($this->getVariablePrefix().'timer.start', $now);
        }
        //Последнее использование сессии - данные в timer.now
        if ($isActivity){
            $this->set($this->getVariablePrefix().'timer.last', $this->get($this->getVariablePrefix().'timer.now', $now));
            $this->set($this->getVariablePrefix().'timer.now', $now);
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function startPhpSession(){
        $this->setName($this->name);
        register_shutdown_function('session_write_close');
        session_cache_limiter('nocache');
        session_start();
        $this->id = session_id();
        return $this->id !== '';
    }

    /**
     * @param string $variable
     * @param null $default
     * @throws Exception\StateError
     * @return mixed|null
     */
    public function get($variable, $default=null){
        if ($this->state !== self::STATE_ACTIVE && $this->state !== self::STATE_EXPIRE){
            throw new Exception\StateError(__CLASS__.': session\'s state must be ACTIVE or EXPIRE');
        }
        if (isset($this->data[$variable])){
            return $this->data[$variable];
        }
        return $default;
    }

    /**
     * @param string $variable
     * @param mixed|null $value
     * @return mixed|null
     * @throws Exception\StateError
     */
    public function set($variable, $value=null){
        if ($this->state !== self::STATE_ACTIVE){
            throw new Exception\StateError(__CLASS__.': session\'s state must be ACTIVE');
        }
        $this->changed = true;
        $old = isset($this->data[$variable]) ? $this->data[$variable] : null;
        if (null === $value){
            unset($this->data[$variable]);
        }
        else{
            $this->data[$variable] = $value;
        }
        if ((int) $this->getOption('auto_write')){
            $this->write();
            $this->changed = false;
        }
        return $old;
    }

    /**
     * @param string $variable
     * @return bool
     * @throws Exception\StateError
     */
    public function has($variable){
        if ($this->state !== self::STATE_ACTIVE){
            throw new Exception\StateError(__CLASS__.': session\'s state must be ACTIVE');
        }
        return isset($this->data[$variable]);
    }

    /**
     * @return array
     */
    public function getOptions(){
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options){
        $this->options = $options;
        foreach ($this->options as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    protected function setIniParams(){
        ini_set('session.auto_start', '0');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.use_cookies','1');
    }

    /**
     * @param $option
     * @return mixed|null
     */
    public function getOption($option){
        return $this->hasOption($option) ? $this->options[$option] : null;
    }

    /**
     * @return array
     */
    protected function getDefaultOptions(){
        return array(
            'name_prefix'=>'Session',
            'session_lifetime'=>'15min',
            'id_lifetime'=>'10sec',
            'security'=>array('browser','ip'),
            'token_length'=>32,
            'auto_write'=>0,//Запись данных в сессию сразу после записи в локальный массив
            'cookie'=>session_get_cookie_params()
        );
    }

    /**
     * @return string
     */
    public function getState(){
        return $this->state;
    }

    /**
     * @return string
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(){
        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage){
        $this->storage = $storage;
    }

    /**
     * @param string $option
     * @return mixed|null
     */
    protected function getDefaultOption($option){
        $defaults = $this->getDefaultOptions();
        return isset($defaults[$option]) ? $defaults[$option] : null;
    }

    /**
     * @param string $option
     * @return bool
     */
    protected function hasOption($option){
        return isset($this->options[$option]);
    }

    /**
     * @param string $option
     * @param mixed $value
     * @param bool $default
     * @param bool $add
     */
    public function setOption($option, $value, $default=false, $add=false){
        if ($this->hasOption($option) || $add){
            if ($default){
                $value = $this->getDefaultOption($option);
            }
            if ($option == 'session_lifetime'){
                $value = QS_parseTime($value);
                ini_set('session.cookie_lifetime', $value);
                ini_set('session.gc_maxlifetime', $value);
            }elseif ($option == 'id_lifetime'){
                $value = QS_parseTime($value);
            }elseif($option == 'security' && !is_array($value)){
                $value = explode(',',$value);
            }elseif ($option == 'cookie'){
                $value = array_merge($value, session_get_cookie_params());
                $this->setCookieOptions($value);
            }
            $this->options[$option] = $value;
        }
    }

    /**
     * @param array $options
     * @return bool
     */
    public function setCookieOptions(array $options){
        session_set_cookie_params($options['lifetime'], $options['path'],
            $options['domain'], $options['secure'], $options['httponly']);
        return true;
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function setName($name){
        $old = $this->name;
        $this->name = strval($name); //$this->_crypt->md5('QSSession'.$new_name);
        session_name($this->getOption('name_prefix').$name);
        return $old;
    }

    /**
     * @return string
     */
    public function getVariablePrefix(){
        return $this->variablePrefix;
    }

    /**
     * @param string $variablePrefix
     */
    public function setVariablePrefix($variablePrefix){
        $this->variablePrefix = strval($variablePrefix);
    }

    /**
     * @return Input
     */
    public function getInput(){
        return $this->input;
    }

    /**
     * @param Input $input
     */
    public function setInput(Input $input){
        $this->input = $input;
    }
} 
<?php
namespace Core;


use PHPMailer\PHPMailer;
use PHPMailer\phpmailerException;

class Mailer extends PHPMailer{

    /**
     * @var array
     */
    protected static $defaultSMTPOptions = [
        'ThrowException'=>false,
        'Host'=>'smtp.peterhost.ru',
        'Port'=>25,
        'Auth'=>true,
        'Secure'=>'tls',
        'Options'=>[],
        'AutoTLS'=>true,
        'Debug'=>0,
        'KeepAlive'=>false
    ];

    protected static $mailerOptions = [
        'LogSendErrors'=>true,
        'Language'=>'ru'
    ];

    public function __construct($exceptions = null){
        parent::__construct($exceptions);
        $this->setLanguage(self::getMailerOption('Language'));
        $this->setDefaultData();
    }

    protected function setDefaultData(){
        $this->setFrom('no-reply@sektafood.ru', 'SektaFood');
    }

    public function setSubject($sbj){
        $this->Subject = "=?utf-8?B?". base64_encode($sbj). "?=";
    }

    public function setSMTP($options=[]){
        $options = array_merge(self::getDefaultSMTPOptions(), $options);
        if (!isSetArrayKeys(['Host', 'Port'], $options)){
            throw new \InvalidArgumentException('Options must have Host and Port keys.');
        }
        $this->isSMTP();
        $this->Host = $options['Host'];
        $this->Port = $options['Port'];
        $this->SMTPAuth = $options['Auth'];
        $this->Username = $options['Username'];
        $this->Password = $options['Password'];
        $this->SMTPSecure = $options['Secure'];
        $this->SMTPOptions = $options['Options'];
        $this->SMTPAutoTLS = $options['AutoTLS'];
        $this->SMTPDebug = $options['Debug'];
        $this->SMTPKeepAlive = $options['KeepAlive'];
        $this->Priority;
    }

    public function clear(){
        $this->clearAddresses();
        $this->clearAllRecipients();
        $this->clearAttachments();
        $this->clearBCCs();
        $this->clearCCs();
        $this->clearCustomHeaders();
        $this->clearReplyTos();
        $this->clearQueuedAddresses('to');
        $this->clearQueuedAddresses('cc');
        $this->clearQueuedAddresses('bcc');
    }

    /**
     * @param array $options
     * @return $this
     */
    public static function getSMTPMailer(array $options=[]){
        $options = array_merge(self::getDefaultSMTPOptions(), $options);
        if (!isSetArrayKeys(['Host', 'Port'], $options)){
            throw new \InvalidArgumentException('Options must have Host and Port keys.');
        }
        /**
         * @var PHPMailer $mailer
         */
        $mailer = new self($options['ThrowException']);
        $mailer->isSMTP();
        $mailer->Host = $options['Host'];
        $mailer->Port = $options['Port'];
        $mailer->SMTPAuth = $options['Auth'];
        $mailer->Username = $options['Username'];
        $mailer->Password = $options['Password'];
        $mailer->SMTPSecure = $options['Secure'];
        $mailer->SMTPOptions = $options['Options'];
        $mailer->SMTPAutoTLS = $options['AutoTLS'];
        $mailer->SMTPDebug = $options['Debug'];
        $mailer->SMTPKeepAlive = $options['KeepAlive'];
        $mailer->Priority;
        return $mailer;
    }

    /**
     * @param string $option
     * @param mixed $value
     */
    public static function setDefaultSMTPOption($option, $value){
        self::$defaultSMTPOptions[$option] = $value;
    }

    /**
     * @param string $option
     * @return mixed|null
     */
    public static function getDefaultSMTPOption($option){
        return isset(self::$defaultSMTPOptions[$option]) ? self::$defaultSMTPOptions[$option] : null;
    }

    /**
     * @return array
     */
    public static function getDefaultSMTPOptions(){
        return self::$defaultSMTPOptions;
    }

    /**
     * @param array $SMTPOptions
     */
    public static function setDefaultSMTPOptions($SMTPOptions){
        self::$defaultSMTPOptions = $SMTPOptions;
    }

    /**
     * @param string $option
     * @param mixed $value
     */
    public static function setMailerOption($option, $value){
        self::$mailerOptions[$option] = $value;
    }

    /**
     * @param string $option
     * @return mixed|null
     */
    public static function getMailerOption($option){
        return isset(self::$mailerOptions[$option]) ? self::$mailerOptions[$option] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function send(){
        try{
            if (parent::send()){
                return true;
            }else{
                if (self::getMailerOption('LogSendErrors')){
                    Debugger::log('Mailer::send: Error when sending an email. '.$this->ErrorInfo);
                }
                return false;
            }
        }catch (phpmailerException $e){
            if (self::getMailerOption('LogSendErrors')){
                Debugger::log('Mailer::send: Error when sending an email. '.$this->ErrorInfo);
            }
            if ($this->exceptions) {
                throw $e;
            }
            return false;
        }
    }

    public static function getTemplate($path, $variables=[]){
        extract($variables);
        ob_start();
        require $path;
        $content = ob_get_clean();
        return $content;
    }
}
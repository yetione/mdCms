<?php
namespace Core\Response;


class JSONResponse extends Response{

    /**
     * @var mixed[]
     */
    protected $data=array();

    protected $format = self::FORMAT_JSON;

    const STATUS_OK = 'OK';
    const STATUS_ERROR = 'error';
    const KEY_STATUS = 'status';
    const KEY_MESSAGE = 'message';


    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value){
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default=null){
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * @return string
     */
    public function render(){
        header('Content-Type: application/json');
        return json_encode($this->data);
    }


    protected function onError($code, $message){
        $this->set(self::KEY_STATUS, self::STATUS_ERROR);
        $this->set(self::KEY_MESSAGE, $message);
    }
}
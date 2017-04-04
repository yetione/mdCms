<?php
namespace Core\DataBase\Exception;


class StatementExecuteError extends \Exception {
    protected $errorData = array();

    public function __construct($errorData= array(), $message = "", $code = 0, \Exception $previous = null){
        $this->setErrorData($errorData);
        parent::__construct($message, $code,$previous);
    }

    /**
     * @return array
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * @param array $errorData
     */
    public function setErrorData($errorData)
    {
        $this->errorData = $errorData;
    }
}
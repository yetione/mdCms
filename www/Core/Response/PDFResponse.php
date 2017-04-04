<?php
namespace Core\Response;


class PDFResponse extends Response{


    protected $content;

    public function __construct(){

    }

    /**
     * @return mixed
     */
    public function getContent(){
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content){
        $this->content = $content;
    }

    protected function onError($code, $message){
        // TODO: Implement onError() method.
    }

    /**
     * @return string
     */
    public function render(){
        header('Content-type: application/pdf');
        // TODO: Implement render() method.
    }
}
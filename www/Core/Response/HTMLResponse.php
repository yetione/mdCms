<?php
namespace Core\Response;

use Core\Exception\FileNotFound;


class HTMLResponse extends Response{

    /**
     * @var string
     */
    protected $template;

    protected $layout;

    protected $templatePath;

    protected $title='';

    protected $keywords;

    protected $description;

    protected $charset = 'utf-8';

    protected $titlePostfix = '';

    protected $headHTML = '';

    protected $titlePrefix ='';

    protected $headers = [];



    public function __construct($template, $layout){
        $this->format = self::FORMAT_HTML;
        $this->templatePath = QS_path(array('templates', $template), false);
        if (!file_exists($this->templatePath)){
            throw new FileNotFound(__CLASS__.': template '.$template.' not exists');
        }
        $this->layout = $layout;
        $this->headers = [];
    }

    /**
     * @return string
     */
    public function getHeadHTML(){
        return $this->headHTML;
    }

    /**
     * @param string $html
     */
    public function addHeadHTML($html){
        $this->headHTML .= $html;
    }

    /**
     * @param string $html
     */
    public function setHeadHTML($html){
        $this->headHTML = $html;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords){
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getKeywords(){
        return $this->keywords;
    }

    /**
     * @return string
     */
    public function getDescription(){
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description){
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCharset(){
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset($charset){
        $this->charset = $charset;
    }

    public function getFilePath($file){
        $file = explode('/', $file);
        array_unshift($file, $this->templatePath);
        return QS_path($file, false, false, false);
    }

    public function render(){
        $layoutPath = $this->getFilePath('layouts/'.$this->layout.'.php');
        foreach ($this->headers as $h){
            header($h);
        }
        ob_start();
        include $layoutPath;
        $content = ob_get_clean();
        return $content;
    }

    /**
     * @return mixed
     */
    public function getLayout(){
        return $this->layout;
    }

    /**
     * @param mixed $layout
     */
    public function setLayout($layout){
        $this->layout = $layout;
    }

    /**
     * @return string
     */
    public function getTitlePostfix(){
        return $this->titlePostfix;
    }

    /**
     * @param string $titlePostfix
     */
    public function setTitlePostfix($titlePostfix)
    {
        $this->titlePostfix = $titlePostfix;
    }

    /**
     * @return string
     */
    public function getTitlePrefix(){
        return $this->titlePrefix;
    }

    /**
     * @param string $titlePrefix
     */
    public function setTitlePrefix($titlePrefix){
        $this->titlePrefix = $titlePrefix;
    }

    /**
     * @return string
     */
    public function getHTMLTitle(){
        return $this->titlePrefix.$this->title.$this->titlePostfix;
    }

    /**
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title){
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function addHeader($header){
        $this->headers[] = $header;
    }


    protected function onError($code, $message){
        $path = $this->getFilePath("blocks/errors/{$code}.php");
        if (!file_exists($path)){
            $path = $this->getFilePath('blocks/error.php');
        }
        $block = $this->createBlock('content', $path);
        $block->set('error', 'Внутренняя ошибка');
    }
}
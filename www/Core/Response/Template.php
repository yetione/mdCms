<?php
namespace Core\Response;


use Core\Exception\FileNotFound;

class Template {

    const BASE_DIR = TEMPLATES_PATH;

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @var array
     */
    protected $variables = array();

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string|null
     */
    protected $layout;

    /**
     * @var bool
     */
    protected $throwException = true;

    public function __construct($template, $layout=null, $throwException=true){
        $this->setThrowException($throwException);
        $this->setTemplate($template);
        $this->setLayout($layout);
    }

    /**
     * @param string $variable
     * @param mixed $value
     * @param bool $override
     * @return $this
     */
    public function set($variable, $value, $override=false){
        if (!isset($this->variables[$variable]) || $override){
            $this->variables[$variable] = $value;
        }
        return $this;
    }

    /**
     * @param string $variable
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($variable, $default=null){
        return isset($this->variables[$variable]) ? $this->variables[$variable] : $default;
    }

    /**
     * @return string
     */
    public function getTemplate(){
        return $this->template;
    }

    /**
     * @param string $template
     * @param bool $force
     */
    public function setTemplate($template, $force=false){
        $this->template = $template;
    }



    /**
     * @return null|string
     */
    public function getLayout(){
        return $this->layout;
    }

    /**
     * @param null|string $layout
     */
    public function setLayout($layout){
        $this->layout = $layout;
    }

    /**
     * @return boolean
     */
    public function isThrowException(){
        return $this->throwException;
    }

    /**
     * @param boolean $throwException
     */
    public function setThrowException($throwException){
        $this->throwException = $throwException;
    }

    public function includeModule($name){
        $path = QS_path([TEMPLATES_DIR, $this->getTemplate(), $name], false);
        if (!file_exists($path)){
            return false;
        }


        //return $this->processDir($name);
        return $this->processDir($path);

    }

    public function processDir($dir){
        $result = array();
        $str = '';
        $cdir = scandir($dir,1);
        foreach ($cdir as $key => $value) {
            if (!in_array($value,array(".",".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $str .= $this->processDir($dir . DIRECTORY_SEPARATOR . $value);
                    //$result[$value] = $this->processDir($dir . DIRECTORY_SEPARATOR . $value, $current);

                }
                else {
                    $result[] = $value;

                    $str .= $this->getFileStr($dir . DIRECTORY_SEPARATOR . $value);
                }
            }
        }

        return $str;
    }

    protected function getFileStr($file){
        $ext = strtolower(substr($file, strrpos($file, '.')+1));
        switch ($ext){
            case 'js':
                $file = substr($file, strlen(BASE_PATH)+1);
                return '<script src="'.$file.'"></script>';
            case 'less':
                $file = substr($file, strlen(BASE_PATH)+1);
                return '<link rel="stylesheet/less" type="text/css" href="'.$file.'">';
            case 'php':
                ob_start();
                require $file;
                return ob_get_clean();
            case 'html':
                return '';
            default:
                return '<!--error '.$file.'-->';
        }

    }

    /**
     * @param string $variable
     * @param bool $escape
     */
    protected function show($variable, $escape=true){
        echo (isset($this->variables[$variable]) ? ($escape ? $this->escape($this->variables[$variable]) : $this->variables[$variable]) : null);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function escape($value){
        return htmlspecialchars($value);
    }

    /**
     * @return string
     */
    public function getTemplatePath(){
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     */
    public function setTemplatePath($templatePath){
        if (!is_file($templatePath)){
            throw new \RuntimeException(__METHOD__.": Cant set template path: {$templatePath} is not a file.");
        }
        $this->templatePath = $templatePath;
    }

    /**
     * @param string $filePath
     */
    protected function import($filePath){
        $includePath = $filePath;
        if ($filePath[0] !== '/'){
            $includePath = QS_joinPath(Template::BASE_DIR, $filePath);
        }
        if (!is_file($includePath)){
            throw new \RuntimeException(__METHOD__."Cant import template: {$includePath}");
        }
        require $includePath;
    }

    /**
     * @return string
     */
    public function render(){
        ob_start();
        require $this->getTemplatePath();
        return ob_get_clean();
    }
}
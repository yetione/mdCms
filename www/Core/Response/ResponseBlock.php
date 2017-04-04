<?php
namespace Core\Response;


use Core\Core;

class ResponseBlock  {

    protected $variables = array();

    protected $filePath;

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var \Core\DataBase\Entities\User
     */
    protected $user;


    public function __construct($file, Core $core){
        $this->filePath = $file;
        $this->core = $core;
        $this->user = $core->getSession()->get('Users.current_user');
    }

    public function setVars(array $vars){
        $this->variables = $vars;
    }

    public function getBC(){
        $args = func_get_args();
        array_unshift($args, array('Главная', '/'));
        $result = '';
        $argsCount = count($args);
        for($i=0;$i<$argsCount;$i++){
            $result .= '<li'.($i == ($argsCount-1) ? ' class="active">' : '>').
                (count($args[$i]) == 2 ? '<a href="'.$args[$i][1].'">'.$args[$i][0].'</a>' : $args[$i][0]).'</li>';
        }
        return "<ol class='breadcrumb'>{$result}</ol>";
    }

    public function currentUrl($newParams=array()){
        $newParams = array_merge($_GET, $newParams);
        unset($newParams['_request']);
        $queryStr = http_build_query($newParams, '', ini_get('arg_separator.output'), PHP_QUERY_RFC3986);
        return $_REQUEST['_request'].'?'.$queryStr;
    }

    public function set($var, $value, $override = true){
        if (!isset($this->variables[$var]) || $override){
            $this->variables[$var] = $value;
        }
        return $this;
    }

    public function get($var, $default=null){
        return isset($this->variables[$var]) ? $this->variables[$var] : $default;
    }

    public function render(){
        ob_start();
        include $this->filePath;
        $content = ob_get_clean();
        return $content;
    }

    public function __toString(){
        return $this->render();
    }

    public function getCore(){
        return $this->core;
    }


} 
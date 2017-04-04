<?php
namespace Modules\Email\Templates;


use Core\Mailer;

class Basic extends Mailer{

    protected $templateData = [
        'slogan'=>'<b>#SEKTAFOOD</b> - это доставка наборов полезных и вкусных блюд по Москве',
        'header'=>'',
    ];

    protected $images = [];


    protected function setDefaultData(){
        parent::setDefaultData();
        $this->isHTML();
        //$this->addImage(QS_path(['templates','emails', 'imagess', 'sektafood-logo.png'], false), 'logo');
        //$this->addImage(QS_path(['templates','emails', 'imagess', 'bg.jpg'], false), 'backgroundImage');
        //$this->addImage(QS_path(['templates','site', 'images', 'logo_black.png'], false), 'logo-black');
        //$this->addImage(QS_path(['templates','site', 'images', 'logo_black.png'], false), 'logo-black');
    }

    public function render($template, $variables=[]){
        foreach ($this->images as $alias=>$path){
            $this->addEmbeddedImage($path, $alias);
        }
        extract($variables);
        ob_start();
        include $template;
        $result = ob_get_clean();
        //var_dump($result);
        $this->msgHTML($result);
    }

    /**
     * @param string $path
     * @param string $alias
     * @param bool $override
     * @return $this
     */
    public function addImage($path, $alias, $override=false){
        if (!isset($this->images[$alias]) || $override){
            $this->images[$alias] = $path;
        }
        return $this;
    }

    public function setVar($name, $value){
        $this->templateData[$name] = $value;
    }

    public function setVars($vars){
        $this->templateData = array_merge($this->templateData, $vars);
    }

    public function getVar($name){
        return isset($this->templateData[$name]) ? $this->templateData[$name] : null;
    }

}
<?php
namespace Core\DataBase\Generator;


class EntitiesGenerator extends Generator{

    public function generate(){
        $namespace = $this->generateNamespace();
        $use = $this->generateUse();
        $className = $this->generateClassName();
        $template = "<?php
{$namespace}\n
{$use}\n
{$className}
{
\t\tprotected function init(){}
}
";
        return $this->write($this->metadata->getName(), $template);
    }
}
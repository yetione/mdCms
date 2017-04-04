<?php
namespace Core\DataBase\Generator;


class QueriesGenerator extends Generator {

    protected $extendsClass = 'EntityQuery';

    public function generate(){
        $namespace = $this->generateNamespace();
        $use = $this->generateUse();
        $className = $this->generateClassName();
        $template = "<?php
{$namespace}\n
{$use}\n
{$className}
{
}
";
        return $this->write($this->metadata->getName(), $template);
    }
}
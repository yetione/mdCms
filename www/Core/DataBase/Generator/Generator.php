<?php
namespace Core\DataBase\Generator;


use Core\DataBase\Model\EntityMetadata;
use Core\DataBase\Utils\PhpNameGenerator;
use Core\DataBase\Utils\SetOptions;

abstract class Generator {

    use SetOptions;

    /**
     * @var string
     */
    protected $filesDirectory;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string[]
     */
    protected $useClasses = array();

    /**
     * @var PhpNameGenerator
     */
    protected $nameGenerator;

    /**
     * @var EntityMetadata
     */
    protected $metadata;

    /**
     * @var bool
     */
    protected $regenerateExisting = true;

    /**
     * @var string
     */
    protected $extendsClass;

    /**
     * @var string[]
     */
    protected $implementsInterfaces = array();

    public function __construct(array $options){
        $this->nameGenerator = new PhpNameGenerator();
        $this->setOptions($options);
    }

    abstract public function generate();

    /**
     * @return string
     */
    protected function generateNamespace(){
        return !is_null($this->getNamespace()) ? 'namespace '.$this->getNamespace().";\n\n" : '';
    }

    /**
     * @return string
     */
    protected function generateUse(){
        $lines = array();
        foreach ($this->getUseClasses() as $className=>$as) {
            $lines[] = 'use ' . $className . (!is_null($as) ? ' as ' . $as : '').';';
        }
        return implode("\n", $lines);
    }

    protected function generateClassName(){
        return 'class '.$this->metadata->getName() .
        ($this->hasExtendsClass() ? ' extends '.$this->getExtendsClass() : '') .
        ($this->hasImplementsInterfaces() ? ' implements '.implode(', ', $this->getImplementsInterfaces()) : '');
    }

    /**
     * @return bool
     */
    public function hasExtendsClass(){
        return !empty($this->extendsClass);
    }

    /**
     * @return string
     */
    public function getExtendsClass(){
        return $this->extendsClass;
    }

    /**
     * @param string $extendsClass
     */
    public function setExtendsClass($extendsClass){
        $this->extendsClass = $extendsClass;
    }

    /**
     * @return bool
     */
    protected function hasImplementsInterfaces(){
        return !empty($this->implementsInterfaces);
    }

    /**
     * @return \string[]
     */
    public function getImplementsInterfaces(){
        return $this->implementsInterfaces;
    }

    /**
     * @param \string[] $implementsInterfaces
     */
    public function setImplementsInterfaces($implementsInterfaces){
        $this->implementsInterfaces = $implementsInterfaces;
    }

    /**
     * @param string $implementsInterface
     */
    public function addImplementsInterface($implementsInterface){
        $this->implementsInterfaces[] = $implementsInterface;
    }

    /**
     * @return boolean
     */
    public function isRegenerateExisting(){
        return $this->regenerateExisting;
    }

    /**
     * @param boolean $regenerateExisting
     */
    public function setRegenerateExisting($regenerateExisting){
        $this->regenerateExisting = $regenerateExisting;
    }


    /**
     * @param string $name
     * @param string $data
     * @return bool
     */
    protected function write($name, $data){
        $fileName = $name . '.php';
        $path = QS_path(array($this->filesDirectory, $fileName), false, false, false);
        if (!file_exists($path) || $this->isRegenerateExisting()){
            file_put_contents($path, $data);
            return true;
        }
        return false;
    }

    /**
     * @return EntityMetadata
     */
    public function getMetadata(){
        return $this->metadata;
    }

    /**
     * @param EntityMetadata $metadata
     */
    public function setMetadata($metadata){
        $this->metadata = $metadata;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function generateName($name){
        return $this->nameGenerator->generateName(array($name, PhpNameGenerator::CONV_METHOD_UNDERSCORE));
    }

    /**
     * @return string
     */
    public function getFilesDirectory(){
        return $this->filesDirectory;
    }

    /**
     * @param string $filesDirectory
     */
    public function setFilesDirectory($filesDirectory){
        $this->filesDirectory = $filesDirectory;
    }

    /**
     * @return string
     */
    public function getNamespace(){
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace){
        $this->namespace = $namespace;
    }

    /**
     * @return \string[]
     */
    public function getUseClasses(){
        return $this->useClasses;
    }

    /**
     * @param $useClasses
     */
    public function setUseClasses($useClasses){
        $this->useClasses = $useClasses;
    }

    /**
     * @param string $class
     * @param string|null $as
     */
    public function useClass($class, $as=null){
        $this->useClasses[$class] = $as;
    }

} 
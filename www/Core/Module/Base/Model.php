<?php
namespace Core\Module\Base;

abstract class Model {
    /**
     * @var Module
     */
    protected $_module;

    protected $_error;

    protected $_getFilters = array();

    protected $_columns = array();

    protected $_columnsMap = array();

    protected $_properties = array();

    const R_ONE_TO_ONE = 1;
    const R_ONE_TO_MANY = 2;
    const R_MANY_TO_MANY = 3;
    const R_MANY_TO_ONE = 4;

    const LEFT_JOIN = 5;
    const RIGHT_JOIN = 6;
    const INNER_JOIN = 7;

    const TYPE_BOOLEAN = 'bool';
    const TYPE_INT = 'Integer';
    const TYPE_TINYINT = 'tinyint';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_BIGINT = 'bigint';
    const TYPE_DOUBLE = 'double';
    const TYPE_FLOAT = 'float';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_REAL = 'real';
    const TYPE_STRING = 'varchar';



    protected $_relations = array();




    public function __construct(Module $module){
        $this->_module = $module;
    }
} 
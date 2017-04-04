<?php
namespace Core\DataBase;


use Core\Debugger;

class Statement extends Connection
{

    public function execute( $input_parameters = null){
        Debugger::log('Query: '.(is_null($input_parameters) ? $this->queryString : str_replace(array_keys($input_parameters), array_values($input_parameters), $this->queryString)));
        return parent::execute($input_parameters);
    }
} 
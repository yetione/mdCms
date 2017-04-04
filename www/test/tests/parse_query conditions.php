<?php
function parse($expr, $params=array()){
    $pattern = '/^((\w+)\.(\w+))\s([A-Za-z ]+)(?:\s(\?))?$/i';
    if (preg_match($pattern, $expr, $matches)){
        $entityName = $matches[2];
        $entityField = $matches[3];
        $operator = $matches[4];
        $p = $matches[5];

        var_dump($expr,$matches);
        return true;
    }
    return false;
}
parse('Country.City Like ?');
parse('Country.City Like ');
parse('Country.City Like');
parse('Country.City');
parse('');
parse(false);
parse(0);
parse('3.das Not like ?');
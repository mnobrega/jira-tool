<?php
/**
 * Created by PhpStorm.
 * User: mnobrega
 * Date: 21-01-2017
 * Time: 14:47
 */

function convertObjetct2Array($data)
{
    $ret = null;
    if(is_object($data)){
        foreach(get_object_vars($data) as $key=>$val){
            if(is_object($val)){
                $ret[convertCamelCase2camel_case($key)]=convertObjetct2Array($val);
            }else{
                $ret[convertCamelCase2camel_case($key)]=$val;
            }
        }
        return $ret;
    }elseif(is_array($data)){
        foreach($data as $key=>$val){
            if(is_object($val)){
                $ret[convertCamelCase2camel_case($key)]=convertObjetct2Array($val);
            }else{
                $ret[convertCamelCase2camel_case($key)]=$val;
            }
        }
        return $ret;
    }else{
        return $data;
    }
}

function convertCamelCase2camel_case($input) {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
        $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
}

function convertCamelCaseKeys2camel_case(Array $arr)
{
    $newArr = array();
    foreach ($arr as $key=>$value)
    {
        $newArr[convertCamelCase2camel_case($key)] = $value;
    }
    return $newArr;
}
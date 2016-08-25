<?php
/**
 * @author: dep
 * Date: 08.07.16
 */

namespace demmonico\helpers;


/**
 * Class for work with classes, functions etc. using Reflection
 */
class ReflectionHelper
{
    /**
     * Returns name of getter without 'get'
     * @param $getterName
     * @return string
     */
    public static function parseGetterName($getterName)
    {
        if (false !== $pos = strpos($getterName, 'get'))
            $getterName = substr($getterName, 3);
        return strtolower($getterName);
    }

    /**
     * Returns constant name cutting to first $separator
     * @param $constName
     * @param string $separator
     * @return string
     */
    public static function parseConstantName($constName, $separator='_')
    {
        if (!empty($separator)){
            $pos = strpos($constName, $separator);
            if (false !== $pos && $pos != strlen($constName))
                $constName = substr($constName, $pos+1);
        }
        $constName = str_replace($separator, ' ', $constName);
        return ucwords($constName);
    }



    /**
     * Returns array of class methods (can search by prefix)
     * @param $className
     * @param integer|null $filter  Filter the results to include only methods with certain attributes. Any combination of ReflectionMethod::IS_STATIC
     * @param string|null $prefix
     * @return array
     */
    public static function getMethods($className, $filter=null, $prefix=null)
    {
        $reflection = new \ReflectionClass($className);
        $arr = is_null($filter) ? $reflection->getMethods() : $reflection->getMethods($filter);

        $r = [];
        foreach($arr as $i){
            $name = $i->getName();
            if (is_null($prefix) || 0 === $pos=strpos($name, $prefix)){
                $r[] = $name;
            }
        }
        return $r;
    }

    /**
     * Returns array of class constants (can use name prefix)
     * @param $className
     * @param null $prefix
     * @return array
     */
    public static function getConstants($className, $prefix=null)
    {
        $reflection = new \ReflectionClass($className);
        $arr = $reflection->getConstants();

        $r = [];
        foreach($arr as $k=>$v){
            $k = strtolower($k);
            if (is_null($prefix) || 0 === $pos=strpos($k, $prefix)){
                $r[$v] = self::parseConstantName($k);
            }
        }
        return $r;
    }



    /**
     * Returns type of variable value (with/without allowable analyze)
     * @param $var
     * @param array $allowTypesArr
     * @param null $default
     * @return mixed|null|string
     */
    public static function detectVarType($var, $allowTypesArr=[], $default=null)
    {
        $type = gettype($var);
        if (!empty($allowTypesArr)){
            if (false !== $r = array_search(ucfirst($type), $allowTypesArr)) {
                return $r;
            } elseif(!is_null($default)) {
                return $default;
            }
        }
        return $type;
    }

}
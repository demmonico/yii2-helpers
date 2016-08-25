<?php
/**
 * @author: dep
 * Date: 08.07.16
 */

namespace demmonico\helpers;


/**
 * Class Multiton. Parent for create own simple multiton
 * @author: dep
 * @package demmonico\helpers
 */
abstract class Multiton
{
    private static $_instances = [];



    /**
     * limits create a new instance
     * @param null $data
     */
    final private function __construct($data=null)
    {
        $this->init($data);
    }

    /**
     * limits clone object
     */
    final private function __clone() {}

    /**
     * limits wakeup object
     */
    final private function __wakeup() {}



    /**
     * Interface provides init actions while initialization class instance
     * @param null $data
     */
    protected function init($data=null){}



    /**
     * Returns instance of called class
     * @param null $data array
     * @return mixed
     */
    final public static function getInstance($data=null)
    {
        $class = get_called_class();
        if (empty($class)) return null;

        if (empty($data)){
            $classID = $class;

        } elseif(is_array($data)){
            $classID = $class.md5(implode('', array_keys($data)));

        } else {
            $classID = $class.md5($data);
        }

        if (!isset(self::$_instances[$classID])){
            self::$_instances[$classID] = new $class($data);
        }

        return self::$_instances[$classID];
    }
}

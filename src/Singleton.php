<?php
/**
 * @author: dep
 * Date: 31.05.16
 */

namespace demmonico\helpers;


/**
 * Class Singleton. Parent for create own simple singleton
 * @author: dep
 * @package demmonico\helpers
 */
abstract class Singleton
{
    private static $_instance;



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
     * @return static
     */
    final public static function getInstance($data=null)
    {
        $class = get_called_class();
        if (empty($class)) return null;

        if (!isset(self::$_instance)){
            self::$_instance = new $class($data);
        }

        return self::$_instance;
    }
}

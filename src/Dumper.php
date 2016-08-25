<?php
/**
 * @author: dep
 * Date: 08.07.16
 */

namespace demmonico\helpers;

use yii\helpers\VarDumper;


/**
 * More effective dump
 * 
 * @use \Yii::$app->dump->stop($this->findCondition);
 */
class Dumper extends VarDumper
{
    public static function log($var, $depth=10, $highlight=true)
    {
        if ((php_sapi_name() == 'cli') || \Yii::$app->request->isAjax) {
            var_dump($var);
        } else {
            echo self::dumpAsString($var,$depth,$highlight).'<br>';
        }
        echo PHP_EOL;
    }

    public static function stop($var, $depth=10, $highlight=true)
    {
        self::log($var, $depth, $highlight); exit;
    }

    public static function arr()
    {
        $args = func_get_args();
        if (is_array($args) && !empty($args)){
            foreach ($args as $v){
                self::log($v, false);
            }
        }
    }
}
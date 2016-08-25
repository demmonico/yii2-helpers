<?php
/**
 * @author: dep
 * Date: 28.01.16
 */

namespace demmonico\helpers;

use yii\web\NotFoundHttpException;


/**
 * Class for work with URL and requests
 */
class RequestHelper
{
    /**
     * Get string with parsed route like "controller/action"
     * @return mixed
     * @throws NotFoundHttpException
     */
    public static function getRequestRoute()
    {
        $route = \Yii::$app->getRequest()->resolve();
        if (isset($route[0]) && !empty($route[0]))
            return $route[0];
        else
            throw new NotFoundHttpException(\Yii::t('yii', 'Page not found.'));
    }

    /**
     * Get controller from parsed string (first match in string like "controller/action")
     * @return string
     * @throws NotFoundHttpException
     */
    public static function getRequestController()
    {
        $r = explode('/', ltrim(self::getRequestRoute(), '/'));
        if (isset($r[0]) && !empty($r[0]))
            return $r[0];
        else
            throw new NotFoundHttpException(\Yii::t('yii', 'Page not found.'));
    }

    /**
     * Get action from parsed string (first match in string like "controller/action")
     * @return string|false
     */
    public static function getRequestAction()
    {
        $r = explode('/', ltrim(self::getRequestRoute(), '/'));
        return (isset($r[1]) && !empty($r[1])) ? $r[1] : false;
    }



    /**
     * Validates URL params and returns it (if valid) or returns remote address if no params
     * @param string $url
     * @return string
     */
    public static function getRequestClientIP($url='')
    {
        if (!$url || !is_string($url)){
            if (isset($_SERVER['REMOTE_ADDR']))
                $url = $_SERVER['REMOTE_ADDR'];
        }
        return (filter_var($url, FILTER_VALIDATE_IP)) ? $url : '';
    }

    /**
     * Check is request comes from localhost
     * @return bool
     */
    public static function isLocalRequest()
    {
        $url = self::getRequestClientIP(); // get remote address
        return $url && in_array($url, ['127.0.0.1','::1']);
    }

}
<?php
/**
 * @author: dep
 * Date: 08.07.16
 */

namespace demmonico\helpers;

use Faker\Provider\tr_TR\DateTime;


/**
 * Class for work with Date and Time
 */
class DateTimeHelper
{
    const DATE_FORMAT_DEFAULT       = 'Y-m-d H:i:s';
    const DATE_FORMAT_ADMIN         = 'M d, Y h:i:s A';
    const DATE_FORMAT_DEFAULT_NULL  = '0000-00-00 00:00:00';



    /**
     * Returns Datetime in depends on $timeInit and $timeZone
     * - current date/time in $timeZone
     * - predefined $timeInit date/time in $timeZone (default UTC)
     * @param null $timeInit
     * If $timeInit=null get current date/time at $timeZone or current timezone.
     * Current timezone must be set by function date_default_timezone_set('UTC') or set at config file (config timeZone)
     * @param string|int $timeZone
     * If $timeZone is string like 'UTC' - it use to create DateTime in $timeZone
     * If $timeZone is number - it use to correct time by client timezone (use like client time shift)
     * @return DateTime
     */
    public static function get($timeInit=null, $timeZone=null)
    {
        $timeInit = (is_null($timeInit)) ? 'now' : $timeInit;
        // if predefined $timeInit and $timeZone=null then get time at UTC
        $timeZone = (!is_null($timeInit) && $timeInit!='now' && is_null($timeZone)) ? 'UTC' : $timeZone;
        
        // get time object
        $time = new \DateTime($timeInit, (is_string($timeZone) && !is_numeric($timeZone)) ? new \DateTimeZone($timeZone) : null);
        if (!is_null($timeInit) && $timeInit!='now'){
            if ($timeInit === self::DATE_FORMAT_DEFAULT_NULL) $time->setTimestamp(0);   // FIX '0000-00-00 00:00:00' => '-0001-11-30 00:00:00'

            // correct time by user timezone
            if (!empty($timeZone)){
                $time_shift = (!is_numeric($timeZone)) ? $time->getOffset() : $timeZone;
            } else {
                $time_shift = \Yii::$app->getSession()->get('timezone');
            }
            if (!empty($time_shift))
                $time->modify($time_shift.' sec');
        }
        
        return $time;
    }

    /**
     * Returns Datetime at integer format
     * @param null $timeInit
     * @param string|int $timeZone
     * @see get
     * @return int
     */
    public static function getInt($timeInit=null, $timeZone=null)
    {
        $time = self::get($timeInit, $timeZone);
        return $time->getTimestamp();
    }

    /**
     * Returns Datetime at string format
     * @param null $timeInit
     * @param string|int $timeZone
     * @param string $format
     * @param bool $isAppendTimezone
     * @see get
     * @return int
     */
    public static function getFormat($timeInit=null, $timeZone=null, $format=self::DATE_FORMAT_DEFAULT, $isAppendTimezone=false)
    {
        $time = self::get($timeInit, $timeZone);
        $r = $time->format($format);

        // add timezone if possible
        if ($isAppendTimezone AND $r!=='' AND $r!==false){
            if (is_null($timeZone) && $time instanceof \DateTime && $tz=$time->getTimezone()){
                if ($tz = $tz->getName())
                    $r .= ' ('.$tz.')';
            } elseif ($timeZone){
                // $timeZone in number format (from session)
                if (is_numeric($timeZone)){
                    if ($tz = number_format($timeZone/3600, 1))
                        $r .= ' (UTC'.(ctype_digit($tz[0])?'+':'').$tz.')';
                    // $timeZone in 'UTC' (string) format
                } elseif (is_string($timeZone)){
                    $r .= ' ('.$timeZone.')';
                }
            }
        }

        return $r;
    }
    
    
    
    /**
     * Returns list of months
     * @param bool $isAddEmptyFirst
     * @return array
     */
    public static function listMonths($isAddEmptyFirst=false)
    {
        $r = $isAddEmptyFirst ? [''=>''] : [];
        for ($m=1; $m<13; $m++) {
            $r[$m] = date('F', mktime(0, 0, 0, $m, 1, date('Y')));
        }
        return $r;
    }

    /**
     * Returns list of years
     * @param array $yearRange [start, end] or [start]
     * @param bool $isAddEmptyFirst
     * @param string $order
     * @return array
     */
    public static function listYears(array $yearRange, $isAddEmptyFirst=false, $order='ASC')
    {
        $end = sizeof($yearRange)==2 ? $yearRange[1] : (int)date('Y');
        if ('ASC'==strtoupper($order)){
            $start = $yearRange[0];
        } else {
            $start = $end;
            $end = $yearRange[0];
        }
        $r = $isAddEmptyFirst ? [''=>''] : [];
        for ($y=$start; 'ASC'==strtoupper($order) ? $y<=$end : $y>=$end; 'ASC'==strtoupper($order) ? $y++ : $y--) {
            $r[$y] = $y;
        }
        return $r;
    }



    /**
     * Returns DateTime interval
     * @param string|null $from DateTime
     * @param string|null $to DateTime
     * @param string|null $format
     * @return string
     */
    public static function getDiff($from=null, $to=null, $format=null)
    {
        $from = self::get($from, null, 'obj');
        $to = self::get($to, null, 'obj');
        $diff = $from->diff($to);

        if (is_null($format))
            $format = "%a days %H:%i:%s";

        return $diff->format($format);
    }



// don't tested

    /**
     * Returns time period from DATE ($was - time at UTC timezone) to NOW as string
     * @param $was
     * @return string
     */
    public static function getTimeSince($was)
    {

        $callback_get_date = function ($date, $type, $lang)
        {
            $labels = array(
                'year' => array(
                    'en' => array('year', 'years'),
                    'ru' => array('год','лет','года'),
                ),
                'month' => array(
                    'en' => array('month', 'months'),
                    'ru' => array('месяц','месяцев','месяца'),
                ),
                'week' => array(
                    'en' => array('week', 'weeks'),
                    'ru' => array('неделю','недель','недели'),
                ),
                'day' => array(
                    'en' => array('day', 'days'),
                    'ru' => array('день','дней','дня'),
                ),
                'hour' => array(
                    'en' => array('hour', 'hours'),
                    'ru' => array('час','часов','часа'),
                ),
                'minute' => array(
                    'en' => array('minute','minutes'),
                    'ru' => array('минута','минут','минуты'),
                ),
                'second' => array(
                    'en' => array('second','seconds'),
                    'ru' => array('секунда','секунд','секунды'),
                ),
            );

            if ($lang=='en') {
                if(($date%10) == 1){
                    $r = $labels[$type][$lang][0];
                } else {
                    $r = $labels[$type][$lang][1];
                }
            } else {
                if((($date % 10) > 4 && ($date % 10) < 10) || ($date > 10 && $date < 20)) {
                    $r = $labels[$type][$lang][1];
                } elseif(($date % 10) > 1 && ($date % 10) < 5) {
                    $r = $labels[$type][$lang][2];
                } elseif(($date%10) == 1) {
                    $r = $labels[$type][$lang][0];
                } else {
                    $r = $labels[$type][$lang][1];
                }
            }

            return $r;
        };

        $callback_timespan = function ($seconds = 1, $time = '', $lang = 'en') use ($callback_get_date) {
            if ( !is_numeric($seconds) )
                $seconds = 1;
            if ( !is_numeric($time) )
                $time = self::getInt();
            $seconds = $time <= $seconds ? 1 : $time - $seconds;

            $str = array();
            $years = floor($seconds / 31536000);

            if ($years > 0) $str[] = $years.' '.call_user_func($callback_get_date, [$years, 'year', $lang]);

            $seconds -= $years * 31536000;
            $months = floor($seconds / 2628000);

            if ($years > 0 OR $months > 0) {
                if ($months > 0) $str[] = $months.' '.call_user_func($callback_get_date, [$months, 'month', $lang]);
                $seconds -= $months * 2628000;
            }

            $weeks = floor($seconds / 604800);

            if ($years > 0 OR $months > 0 OR $weeks > 0) {
                if ($weeks > 0) $str[] = $weeks.' '.call_user_func($callback_get_date, [$weeks, 'week', $lang]);
                $seconds -= $weeks * 604800;
            }

            $days = floor($seconds / 86400);

            if ($months > 0 OR $weeks > 0 OR $days > 0) {
                if ($days > 0) $str[] = $days.' '.call_user_func($callback_get_date, [$days, 'day', $lang]);
                $seconds -= $days * 86400;
            }

            $hours = floor($seconds / 3600);

            if ($days > 0 OR $hours > 0) {
                if ($hours > 0) $str[] = $hours.' '.call_user_func($callback_get_date, [$hours, 'hour', $lang]);
                $seconds -= $hours * 3600;
            }

            $minutes = floor($seconds / 60);

            if ($days > 0 OR $hours > 0 OR $minutes > 0) {
                if ($minutes > 0) $str[] = $minutes.' '.call_user_func($callback_get_date, [$minutes, 'minute', $lang]);
                $seconds -= $minutes * 60;
            }

            if ($str == '') $str[] = $seconds.' '.call_user_func($callback_get_date, [$seconds, 'second', $lang]);

            return $str;
        };

        $r = '';
        $end = ' ago';
        if (isset($was)){
            if (!is_int($was)) {        // if WAS in DateTime format
                $was = strtotime($was);
                //$was = self::getTrueTime($was, 'integer');    // this way $was get in client timezone whereas $now in UTC
            }
            $now = self::getInt();
            if ($was < $now) {
                $arr = call_user_func($callback_timespan, [$was, $now]);
                if ((isset($arr, $arr[0])) && (!empty($arr[0])) && (is_string($arr[0])) && ($arr[0]!=''))
                    return $arr[0] . $end;
            }
            $r = 'just now';
        }
        return $r;
    }

}
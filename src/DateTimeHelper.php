<?php

namespace demmonico\helpers;

use \DateTime;
use \DateTimeZone;


/**
 * Class for work with Date and Time
 *
 * @author: dep
 * Date: 08.07.16
 */
class DateTimeHelper
{
    const DATE_FORMAT_DEFAULT       = 'Y-m-d H:i:s';
    const DATE_FORMAT_ADMIN         = 'M d, Y h:i:s A';
    const DATE_FORMAT_DIFF          = '%a days %H:%i:%s';
    const DATE_FORMAT_DEFAULT_NULL  = '0000-00-00 00:00:00';



// input to server

    /**
     * Returns Datetime at Yii system timezone (or 'UTC') in depends on [$inputTime] and [$inputTimeZone]
     * - current date/time in [$inputTimeZone]
     * - predefined [$inputTime] date/time in [$inputTimeZone]
     * @param string|null $inputTime
     * If [$inputTime] == null - get current date/time at [$inputTimeZone].
     * @param string|int|float|null $inputTimeZone Current timezone or shift of time (in seconds) that will be added using [DateTime::modify()]
     * If [$inputTimeZone] is string like 'UTC' or 'GMT+3' - it use to create DateTime
     * If [$inputTimeZone] is number - it use to correcting time by needle time shift or client timezone (use like client time shift). Timezone use UTC
     * If [$inputTimeZone] is NULL the Yii system timezone [\Yii::$app->timeZone] will be used else 'UTC'
     * @return DateTime
     */
    public static function utc($inputTime=null, $inputTimeZone=null)
    {
        // detect timezone
        $timeZoneDefault = \Yii::$app->timeZone ?: 'UTC';
        $timeZone = isset($inputTimeZone) && is_string($inputTimeZone) && !is_numeric($inputTimeZone) ? $inputTimeZone : $timeZoneDefault;

        // create time object
        $time = new DateTime(isset($inputTime) ? $inputTime : 'now', new DateTimeZone($timeZone));

        // FIX '0000-00-00 00:00:00' => '-0001-11-30 00:00:00' problem
        if ($inputTime === self::DATE_FORMAT_DEFAULT_NULL)
            $time->setTimestamp(0);

        // correct time
        if (!empty($inputTimeZone)){

            // by time shift
            if (is_numeric($inputTimeZone))
                $time->modify((int)$inputTimeZone.' sec');

            // lead to UTC
            if (strcmp($timeZone, $timeZoneDefault) !== 0)
                $time->setTimezone(new DateTimeZone($timeZoneDefault));
        }

        return $time;
    }

    /**
     * Returns UTC Datetime at integer format
     * @param string|null $inputTime
     * @param string|int|float|null $inputTimeZone
     * @see utc
     * @return int
     */
    public static function utc2int($inputTime=null, $inputTimeZone=null)
    {
        $time = self::utc($inputTime, $inputTimeZone);
        return $time->getTimestamp();
    }

    /**
     * Returns UTC Datetime at string format
     * @param string|null $inputTime
     * @param string|int|float|null $inputTimeZone
     * @param string|null $format
     * @see utc
     * @return int
     */
    public static function utc2str($inputTime=null, $inputTimeZone=null, $format=self::DATE_FORMAT_DEFAULT)
    {
        $time = self::utc($inputTime, $inputTimeZone);
        return $time->format($format);
    }

// output to client

    /**
     * Returns client's Datetime in depends on [$inputTime] at [$inputTimeZone] or Yii system timezone (default 'UTC')
     * - current date/time in [$inputTimeZone]
     * - predefined [$inputTime] date/time in [$inputTimeZone]
     * @param string|null $inputTime
     * If [$inputTime] == null - get current date/time at [$inputTimeZone].
     * @param string|int|float|null $inputTimeZone Current timezone or shift of time (in seconds) that will be added using [DateTime::modify()]
     * If [$inputTimeZone] is string like 'UTC' or 'GMT+3' - it use to create DateTime
     * If [$inputTimeZone] is number - it use to correcting time by needle time shift or client timezone (use like client time shift). Timezone use UTC
     * If [$inputTimeZone] is NULL the Yii system timezone [\Yii::$app->timeZone] will be used else 'UTC'
     * @return DateTime
     */
    public static function get($inputTime=null, $inputTimeZone=null)
    {
        // create time object
        $time = new DateTime(isset($inputTime) ? $inputTime : 'now', new DateTimeZone(\Yii::$app->timeZone ?: 'UTC'));

        // FIX '0000-00-00 00:00:00' => '-0001-11-30 00:00:00' problem
        if ($inputTime === self::DATE_FORMAT_DEFAULT_NULL)
            $time->setTimestamp(0);

        // lead to user's timezone
        if (isset($inputTimeZone) && is_string($inputTimeZone) && !is_numeric($inputTimeZone)){
            $time->setTimezone(new DateTimeZone($inputTimeZone));
        }

        // correct time by user's time shift
        $timeShift = is_null($inputTimeZone) ? \Yii::$app->getSession()->get('timezone') : (is_numeric($inputTimeZone) ? $inputTimeZone : null);
        if (!empty($timeShift) && is_numeric($timeShift)){
            $time->modify((int)$timeShift.' sec');
        }

        return $time;
    }

    /**
     * Returns client's Datetime at integer format
     * @param string|null $inputTime
     * @param string|int|float|null $inputTimeZone
     * @see utc
     * @return int
     */
    public static function int($inputTime=null, $inputTimeZone=null)
    {
        $time = self::get($inputTime, $inputTimeZone);
        return $time->getTimestamp();
    }

    /**
     * Returns client's Datetime at string format
     * @param string|null $inputTime
     * @param string|int|float|null $inputTimeZone
     * @param string|null $format
     * @param bool $isAppendTimezone whether append timezone to output string
     * @see utc
     * @return int
     */
    public static function format($inputTime=null, $inputTimeZone=null, $format=self::DATE_FORMAT_DEFAULT, $isAppendTimezone=false)
    {
        $time = self::get($inputTime, $inputTimeZone);
        $r = $time->format($format);

        // add timezone if possible
        if ($isAppendTimezone AND $r!=='' AND $r!==false){
            if (is_null($inputTimeZone) && $tz=$time->getTimezone()){
                if ($tz = $tz->getName())
                    $r .= ' ('.$tz.')';
            } elseif ($inputTimeZone){
                // $inputTimeZone in number format (from session)
                if (is_numeric($inputTimeZone)){
                    if ($tz = number_format($inputTimeZone/3600, 1))
                        $r .= ' (UTC'.(ctype_digit($tz[0])?'+':'').$tz.')';
                }
                // $inputTimeZone in 'UTC' (string) format
                elseif (is_string($inputTimeZone)){
                    $r .= ' ('.$inputTimeZone.')';
                }
            }
        }

        return $r;
    }
    
    

// intervals

    /**
     * Returns DateTime interval (meaning timezone is equal - UTC)
     * @param string|null $from DateTime
     * @param string|null $to DateTime
     * @param string|null $format
     * @return string
     */
    public static function utcDiff($from=null, $to=null, $format=null)
    {
        $from = self::utc($from);
        $to = self::utc($to);
        $diff = $from->diff($to);
        return $diff->format(isset($format) ? $format : self::DATE_FORMAT_DIFF);
    }



// other

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
                $time = self::utc2int();
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
            $now = self::utc2int();
            if ($was < $now) {
                $arr = call_user_func($callback_timespan, [$was, $now]);
                if ((isset($arr, $arr[0])) && (!empty($arr[0])) && (is_string($arr[0])) && ($arr[0]!=''))
                    return $arr[0] . $end;
            }
            $r = 'just now';
        }
        return $r;
    }



// DEPRECATED

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
    public static function getOld($timeInit=null, $timeZone=null)
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

}



// for tests

//\Yii::$app->dump->log(DateTimeHelper::get());
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00'));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', 'GMT+3'));
//\Yii::$app->dump->log(DateTimeHelper::getOld('2016-10-06 07:52:00', 'GMT+3'));
//
//\Yii::$app->dump->log('----------------------------------------------------------------');
//
//\Yii::$app->dump->log($t = DateTimeHelper::get('2016-10-06 07:52:00', 'Europe/Kiev'));
//\Yii::$app->dump->log($t->setTimezone(new \DateTimeZone('UTC')));
//\Yii::$app->dump->log($t = DateTimeHelper::getOld('2016-10-06 07:52:00', 'Europe/Kiev'));
//\Yii::$app->dump->log($t->setTimezone(new \DateTimeZone('UTC')));
//\Yii::$app->dump->log($t = DateTimeHelper::get('2016-10-06 07:52:00', 'Europe/Paris'));
//\Yii::$app->dump->log($t->setTimezone(new \DateTimeZone('UTC')));
//\Yii::$app->dump->log($t = DateTimeHelper::getOld('2016-10-06 07:52:00', 'Europe/Paris'));
//\Yii::$app->dump->log($t->setTimezone(new \DateTimeZone('UTC')));
//
//\Yii::$app->dump->log('----------------------------------------------------------------');
//
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', 18000));   // +5 hours
//\Yii::$app->dump->log(DateTimeHelper::getOld('2016-10-06 07:52:00', 18000));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', 18000.0));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', +18000));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', +18000.0));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', '+18000'));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', '+18000.0'));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', -18000));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', -18000.0));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', '-18000'));
//\Yii::$app->dump->log(DateTimeHelper::get('2016-10-06 07:52:00', '-18000.0'));
//die;


//\Yii::$app->dump->log(DateTimeHelper::utc2str());
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00'));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 'GMT+3'));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 'Europe/Kiev'));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 18000));
//
//\Yii::$app->dump->log('----------------------------------------------------------------');
//
//\Yii::$app->dump->log(DateTimeHelper::utc2str(null, null, 'Y h'));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', null, 'Y h'));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 'GMT+3', 'Y h'));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 'Europe/Kiev', 'Y h'));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 18000, 'Y h'));
//
//\Yii::$app->dump->log('----------------------------------------------------------------');
//
//\Yii::$app->dump->log(DateTimeHelper::utc2str(null, null, 'Y', true));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', null, 'Y', true));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 'GMT+3', 'Y', true));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 'Europe/Kiev', 'Y', true));
//\Yii::$app->dump->log(DateTimeHelper::utc2str('2016-10-06 07:52:00', 18000, 'Y', true));

//\Yii::$app->dump->log(DateTimeHelper::utc2int());
//\Yii::$app->dump->log(DateTimeHelper::utc2int('2016-10-06 07:52:00'));
//\Yii::$app->dump->log(DateTimeHelper::utc2int('2016-10-06 07:52:00', 'GMT+3'));
//\Yii::$app->dump->log(DateTimeHelper::utc2int('2016-10-06 07:52:00', 'Europe/Kiev'));
//\Yii::$app->dump->log(DateTimeHelper::utc2int('2016-10-06 07:52:00', 18000));

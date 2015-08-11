<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 9/08/2015
 * Time: 19:03
 */

namespace Rath\Helpers;


class General
{
    const dateFormat = "Y-m-d";
    const timeFormat = "H:i:s";
    const dateTimeFormat = "Y-m-d H:i:s";

    /**
     * @return string
     */
    public static function getCurrentDate()
    {
        return date(self::dateFormat);
    }

    /**
     * @return string
     */
    public static function getCurrentTime()
    {
        return date(self::timeFormat);
    }

    public static function getCurrentDateTime(){
        return date(self::dateTimeFormat);
    }
}
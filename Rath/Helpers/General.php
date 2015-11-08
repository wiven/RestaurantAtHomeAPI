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
    const yearFormat = "Y";
    const timeFormat = "H:i:s";
    const dateTimeFormat = "Y-m-d H:i:s";

    /**
     * @return string
     */
    public static function getCurrentDate()
    {
        return date(self::dateFormat);
    }

    public static function getCurrentYear()
    {
        return date(self::yearFormat);
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

    /**
     * @return bool|int|string
     * DayOfWeek where 0 = Sunday.
     */
    public static function getCurrentDayOfWeek()
    {
        return date('w');
//        $day = $day -1;
//        if( $day == -1)
//            $day = 6;
//
//        return $day;
    }

    const booleanType = "boolean";
    const integerType = "integer";
    const doubleType = "double";
    const stringType = "string";
    const arrayType = "array";
    const objectType = "object";
    const resourceType = "resource";
    const nullType = "NULL";
    const unknownType = "unknown type";

    public static function getBaseUrl()
    {
        return "//".$_SERVER["HTTP_HOST"];
    }
}
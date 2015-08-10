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
    /**
     * @return string
     */
    public static function getCurrentDate()
    {
        $date = new \DateTime('now');
        return $date->format("Y-m-d");
    }

    /**
     * @return string
     */
    public static function getCurrentTime()
    {
        $time = new \DateTime('now');
        return $time->format("H:i:s");
    }
}
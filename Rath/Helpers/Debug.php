<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 02-Oct-15
 * Time: 11:00 AM
 */

namespace Rath\Helpers;


class Debug
{
    public static function varDump($object)
    {
        If(DEBUG)
            var_dump($object);
    }

    public static function writeEcho($text)
    {
        if(DEBUG)
            echo $text;
    }
}
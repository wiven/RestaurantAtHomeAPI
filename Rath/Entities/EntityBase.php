<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 16-Oct-15
 * Time: 07:53 PM
 */

namespace Rath\Entities;


use JsonMapper;
use Rath\Helpers\General;

abstract class EntityBase
{
    /**
     * @var JsonMapper
     */
    private static $jsonMapper;

    /**
     * @param $jsonString
     * @return object
     * @throws \JsonMapper_Exception
     */
    public static function fromJson($jsonString)
    {
        if(gettype($jsonString) == General::stringType)
            $jsonString = json_decode($jsonString);

        self::initMapper();
        $class = get_called_class();
        return self::$jsonMapper->map($jsonString,new $class);
    }

    private static function initMapper()
    {
        if(!isset(self::$jsonMapper))
            self::$jsonMapper = new JsonMapper();
    }
}
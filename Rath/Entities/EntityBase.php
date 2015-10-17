<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 16-Oct-15
 * Time: 07:53 PM
 */

namespace Rath\Entities;


use JsonMapper;
use Logger;
use Rath\Helpers\General;

abstract class EntityBase extends \stdClass
{
    /**
     * @var JsonMapper
     */
    private static $jsonMapper;

    /**
     * @var Logger
     */
    protected $log;

    public function __construct()
    {
//        $this->log = Logger::getLogger(get_called_class());
    }

    public function toJson()
    {
        $object = $this;
        unset($object->log);
        return json_encode($object);
    }

    /**
     * @param $jsonString string|array
     * @return object
     * @throws \JsonMapper_Exception
     */
    public static function fromJson($jsonString)
    {
        if(gettype($jsonString) == General::arrayType)
            $jsonString = json_encode($jsonString);

        if(gettype($jsonString) == General::stringType)
            $jsonString = json_decode($jsonString);

        self::initMapper();
        $class = get_called_class();
        return self::$jsonMapper->map($jsonString,new $class);
    }

    /**
     * @param $jsonString string|array
     * @return mixed
     */
    public static function fromJsonArray($jsonString)
    {
        if(gettype($jsonString) == General::arrayType)
            $jsonString = json_encode($jsonString);

        if(gettype($jsonString) == General::stringType)
            $jsonString = json_decode($jsonString);

        self::initMapper();
        $class = get_called_class();
        return self::$jsonMapper->mapArray($jsonString,[], $class);
    }

    private static function initMapper()
    {
        if(!isset(self::$jsonMapper))
            self::$jsonMapper = new JsonMapper();
    }
}
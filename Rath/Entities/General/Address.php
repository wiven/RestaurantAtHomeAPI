<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:25
 */

namespace Rath\Entities\General;


use Rath\Entities\EntityBase;

class Address extends EntityBase
{
    const TABLE_NAME = "address";

    const ID_COL = "id";
    const STREET_COL = "street";
    const NUMBER_COL = "number";
    const ADDITION_COL = "addition";
    const POSTCODE_COL = "postcode";
    const CITY_COL = "city";
    const USER_ID_COL = "userId";
    const LATITUDE_COL = "latitude";
    const LONGITUDE_COL = "longitude";
    const CITY_ID_COL = "cityid";

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $street;
    /**
     * @var int
     */
    public $number;
    /**
     * @var string
     */
    public $addition;
    /**
     * @var string
     */
    public $postcode;
    /**
     * @var string
     */
    public $city;
    /**
     * @var float
     */
    public $latitude;
    /**
     * @var float
     */
    public $longitude;
    /**
     * @var int
     */
    public $cityid;

    /**
     * @var int
     */
    public $userId;

    /**
     * @param $address Address
     * @return array
     */
    public static function toDbArray($address){
        $array = [
            Address::STREET_COL => $address->street,
            Address::NUMBER_COL => $address->number,
            Address::ADDITION_COL => $address->addition,
            Address::POSTCODE_COL => $address->postcode,
            Address::CITY_COL => $address->city,
            Address::LATITUDE_COL => $address->latitude,
            Address::LONGITUDE_COL => $address->longitude,
            self::CITY_ID_COL => $address->cityid
        ];
        if(!empty($address->userId))
            $array[Address::USER_ID_COL] = $address->userId;
        return $array;
    }


}
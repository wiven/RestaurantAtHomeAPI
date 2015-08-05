<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:25
 */

namespace Rath\Entities\General;


class Address
{
    const TABLE_NAME = "address";

    const ID_COL = "id";
    const STREET_COL = "street";
    const NUMBER_COL = "number";
    const ADDITION_COL = "addition";
    const POSTCODE_COL = "postcode";
    const CITY_COL = "city";
    const USER_ID_COL = "userId";

    public $id;
    public $street;
    public $number;
    public $addition;
    public $postcode;
    public $city;
    /**
     * @var int
     */
    public $userId;

    /**
     * @param $address Address
     */
    public static function toDbArray($address){
        $array = [
            Address::STREET_COL => $address->street,
            Address::NUMBER_COL => $address->number,
            Address::ADDITION_COL => $address->addition,
            Address::POSTCODE_COL => $address->postcode,
            Address::CITY_COL => $address->city
        ];
        if(!empty($address->userId))
            $array[Address::USER_ID_COL] = $address->userId;
        return $array;
    }


}
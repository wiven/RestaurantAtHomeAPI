<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 9/10/2015
 * Time: 7:42 PM
 */

namespace Rath\Controllers;


use Rath\Controllers\Data\ControllerBase;
use Rath\Controllers\Data\DataControllerFactory;
use Rath\Entities\AppMgt\City;
use Rath\Entities\AppMgt\DistanceMatrix;
use Rath\Entities\AppMgt\FilterField;
use Rath\Entities\DynamicClass;
use Rath\Entities\General\Address;
use Rath\Entities\Product\Product;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Promotion\PromotionType;
use Rath\Entities\Restaurant\KitchenType;
use Rath\Entities\Restaurant\OpeningHours;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Helpers\Debug;
use Rath\Helpers\General;
use Rath\helpers\MedooFactory;
use Rath\Helpers\PhotoManagement;

class SearchController extends ControllerBase
{
    /**
     * @param $skip
     * @param $top
     * @param $query
     * @return array|bool
     * @throws \Exception
     * <p>
     * Options: Open = true of false
     * </p>
     */
    public function searchProducts($skip, $top, $query)
    {
        $where = [];
        if(!empty($query)){
            $where = $this->getFilterFieldsToMedooWhereArray($query);
        }

        //Allow custom options to be passed
        $options = $this->getMedooWhereArrayOptions($where);


        if(isset($options["open"]))
            if($options["open"])
            {
                $where["AND"][OpeningHours::TABLE_NAME.".".OpeningHours::DAY_OF_WEEK_COL] = General::getCurrentDayOfWeek();
                $where["AND"][OpeningHours::TABLE_NAME.".".OpeningHours::FROM_TIME_COL."[<]"] = General::getCurrentTime();
                $where["AND"][OpeningHours::TABLE_NAME.".".OpeningHours::TO_TIME_COL."[>]"] = General::getCurrentTime();
            }

        // skip / top filters
        $where["LIMIT"] = [$skip,$top];

        $result =  $this->db->distinct()->select(Product::TABLE_NAME,
            [
                "[><]".Restaurant::TABLE_NAME =>[
                    Product::TABLE_NAME.".".Product::RESTAURANT_ID_COL => Restaurant::ID_COL
                ],
                "[><]".Address::TABLE_NAME => [
                    Restaurant::TABLE_NAME.".".Restaurant::ADDRESS_ID_COL => Address::ID_COL
                ],
                "[><]".KitchenType::TABLE_NAME => [
                    Restaurant::TABLE_NAME.".".Restaurant::KITCHEN_TYPE_ID_COL => KitchenType::ID_COL
                ],
                "[><]".OpeningHours::TABLE_NAME => [
                    Restaurant::TABLE_NAME.".".Restaurant::ID_COL => OpeningHours::RESTAURANT_ID_COL
                ],
                "[>]".Promotion::TABLE_NAME => [
                    Product::TABLE_NAME.".".Product::PROMOTION_ID_COL => Promotion::PROMOTION_TYPE_ID_COL
                ],
                "[>]".PromotionType::TABLE_NAME =>[
                    Promotion::TABLE_NAME.".".Promotion::PROMOTION_TYPE_ID_COL => PromotionType::ID_COL
                ],
                "[><]".City::TABLE_NAME => [
                    Address::TABLE_NAME.".".Address::CITY_ID_COL => City::ID_COL
                ],
                "[><]".DistanceMatrix::TABLE_NAME => [
                    City::TABLE_NAME.".".City::ID_COL => DistanceMatrix::TO_CITY_ID_COL
                ]
            ],
            [
                Product::TABLE_NAME.".".Product::ID_COL,
                Product::TABLE_NAME.".".Product::NAME_COL,
                Product::TABLE_NAME.".".Product::PRICE_COL,
                Product::TABLE_NAME.".".Product::DESCRIPTION_COL,
                Product::TABLE_NAME.".".Product::PHOTO_COL,
                PromotionType::TABLE_NAME.".".PromotionType::NAME_COL."(promotionTypeName)",

                Restaurant::TABLE_NAME.".".Restaurant::NAME_COL."(restaurantName)",
                KitchenType::TABLE_NAME.".".KitchenType::NAME_COL."(kitchenTypeName)",
                Address::TABLE_NAME.".".Address::STREET_COL,
                Address::TABLE_NAME.".".Address::NUMBER_COL,
                Address::TABLE_NAME.".".Address::ADDITION_COL,
                Address::TABLE_NAME.".".Address::CITY_COL,
                Address::TABLE_NAME.".".Address::LATITUDE_COL,
                Address::TABLE_NAME.".".Address::LONGITUDE_COL,
                DistanceMatrix::TABLE_NAME.".".DistanceMatrix::DISTANCE_COL,

                OpeningHours::TABLE_NAME.".".OpeningHours::FROM_TIME_COL,
                OpeningHours::TABLE_NAME.".".OpeningHours::TO_TIME_COL
            ],
            $where);

        if(!empty($result))
            $result = PhotoManagement::getPhotoUrlsForArray($result,Product::PHOTO_COL);

        $this->log->debug($this->db->last_query());

        return $result;
    }

    /**
     * @param $query
     * @return array
     * @throws \Exception
     */
    public function getFilterFieldsToMedooWhereArray($query)
    {
        $fc = DataControllerFactory::getFilterFieldController();
        $result = [];

        $parameters = explode("&",$query);
        foreach ($parameters as $para)
        {
            $this->log->debug("<br> ".$para);

            $keyValuePair = explode("=",$para);
            $field = $fc->get($keyValuePair[0]);
            $this->log->debug($field);
            $value = $keyValuePair[1];

            //Allow custom options to be passed
            if(gettype($field) != General::objectType){
                $result["options"][$field] = $value;
            }
            else {
                if (strpos($value, ",") !== false) {
                    $result[$field->databaseFieldname] = explode(",", $value);
                } elseif (strpos($value, "-") !== false) {
                    $range = explode("-", $value);
                    if (count($range) != 2)
                        throw new \Exception("Invalid Filter range submitted");

                    $result[$field->databaseFieldname . "[<>]"] = $range;
                } else {
                    $this->log->debug($value);
                    if ($field->like)
                        $key = $field->databaseFieldname . "[~]";
                    else
                        $key = $field->databaseFieldname;

                    $result[$key] = $value;
                }
            }
        }


        return
            [
                "AND" => $result
            ];
    }

    public function getMedooWhereArrayOptions(&$array)
    {
        $options = [];
        if(isset($array["AND"]["options"])){
            $options = $array["AND"]["options"];
            unset($array["AND"]["options"]);
        }
        return $options;
    }
}
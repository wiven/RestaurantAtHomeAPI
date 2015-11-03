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
use Rath\Entities\ApiResponse;
use Rath\Entities\AppMgt\City;
use Rath\Entities\AppMgt\DistanceMatrix;
use Rath\Entities\AppMgt\FilterField;
use Rath\Entities\General\Address;
use Rath\Entities\Product\Product;
use Rath\Entities\Product\ProductHasTags;
use Rath\Entities\Product\Tag;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Promotion\PromotionType;
use Rath\Entities\Restaurant\KitchenType;
use Rath\Entities\Restaurant\OpeningHours;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Entities\Restaurant\RestaurantHasSpeciality;
use Rath\Entities\Restaurant\Speciality;
use Rath\Entities\Search\SearchPromotionType;
use Rath\Entities\Search\SearchTag;
use Rath\Helpers\General;
use Rath\Helpers\PhotoManagement;

class SearchController extends ControllerBase
{

    //region Search Restaurants
    public function getSearchContent($skip, $top, $query)
    {
        /* @var $tagField FilterField
         * @var $filterFields FilterField[]
         */

        //Get query to And and used fields.
        $filterFields = [];
        $where = [];
        if(!empty($query))
        {
            $this->log->debug('Query -> Where + field extraction');
            $where = $this->getFilterFieldsToMedooWhereArray($query,$filterFields);
            $this->addDefaultPromotionFilters($where);

            $this->log->debug('Where');
            $this->log->debug($where);
            $this->log->debug('FilterFields');
            $this->log->debug($filterFields);
        }

        if(!$this->fromCityFilterApplied($response,$filterFields))
            return $response;

        $fromCity = $this->getFromCityInfo($where);

        $this->log->debug("Search resto Ids");
        $restoIdsResult = $this->searchRestaurants($this->getRestaurantIdFields(),$where);
        $this->log->debug("resto Ids result:");
        $this->log->debug($restoIdsResult);

        $this->log->debug("search restaurant results");
        // skip / top filters
        $where["LIMIT"] = [$skip,$top];
        $searchResult = $this->searchRestaurants($this->getRestaurantDefaultFields(), $where);
        $this->log->debug($searchResult);

        $restoIds = $this->restaurantIdResultToArray($restoIdsResult);
        if(count($restoIdsResult) != 0){
            $subQuery = $this->getQueryFilters($where,$filterFields);
            $tagUse = $this->searchUsedTags($restoIds,$subQuery);
            $promoTypeUse = $this->searchUsedPromotions($restoIds,$subQuery);
        }
        else
        {
            $this->log->debug("No restaurants within filter, so no tag or promotion usage within range.");
            $tagUse = $this->mergeTagUsage([]);
            $promoTypeUse = $this->mergePromotionUsage([]);
        }

        if(!empty($searchResult)) //do after array conversions
        {
            $this->addExtraRestaurantInfo($searchResult);
            $searchResult = PhotoManagement::getPhotoUrlsForArray($searchResult,Restaurant::LOGO_PHOTO_COL);
        }


        return[
            "fromCity" => $fromCity,
            "tagUse" => $tagUse,
            "promotionUse" => $promoTypeUse,
            "results" => $searchResult,
            "totalResults" => count($restoIdsResult)
        ];

    }

    /**
     * @param $response ApiResponse
     * @param $filterFields FilterField[]
     * @return bool
     */
    public function fromCityFilterApplied(&$response,$filterFields)
    {
        $this->log->debug('Check if fromCityId is defined as filterField');
        $this->log->debug($filterFields);
        $result = from($filterFields)
            ->singleOrDefault(null, function($field){
                if(isset($field->id))
                    return $field->id == FilterField::DISTANT_MTX_FROM_CITY_FIELD;
                return null;
            });
        if($result == null)
        {
            $this->log->debug("Filter not supplied");
            $response = new ApiResponse();
            $response->code = 406;
            $response->message = "The field '900' (fromCityId) has to be defined in the query.";
            return false;
        }
        $this->log->debug("Filter supplied.");
        return true;
    }

    //region Count Query Filtering
    /**
     * @param $where
     * @param $filterFields FilterField[]
     * @return null | array
     */
    public function getQueryFilters($where,$filterFields)
    {
        $query = null;
        $this->getTagQueryFilters($where,$filterFields,$query);
        $this->getPromotionQueryFilter($where,$filterFields,$query);

        $this->log->debug($query);
        return $query;
    }

    /**
     * @param $where
     * @param $filterFields FilterField[]
     * @param $query Array
     */
    public function getTagQueryFilters($where, $filterFields, &$query)
    {
        $this->log->debug('Getting Tag field');
        $tagField = from($filterFields)
            ->singleOrDefault(null,function($field){
                if(isset($field->id))
                    return $field->id == FilterField::TAG_ID_FIELD;
                return null;
            });
        $this->log->debug($tagField);

        $this->log->debug('Getting Tag Query');
        if($tagField != null)
            if(isset($where["AND"][$tagField->databaseFieldname]))
                $query[$tagField->databaseFieldname] =  $where["AND"][$tagField->databaseFieldname];
    }

    /**
     * @param $where
     * @param $filterFields FilterField[]
     * @param $query Array
     */
    public function getPromotionQueryFilter($where, $filterFields, &$query)
    {
        $this->log->debug('Getting promotion field');
        $promoField = from($filterFields)
            ->singleOrDefault(null,function($field){
                if(isset($field->id))
                    return $field->id == FilterField::PROMOTIONTYPE_ID_FIELD;
                return null;
            });
        $this->log->debug($promoField);

        $this->log->debug('Getting Promotion Query');
        if($promoField != null)
            if(isset($where["AND"][$promoField->databaseFieldname]))
                $query[$promoField->databaseFieldname] =  $where["AND"][$promoField->databaseFieldname];
    }
    //endregion

    /**
     * @param $where array
     */
    public function addDefaultPromotionFilters(&$where)
    {
        $date = General::getCurrentDate();
        $where["AND"]["OR #from"] = [
            "OR #fromIsData" => [
                Promotion::TABLE_NAME.".".Promotion::FROM_DATE_COL."[<=]" => $date
            ],
            "OR #fromIsNull" => [
                Promotion::TABLE_NAME.".".Promotion::FROM_DATE_COL => null
            ]
        ];
        $where["AND"]["OR #to"] = [
            "OR #fromIsData" => [
                Promotion::TABLE_NAME.".".Promotion::TO_DATE_COL."[>=]" => $date
            ],
            "OR #fromIsNull" => [
                Promotion::TABLE_NAME.".".Promotion::TO_DATE_COL => null
            ]
        ];
    }

    /**
     * @param $fields
     * @param $where array
     * @return array|bool
     */
    public function searchRestaurants($fields, $where)
    {
        //Allow custom options to be passed
        $options = $this->getMedooWhereArrayOptions($where);

        $this->log->debug("options:");
        $this->log->debug($options);
        if(isset($options["open"])){
            $this->log->debug("Open option available: ".(string)$options["open"]);
            if($options["open"] === 'true')
            {
                $this->log->debug("Adding 'open' filters");
                $where["AND"][OpeningHours::TABLE_NAME.".".OpeningHours::DAY_OF_WEEK_COL] = General::getCurrentDayOfWeek();
                $where["AND"][OpeningHours::TABLE_NAME.".".OpeningHours::FROM_TIME_COL."[<=]"] = General::getCurrentTime();
                $where["AND"][OpeningHours::TABLE_NAME.".".OpeningHours::TO_TIME_COL."[>=]"] = General::getCurrentTime();
            }
        }

        $result =  $this->db->distinct()->select(Restaurant::TABLE_NAME,
            [
                "[><]".Product::TABLE_NAME =>[
                    Restaurant::TABLE_NAME.".".Product::ID_COL => Product::RESTAURANT_ID_COL
                ],
                "[>]".ProductHasTags::TABLE_NAME => [
                    Product::TABLE_NAME.".".Product::ID_COL => ProductHasTags::PRODUCT_ID_COL
                ],
                "[>]".Tag::TABLE_NAME => [
                    ProductHasTags::TABLE_NAME.".".ProductHasTags::TAG_ID_COL => Tag::ID_COL
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
                    Product::TABLE_NAME.".".Product::PROMOTION_ID_COL => Promotion::ID_COL
                ],
                "[>]".PromotionType::TABLE_NAME =>[
                    Promotion::TABLE_NAME.".".Promotion::PROMOTION_TYPE_ID_COL => PromotionType::ID_COL
                ],
                "[>]".RestaurantHasSpeciality::TABLE_NAME =>[
                    Restaurant::TABLE_NAME.".".Restaurant::ID_COL => RestaurantHasSpeciality::RESTAURANT_ID_COL
                ],
                "[>]".Speciality::TABLE_NAME =>[
                    RestaurantHasSpeciality::TABLE_NAME.".".RestaurantHasSpeciality::SPECIALITY_ID_COL => Speciality::ID_COL
                ],
                "[><]".City::TABLE_NAME => [
                    Address::TABLE_NAME.".".Address::CITY_ID_COL => City::ID_COL
                ],
                "[><]".DistanceMatrix::TABLE_NAME => [
                    City::TABLE_NAME.".".City::ID_COL => DistanceMatrix::TO_CITY_ID_COL
                ]
            ],
            $fields,
            $where);

        $this->log->debug("Search restaurant restult Query info");
        $this->logLastQuery();
        $this->logMedooError();

        return $result;
    }

    /**
     * @param $restos array
     */
    public function addExtraRestaurantInfo(&$restos)
    {
        $this->log->debug("Start add extra info");
        $rc = DataControllerFactory::getRestaurantController();

        for($i = 0; $i < count($restos);$i++)
        {
            $restoId = $restos[$i][Restaurant::ID_COL];
            $restos[$i]["specialities"] = $rc->getRestaurantSpecialties($restoId);
            $restos[$i]["open"] = $rc->getIsOpen($restoId);
            $restos[$i]["hasPromotions"] = $rc->getHasPromotions($restoId);
        }
    }

    /**
     * @param $where
     * @return array|bool
     */
    public function getFromCityInfo($where)
    {
        if(isset($where["AND"][DistanceMatrix::TABLE_NAME.".".DistanceMatrix::FROM_CITY_ID_COL])){
            $gc = DataControllerFactory::getGeneralController();
            return $gc->getCity($where["AND"][DistanceMatrix::TABLE_NAME.".".DistanceMatrix::FROM_CITY_ID_COL]);
        }
        $this->log->debug("FromCity  value not found");
        $this->log->debug($where);
        return [];
    }

    public function getRestaurantDefaultFields()
    {
        return
            [
                Restaurant::TABLE_NAME.".".Restaurant::ID_COL,
                Restaurant::TABLE_NAME.".".Restaurant::NAME_COL,
                Restaurant::TABLE_NAME.".".Restaurant::LOGO_PHOTO_COL,
                Restaurant::TABLE_NAME.".".Restaurant::COMMENT_COL,
                KitchenType::TABLE_NAME.".".KitchenType::NAME_COL."(kitchenType)",

                Address::TABLE_NAME.".".Address::STREET_COL,
                Address::TABLE_NAME.".".Address::NUMBER_COL,
                Address::TABLE_NAME.".".Address::ADDITION_COL,
                Address::TABLE_NAME.".".Address::CITY_COL,
                Address::TABLE_NAME.".".Address::LATITUDE_COL,
                Address::TABLE_NAME.".".Address::LONGITUDE_COL,
                DistanceMatrix::TABLE_NAME.".".DistanceMatrix::DISTANCE_COL
            ];
    }

    public function getRestaurantIdFields()
    {
        return[
            Restaurant::TABLE_NAME.".".Restaurant::ID_COL,
        ];
    }

    /**
     * @param $resultRestoIds Array
     * @return array
     */
    public function restaurantIdResultToArray($resultRestoIds)
    {
        $this->log->debug("Convert restaurantIds to Id[]");
        $restos = Restaurant::fromJsonArray($resultRestoIds);
        $restoIdArray = [];
        foreach ($restos as $resto) {
            /* @var $resto Restaurant */
            array_push($restoIdArray,$resto->id);
        }
        $this->log->debug($restoIdArray);
        return $restoIdArray;
    }

    /**
     * @param $restoIds int[]
     * @param $query
     * @return array
     */
    //region Tag use
    public function searchUsedTags($restoIds,$query)
    {
        $this->log->debug("Search Used Tags");
        $tagUse = $this->composeTagUse($restoIds,$query);
        $allTags = $this->mergeTagUsage($tagUse);

        return $allTags;
    }


    /**
     * @param $restoIdArray Int[]
     * @param $query Array
     * @return array Tag[]
     */
    public function composeTagUse($restoIdArray,$query)
    {
        $this->log->debug('Get used tags');

        /* @var $usage Tag[] */
        $usage = [];

        $usedTags = $this->getUsedTags($restoIdArray,$query);
        if($usedTags != null){
            $tags = SearchTag::fromJsonArray($usedTags);

            $this->log->debug('Used tags:');
            $this->log->debug($tags);

            $usage = array_unique($tags); //get only unique values
            $usage = array_values($usage); //reset the index of the array (1,2,3,...)
            $this->log->debug('unique tags:');
            $this->log->debug(count($usage));

            for($i = 0; $i < count($usage); $i++)
            {
                $tagUse = $usage[$i];
                $count = from($tags)
                    ->where(function ($tag) use ($tagUse){
//                    $this->log->debug((string)($tag->id." = ".$tagUse->id));
                        return $tag->id == $tagUse->id;
                    })
                    ->distinct(function($tag){
                        return $tag->restoId;
                    })
                    ->count();
//                $this->log->debug($count);
                $usage[$i]->usage = $count;
            }
        }

//        $this->log->debug($restos);
//        $this->log->debug($restoIdArray);
//        $this->log->debug($tags);
//        $this->log->debug($usage);
        return $usage;

    }

    /**
     * @param $restoId int[]
     * @param null $tagQuery
     * @return array|bool
     */
    public function getUsedTags($restoId, $tagQuery = null)
    {
        if(count($restoId) == 0)
            return null;

        //TODO: filter promotions that are valid (period)
        $where = [];
        if($tagQuery == null)
            $where[Restaurant::TABLE_NAME.".".Restaurant::ID_COL] = $restoId;
        else
        {
            $where = [
                "AND" =>[
                    Restaurant::TABLE_NAME.".".Restaurant::ID_COL => $restoId,
                ]
            ];
            foreach($tagQuery as $key=>$value)
                $where["AND"][$key] = $value;
        }

        $this->log->debug("After where merge");
        $this->log->debug($where);

        $result = $this->db->select(Tag::TABLE_NAME,
            [
                "[><]".ProductHasTags::TABLE_NAME => [
                    Tag::TABLE_NAME.".".Tag::ID_COL => ProductHasTags::TAG_ID_COL
                ],
                "[><]".Product::TABLE_NAME => [
                    ProductHasTags::TABLE_NAME.".".ProductHasTags::PRODUCT_ID_COL => Product::ID_COL
                ],
                "[><]".Restaurant::TABLE_NAME => [
                    Product::TABLE_NAME.".".Product::RESTAURANT_ID_COL => Restaurant::ID_COL
                ],
                "[>]".Promotion::TABLE_NAME => [
                    Product::TABLE_NAME.".".Product::PROMOTION_ID_COL => Promotion::ID_COL
                ],
                "[>]".PromotionType::TABLE_NAME => [
                    Promotion::TABLE_NAME.".".Promotion::PROMOTION_TYPE_ID_COL => PromotionType::ID_COL
                ]
            ],
            [
                Tag::TABLE_NAME.".".Tag::ID_COL,
                Tag::TABLE_NAME.".".Tag::NAME_COL,
                Restaurant::TABLE_NAME.".".Restaurant::ID_COL."(restoId)"
            ],
            $where);

        return $result;
    }
    /**
     * @param $tagUse Tag[]
     * @return array
     */
    public function mergeTagUsage($tagUse)
    {
        $pc = DataControllerFactory::getProductController();
        $tags = Tag::fromJsonArray($pc->getAllTags());
        for($i = 0; $i < count($tags); $i++)
        {
            /* @var $tag Tag */
            $tag = $tags[$i];
            $tgu = from($tagUse)
                ->singleOrDefault(null,function($tg) use ($tag){
                    return $tg->id == $tag->id;
                });
            if($tgu != null)
                $tags[$i]->usage = $tgu->usage;
        }

        return from($tags)
            ->orderByDescending(function($tag){
                $tag->usage;
            })->toArray();
    }

    //endregion


    //region PromotionType Usage

    /**
     * @param $restoIds int[]
     * @param $query
     * @return array
     */
    public function searchUsedPromotions($restoIds,$query)
    {
        $this->log->debug("Search Used Promotions");
        $promoTypeUse =  $this->composePromtionUse($restoIds,$query);
        $allPromoTypes  = $this->mergePromotionUsage($promoTypeUse);

        return $allPromoTypes;
    }

    /**
     * @param $restoIdArray Int[]
     * @param $query Array
     * @return array Tag[]
     */
    public function composePromtionUse($restoIdArray,$query)
    {
        $this->log->debug('Get used tags');

        /* @var $usage Promotion[] */
        $usage = [];

        $usedPromos = $this->getUsedPromotions($restoIdArray,$query);
        if($usedPromos != null){
            $promos = SearchPromotionType::fromJsonArray($usedPromos);

            $this->log->debug('used promotions:');
            $this->log->debug($promos);

            $usage = array_unique($promos); //get only unique values
            $usage = array_values($usage); //reset the index of the array (1,2,3,...)

            $this->log->debug('unique promotions:');
            $this->log->debug(count($usage));

            for($i = 0; $i < count($usage); $i++)
            {
                $promoUse = $usage[$i];
                $count = from($promos)
                    ->where(function ($promo) use ($promoUse){
//                    $this->log->debug((string)($tag->id." = ".$tagUse->id));
                        return $promo->id == $promoUse->id;
                    })
                    ->distinct(function($tag){
                        return $tag->restoId;
                    })
                    ->count();
//                $this->log->debug($count);
                $usage[$i]->usage = $count;
            }
        }
        return $usage;

    }

    /**
     * @param $restoId int[]
     * @param null $query
     * @return array|bool
     */
    public function getUsedPromotions($restoId, $query = null)
    {
        if(count($restoId) == 0)
            return null;

        $where = [];
        if($query == null)
            $where[Restaurant::TABLE_NAME.".".Restaurant::ID_COL] = $restoId;
        else
        {
            $where = [
                "AND" =>[
                    Restaurant::TABLE_NAME.".".Restaurant::ID_COL => $restoId,
                ]
            ];
            foreach($query as $key=>$value)
                $where["AND"][$key] = $value;
        }

        $this->log->debug("After where merge");
        $this->log->debug($where);

        $result = $this->db->select(PromotionType::TABLE_NAME,
            [
                "[><]".Promotion::TABLE_NAME => [
                    PromotionType::TABLE_NAME.".".PromotionType::ID_COL => Promotion::PROMOTION_TYPE_ID_COL
                ],
                "[><]".Product::TABLE_NAME => [
                    Promotion::TABLE_NAME.".".Promotion::ID_COL => Product::PROMOTION_ID_COL
                ],
                "[>]".ProductHasTags::TABLE_NAME => [
                    Product::TABLE_NAME.".".Product::ID_COL => ProductHasTags::PRODUCT_ID_COL
                ],
                "[>]".Tag::TABLE_NAME => [
                    ProductHasTags::TABLE_NAME.".".ProductHasTags::TAG_ID_COL => Tag::ID_COL
                ],
                "[><]".Restaurant::TABLE_NAME => [
                    Product::TABLE_NAME.".".Product::RESTAURANT_ID_COL => Restaurant::ID_COL
                ]
            ],
            [
                PromotionType::TABLE_NAME.".".PromotionType::ID_COL,
                PromotionType::TABLE_NAME.".".PromotionType::NAME_COL,
                Restaurant::TABLE_NAME.".".Restaurant::ID_COL."(restoId)"
            ],
            $where);

        return $result;
    }

    /**
     * @param $promoUse PromotionType[]
     * @return array
     */
    public function mergePromotionUsage($promoUse)
    {
        $pc = DataControllerFactory::getPromotionController();
        $promoTypes = Tag::fromJsonArray($pc->getAllpromotionTypes());
        for($i = 0; $i < count($promoTypes); $i++)
        {
            /* @var $promo Tag */
            $promo = $promoTypes[$i];
            $pmu = from($promoUse)
                ->singleOrDefault(null,function($tg) use ($promo){
                    return $tg->id == $promo->id;
                });
            if($pmu != null)
                $promoTypes[$i]->usage = $pmu->usage;
        }

        return from($promoTypes)
            ->orderByDescending(function($tag){
                $tag->usage;
            })->toArray();
    }
    //endregion
    //endregion


    //region Query Parsing

    /**
     * @param $query string
     * @param $fields FilterField[]
     * @param bool $storeFields
     * @return array
     * @throws \Exception
     */
    public function getFilterFieldsToMedooWhereArray($query,&$fields,$storeFields = true)
    {
        /* @var $field Filterfield|string */
        $fc = DataControllerFactory::getFilterFieldController();
        $result = [];

        $parameters = explode("&",$query);
        foreach ($parameters as $para)
        {
            $this->log->debug($para);

            $keyValuePair = explode("=",$para);
            if(isset($keyValuePair[0]) && isset($keyValuePair[1])) //ensure a value pair exists
            {
                $field = $fc->get($keyValuePair[0]);
                $this->log->debug($field);
                $value = $keyValuePair[1];

                //Store fields
                if($storeFields)
                    array_push($fields,$field);

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
//                        $this->log->debug($value);
                        if ($field->like)
                            $key = $field->databaseFieldname . "[~]";
                        else
                            $key = $field->databaseFieldname;

                        $result[$key] = $value;
                    }
                }
            }
        }

//        $this->log->debug($result);
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
    //endregion


    //region Product  Search functions
    /**
     * @param $skip
     * @param $top
     * @param $query
     * @return array|bool
     * @throws \Exception
     * <p>
     * Options: Open = true of false
     * </p>
     * @deprecated
     */
    public function searchProducts($skip, $top, $query)
    {
        $where = [];
//        if(!empty($query)){
//            $where = $this->getFilterFieldsToMedooWhereArray($query);
//        }

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


    //endregion
}
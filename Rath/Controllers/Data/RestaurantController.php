<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:40
 */

namespace Rath\Controllers\Data;

use PDO;
use Rath\Controllers\ControllerFactory;
use Rath\Controllers\Data\ControllerBase;
use Rath\Entities\General\Address;
use Rath\Entities\Order\Order;
use Rath\Entities\Order\OrderDetail;
use Rath\Entities\Order\OrderStatus;
use Rath\Entities\Product\Product;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Promotion\PromotionType;
use Rath\Entities\Restaurant\Holiday;
use Rath\Entities\Restaurant\KitchenType;
use Rath\Entities\Restaurant\OpeningHours;
use Rath\Entities\Restaurant\PaymentMethod;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Entities\Restaurant\RestaurantHasPaymentMethod;
use Rath\Entities\Restaurant\RestaurantHasSpeciality;
use Rath\Entities\Restaurant\RestaurantPhoto;
use Rath\Entities\Restaurant\RestaurantSocialMedia;
use Rath\Entities\Restaurant\Speciality;
use Rath\Entities\User\User;
use Rath\Helpers\General;
use Rath\Slim\Middleware\Authorization;

class RestaurantController extends ControllerBase
{
    //region Restaurant

    /**
     * @param $id
     * @return array|bool
     */
    public function getRestaurant($id){
        return $this->db->get(Restaurant::TABLE_NAME,
            [
                Restaurant::ID_COL,
                Restaurant::NAME_COL,
                Restaurant::KITCHEN_TYPE_ID_COL,
                Restaurant::ADDRESS_ID_COL,
                Restaurant::LOGO_PHOTO_COL,
                Restaurant::PHONE_COL,
                Restaurant::EMAIL_COL,
                Restaurant::URL_COL,
                Restaurant::LOGO_PHOTO_COL,
                Restaurant::DOMINATING_COLOR_COL,
                Restaurant::COMMENT_COL
            ],
            [
                Restaurant::ID_COL => $id
            ]);
    }

    /**
     * @param $resto Restaurant
     * @return array|bool
     */
    public function addRestaurant($resto){
        $resto->userId = Authorization::$userId;
        $lastId = $this->db->insert(Restaurant::TABLE_NAME,
            Restaurant::restaurantToDbArray($resto)
        );
        if($lastId != 0)
            return $this->getRestaurant($lastId);
        else
            return $this->db->error();

    }

    /**
     * @param $resto Restaurant
     * @return array
     */
    public function updateRestaurant($resto){
        $this->db->update(Restaurant::TABLE_NAME,
            Restaurant::restaurantToDbArray($resto),
            [
                "AND" => [
                    Restaurant::ID_COL => $resto->id,
//                    Restaurant::USER_ID_COL =>  Authorization::$userId
                ]
            ]);
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteRestaurant($id){
        $this->db->delete(Restaurant::TABLE_NAME,
            [
                "AND" => [
                    Restaurant::ID_COL => $id,
//                    Restaurant::USER_ID_COL =>  Authorization::$userId
                ]
            ]);
        return $this->db->error();
    }
    //endregion

    //region KitchenType

    /**
     * @param $id
     * @return array|bool
     */
    public function getKitchenType($id){
        $result = $this->db->select(KitchenType::TABLE_NAME,
            [
                KitchenType::ID_COL,
                KitchenType::NAME_COL
            ],
            [
                KitchenType::ID_COL => $id
            ]);
        return $result;
    }

    /**
     * @param $kitchenType KitchenType
     * @return array
     */
    public function addKitchenType($kitchenType){
        $this->db->insert(KitchenType::TABLE_NAME,
            [
                KitchenType::ID_COL => $kitchenType->id,
                KitchenType::NAME_COL => $kitchenType->name
            ]);
        return $this->db->error();
    }

    /**
     * @param $kitchenType KitchenType
     * @return array
     */
    public function updateKitchenType($kitchenType){
        $this->db->update(KitchenType::TABLE_NAME,
            [
                KitchenType::NAME_COL => $kitchenType->name
            ],
            [
                KitchenType::ID_COL => $kitchenType->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteKitchenType($id){
        $this->db->delete(KitchenType::TABLE_NAME,
            [
                KitchenType::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region Holiday
    /**
     * @param $id
     * @return array|bool
     */
    public function getHoliday($id){
        return $this->db->select(Holiday::TABLE_NAME,
            "*",
            [
                Holiday::ID_COL => $id
            ]);
    }

    /**
     * @param $restoId
     * @return array|bool
     */
    public function getHolidays($restoId){
        return $this->db->select(Holiday::TABLE_NAME,
            "*",
            [
                Holiday::RESTAURANT_ID_COL => $restoId
            ]);
    }

    /**
     * @param $holiday Holiday
     * @return array|bool
     */
    public function addHoliday($holiday){
        $lastId = $this->db->insert(Holiday::TABLE_NAME,
            Holiday::holidayToDbArray($holiday)
        );
        if($lastId != 0)
            return $this->getHoliday($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $holiday Holiday
     * @return array
     */
    public function updateHoliday($holiday){
        $this->db->update(Holiday::TABLE_NAME,
            Holiday::holidayToDbArray($holiday),
            [
                Holiday::ID_COL => $holiday->id
            ]
        );
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteHoliday($id){
        $this->db->delete(Holiday::TABLE_NAME,
            [
                Holiday::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region OpeningHours
    public function getOpeningHour($id){
        return $this->db->select(OpeningHours::TABLE_NAME,
            "*",
            [
                OpeningHours::ID_COL => $id
            ]);
    }

    /**
     * @param $restoId
     * @return array|bool
     */
    public function getOpeningHours($restoId){
        return $this->db->select(OpeningHours::TABLE_NAME,
            "*",
            [
                OpeningHours::RESTAURANT_ID_COL => $restoId
            ]);
    }

    public function addOpeningHour($oh){
        $lastId = $this->db->insert(OpeningHours::TABLE_NAME,
            OpeningHours::toDbArray($oh)
        );
        if($lastId != 0)
            return $this->getOpeningHour($lastId);
        else
            return $this->db->error();
    }

    public function updateOpeningHour($oH){
        $this->db->update(OpeningHours::TABLE_NAME,
            OpeningHours::toDbArray($oH),
            [
                OpeningHours::ID_COL => $oH->id
            ]
        );
        return $this->db->error();
    }

    public function deleteOpeningHour($id){
        $this->db->delete(OpeningHours::TABLE_NAME,
            [
                OpeningHours::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region Restaurant PaymentMethod

    public function getRestaurantPaymentMethods($restoId){
        return $this->db->select(RestaurantHasPaymentMethod::TABLE_NAME,
            [
             "[><]".PaymentMethod::TABLE_NAME =>
                 [
                     RestaurantHasPaymentMethod::PAYMENT_METHOD_ID_COL => PaymentMethod::ID_COL
                 ]
            ],
            [
                PaymentMethod::ID_COL,
                PaymentMethod::NAME_COL
            ],
            [
                RestaurantHasPaymentMethod::RESTAURANT_ID_COL => $restoId
            ]);
    }

    public function addRestaurantPaymentMethod($restoId,$payMeth){
        $this->db->insert(RestaurantHasPaymentMethod::TABLE_NAME,
            [
                RestaurantHasPaymentMethod::RESTAURANT_ID_COL => $restoId,
                RestaurantHasPaymentMethod::PAYMENT_METHOD_ID_COL => $payMeth
            ]
        );
        return $this->db->error();
    }

    public function deleteRestaurantPaymentMethod($restoId,$payMeth){
        $this->db->delete(RestaurantHasPaymentMethod::TABLE_NAME,
            [
                "AND" => [
                    RestaurantHasPaymentMethod::RESTAURANT_ID_COL => $restoId,
                    RestaurantHasPaymentMethod::PAYMENT_METHOD_ID_COL => $payMeth
                ]
            ]);
        return $this->db->error();
    }
    //endregion

    //region Restaurant Speciality

    public function getRestaurantSpecialities($restoId){
        return $this->db->select(RestaurantHasSpeciality::TABLE_NAME,
            [
                "[><]".Speciality::TABLE_NAME =>
                    [
                        RestaurantHasSpeciality::SPECIALITY_ID_COL => Speciality::ID_COL
                    ]
            ],
            [
                Speciality::ID_COL,
                Speciality::NAME_COL
            ],
            [
                RestaurantHasSpeciality::RESTAURANT_ID_COL => $restoId
            ]);
    }

    public function getAllSpecialities(){
        return $this->db->select(Speciality::TABLE_NAME,"*");
    }

    public function addRestaurantSpeciality($restoId,$specId){
        $this->db->insert(RestaurantHasSpeciality::TABLE_NAME,
            [
                RestaurantHasSpeciality::RESTAURANT_ID_COL => $restoId,
                RestaurantHasSpeciality::SPECIALITY_ID_COL => $specId
            ]
        );
        return $this->db->error();
    }

    /**
     * @param $restoId
     * @param $specName
     * @return array
     */
    public function addNewRestaurantSpeciality($restoId,$specName){
        $lastId = $this->db->insert(Speciality::TABLE_NAME,
            [
                Speciality::NAME_COL => $specName
            ]);
        if($lastId != 0)
            return $this->addRestaurantSpeciality($restoId,$lastId);
        else
            return $this->db->error();
    }

    public function deleteRestaurantSpeciality($restoId,$specId){
        $this->db->delete(RestaurantHasSpeciality::TABLE_NAME,
            [
                "AND" => [
                    RestaurantHasSpeciality::RESTAURANT_ID_COL => $restoId,
                    RestaurantHasSpeciality::SPECIALITY_ID_COL => $specId
                ]
            ]);
        return $this->db->error();
    }
    //endregion

    //region Promotions
    public function getActivePromotions($restoId, $skip,$top){
        /** @noinspection SqlDialectInspection */
        $date = $this->db->quote(General::getCurrentDate());
        $query =
            "SELECT promotion.id,promotion.name,toDate,fromDate, (select sum(quantity) from promotionusagehistory".
            " WHERE promotionId = promotion.id) as 'usage' FROM ".Promotion::TABLE_NAME.
            " INNER JOIN promotiontype ON promotion.promotiontypeId = promotiontype.id".
            ' WHERE restaurantId = '.$this->db->quote($restoId).
            ' AND fromDate <= '.$date.
            ' AND toDate >= '.$date.
            'LIMIT '.$skip.",".$top;
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommingPromotions($restoId, $skip,$top){
        /** @noinspection SqlDialectInspection */
        $date = $this->db->quote(General::getCurrentDate());
        $query =
            "SELECT promotion.id,promotion.name,toDate,fromDate, (select sum(quantity) from promotionusagehistory".
            " WHERE promotionId = promotion.id) as 'usage' FROM ".Promotion::TABLE_NAME.
            " INNER JOIN promotiontype ON promotion.promotiontypeId = promotiontype.id".
            ' WHERE restaurantId = '.$this->db->quote($restoId).
            ' AND fromDate >= '.$date.
            'LIMIT '.$skip.",".$top;
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPassedPromotions($restoId, $skip,$top){
        /** @noinspection SqlDialectInspection */
        $date = $this->db->quote(General::getCurrentDate());
        $query =
            "SELECT promotion.id,promotion.name,toDate,fromDate, (select sum(quantity) from promotionusagehistory".
            " WHERE promotionId = promotion.id) as 'usage' FROM ".Promotion::TABLE_NAME.
            " INNER JOIN promotiontype ON promotion.promotiontypeId = promotiontype.id".
            ' WHERE restaurantId = '.$this->db->quote($restoId).
            ' AND toDate <= '.$date.
            'LIMIT '.$skip.",".$top;
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPromotions($restoId,$count, $skip)
    {
        return $this->db->select(Promotion::TABLE_NAME,
            [
                "[><]".PromotionType::TABLE_NAME =>
                    [
                        Promotion::PROMOTION_TYPE_ID_COL => PromotionType::ID_COL
                    ]
            ],
            [
                Promotion::ID_COL,
                PromotionType::NAME_COL,
                Promotion::TO_DATE_COL
            ],
            [
                Promotion::RESTAURANT_ID_COL => $restoId,
                "LIMIT" => [$count,$skip]
            ]);
    }
    //endregion

    //region Orders
    /**
     * @param $restoId int
     * @return int
     */
    public function getNewOrderCount($restoId)
    {
        return $this->db->count(Order::TABLE_NAME,
            [
                Order::ID_COL
            ],
            [
                "AND"=>[
                    Order::RESTAURANT_ID_COL => $restoId,
                    Order::ORDER_STATUS_ID_COL =>OrderStatus::val_New,
                    Order::SUBMITTED_COL => true
                ]
            ]);
    }

    /**
     * @param $restoId
     * @return array|bool
     */
    public function getOrders($restoId,$statusStart,$statusEnd,$skip,$top,$filterToday = true){
        $dayStart = $this->db->quote(date(General::dateTimeFormat,mktime(0,0,0)));
        $dayEnd = $this->db->quote(date(General::dateTimeFormat,mktime(23,59,59)));
        $query =
            "SELECT o.id, name, surname, orderDateTime,amount,orderStatusId,
            (SELECT sum(quantity) from ".OrderDetail::TABLE_NAME." where orderId = o.id) as 'items',
            (select count(slots) from ".OrderDetail::TABLE_NAME." as od
              INNER join product on od.productId = product.id
              where od.OrderId = o.id) as 'slots'
            FROM ".$this->db->database_name.".".Order::TABLE_NAME." as o
            INNER JOIN user ON o.userId = user.id WHERE
            restaurantId = ".$this->db->quote($restoId)."
            AND (orderStatusId BETWEEN ".$this->db->quote($statusStart)." AND ".$this->db->quote($statusEnd).")";
        if($filterToday)
            $query .= "AND (orderDateTime BETWEEN ".$dayStart." AND ".$dayEnd.")";
        $query .= "AND submitted = 1
            ORDER BY orderDateTime ASC
            LIMIT ".$skip.",".$top;

        $pdoQuery = $this->db->query($query);
        //var_dump($pdoQuery);
        //var_dump($this->db->error());
        //if(!$pdoQuery)
        return $pdoQuery->fetchAll(PDO::FETCH_ASSOC);
        //return $this->db->error();
    }
    //endregion

    //region Photo TODO: add function to actual upload the photo
    /**
     * @param $restoPhoto RestaurantPhoto
     * @return array
     */
    public function addPhoto($restoPhoto){
        $this->db->insert(RestaurantPhoto::TABLE_NAME,
            RestaurantPhoto::toDbArray($restoPhoto)
        );
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function getPhoto($id){
        return $this->db->select(RestaurantPhoto::TABLE_NAME,
            "*",
            [
                RestaurantPhoto::ID_COL => $id
            ]);
    }

    /**
     * @param $restoId
     * @return array
     */
    public function getPhotos($restoId)
    {
        return  $this->db->select(RestaurantPhoto::TABLE_NAME,
            "*",
            [
                RestaurantPhoto::RESTAURANT_ID_COL => $restoId,
                "LIMIT" => [0,10]
            ]);
    }

    /**
     * @param $restoPhoto RestaurantPhoto
     * @return array
     */
    public function updatePhoto($restoPhoto){
        $this->db->update(RestaurantPhoto::TABLE_NAME,
            RestaurantPhoto::toDbArray($restoPhoto),
            [
                RestaurantPhoto::ID_COL => $restoPhoto->id
            ]
        );
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deletePhoto($id){
        $this->db->delete(RestaurantPhoto::TABLE_NAME,
            [
                RestaurantPhoto::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region PaymentMethod (App management)
    //TODO: move to App mgt
    /**
     * @param $payMeth PaymentMethod
     * @comments Has no auto increment set
     * @return array
     */
    public function addPaymentMethod($payMeth){
        $this->db->insert(PaymentMethod::TABLE_NAME,
            PaymentMethod::toDbArray($payMeth)
        );
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function getPaymentMethod($id){
        return $this->db->select(PaymentMethod::TABLE_NAME,
            "*",
            [
                PaymentMethod::ID_COL => $id
            ]);
    }

    /**
     * @param $payMeth PaymentMethod
     * @return array
     */
    public function updatePaymentMethod($payMeth){
        $this->db->update(PaymentMethod::TABLE_NAME,
            PaymentMethod::toDbArray($payMeth),
            [
                PaymentMethod::ID_COL => $payMeth->id
            ]
        );
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deletePaymentMethod($id){
        $this->db->delete(PaymentMethod::TABLE_NAME,
            [
                PaymentMethod::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion //TODO

    //region Address
    /**
     * @param $restoAddressId
     * @return array|bool
     */
    public function getAddress($restoAddressId)
    {
        return $this->db->get(Address::TABLE_NAME,
            [
                Address::ID_COL,
                Address::STREET_COL,
                Address::NUMBER_COL,
                Address::ADDITION_COL,
                Address::POSTCODE_COL,
                Address::CITY_COL
            ],
            [
                Address::ID_COL => $restoAddressId
            ]);
    }
    //endregion

    //region Social Media

    /**
     * @param $part RestaurantSocialMedia
     * @return array|bool
     */
    public function addSocialMedia($part)
    {
        $lastId = $this->db->insert(RestaurantSocialMedia::TABLE_NAME,
            RestaurantSocialMedia::toDbArray($part));
        if($lastId != 0)
            return $this->getSocialMedia($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function getSocialMedia($id)
    {
        return $this->db->select(RestaurantSocialMedia::TABLE_NAME,
            "*",
            [
                RestaurantSocialMedia::ID_COL => $id
            ]);
    }

    public function getAllSocialMedia($restoId)
    {
        return $this->db->select(RestaurantSocialMedia::TABLE_NAME,
            "*",
            [
                RestaurantSocialMedia::RESTAURANT_ID_COL => $restoId
            ]);
    }

    /**
     * @param $part RestaurantSocialMedia
     * @return array
     */
    public function updateSocialMedia($part)
    {
        $this->db->update(RestaurantSocialMedia::TABLE_NAME,
            RestaurantSocialMedia::toDbArray($part),
            [
                RestaurantSocialMedia::ID_COL => $part->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteSocialMedia($id)
    {
        $this->db->delete(RestaurantSocialMedia::TABLE_NAME,
            [
                RestaurantSocialMedia::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    public function getProducts($restoId, $skip, $top,$query)
    {
        $search = ControllerFactory::getSearchController();
        $where = $search->getFilterFieldsToMedooWhereArray($query);
        $where["AND"][Product::RESTAURANT_ID_COL] = $restoId;
        $where["LIMIT"] = [$skip,$top];
        //var_dump($where);

        $result = $this->db->select(Product::TABLE_NAME,
            [
                Product::ID_COL,
                Product::NAME_COL,
                Product::PHOTO_COL
            ],
            $where);

        $pc = DataControllerFactory::getProductController();
        for($i = 0; $i < count($result); $i++) {
            $prod = $result[$i];
            $result[$i] = $pc->getPhotoUrls($prod);
        }

        return $result;
    }

    public function getProductsAll($restoId)
    {
        $result = $this->db->select(Product::TABLE_NAME,
            [
                Product::ID_COL,
                Product::NAME_COL
            ],
            [
                Product::RESTAURANT_ID_COL => $restoId
            ]);

        return $result;
    }
}
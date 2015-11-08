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
use Rath\Entities\General\Address;
use Rath\Entities\Order\Coupon;
use Rath\Entities\Order\Order;
use Rath\Entities\Order\OrderDetail;
use Rath\Entities\Order\OrderStatus;
use Rath\Entities\Product\Product;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Promotion\PromotionType;
use Rath\Entities\Restaurant\Holiday;
use Rath\Entities\Restaurant\KitchenType;
use Rath\Entities\Restaurant\LoyaltyBonus;
use Rath\Entities\Restaurant\OpeningHours;
use Rath\Entities\Restaurant\PaymentMethod;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Entities\Restaurant\RestaurantHasPaymentMethod;
use Rath\Entities\Restaurant\RestaurantHasSpeciality;
use Rath\Entities\Restaurant\RestaurantPhoto;
use Rath\Entities\Restaurant\RestaurantSocialMedia;
use Rath\Entities\Restaurant\Speciality;
use Rath\Entities\Slots\SlotTemplate;
use Rath\Entities\Slots\SlotTemplateChange;
use Rath\Helpers\General;
use Rath\Helpers\PhotoManagement;
use Rath\Slim\Middleware\Authorization;

class RestaurantController extends ControllerBase
{
    //region Restaurant

    /**
     * @param $id
     * @return array|bool
     */
    public function getRestaurant($id,$includePhotoLinks = true){
        $resto = $this->db->get(Restaurant::TABLE_NAME,
            [
                Restaurant::ID_COL,
                Restaurant::NAME_COL,
                Restaurant::KITCHEN_TYPE_ID_COL,
                Restaurant::ADDRESS_ID_COL,
                Restaurant::LOGO_PHOTO_COL,
                Restaurant::PHONE_COL,
                Restaurant::EMAIL_COL,
                Restaurant::URL_COL,
                Restaurant::DOMINATING_COLOR_COL,
                Restaurant::COMMENT_COL,
//                Restaurant::PREORDER_COL
//                Restaurant::DELIVERY_COL,
//                Restaurant::DELIVERY_COST_COL
            ],
            [
                Restaurant::ID_COL => $id
            ]);
        if($includePhotoLinks)
            $resto[Restaurant::LOGO_PHOTO_COL] = PhotoManagement::getPhotoUrls($resto[Restaurant::LOGO_PHOTO_COL]);

        return $resto;
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
//        if ($resto->doesDelivery == Restaurant::DELIVERY_NONE) {
//            $resto->deliveryCost = 0;
//        }
//
//        if ($resto->doesDelivery == Restaurant::DELIVERY_PLATFORM) {
//            $resto->deliveryCost = 9.68;
//        }

        $this->db->update(Restaurant::TABLE_NAME,
            Restaurant::restaurantToDbArray($resto),
            [
                "AND" => [
                    Restaurant::ID_COL => $resto->id,
//                    Restaurant::PRODUCT_ID_COL =>  Authorization::$userId
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
//                    Restaurant::PRODUCT_ID_COL =>  Authorization::$userId
                ]
            ]);
        return $this->db->error();
    }

    public function updateLogoPhoto($id, $photoUrl)
    {
        $prod = $this->db->get(Restaurant::TABLE_NAME,
            [
                Restaurant::ID_COL,
                Restaurant::LOGO_PHOTO_COL
            ],
            [
                Restaurant::ID_COL => $id
            ]);

        if($prod[Restaurant::LOGO_PHOTO_COL] <> "")
          PhotoManagement::deletePhoto($prod[Restaurant::LOGO_PHOTO_COL]);

        $this->db->update(Restaurant::TABLE_NAME,
            [
                Restaurant::LOGO_PHOTO_COL => $photoUrl
            ],
            [
                Restaurant::ID_COL => $id
            ]);
        return $this->getRestaurant($id);
    }

    public function getRestaurantInTheSpotlight()
    {
        $query =
            "SELECT id,name,logoPhoto FROM restaurant
            ORDER BY RAND()
            LIMIT 4";
        $restos = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);

        for($i = 0; $i < count($restos); $i++){
            $restos[$i][Restaurant::LOGO_PHOTO_COL] = PhotoManagement::getPhotoUrls($restos[$i][Restaurant::LOGO_PHOTO_COL]);
        }
        return $restos;
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

    public function getKitchenTypes()
    {
        return $this->db->select(KitchenType::TABLE_NAME,"*");
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

    /**
     * @param $restoId int
     * @return bool
     */
    public function getIsOpen($restoId)
    {
        $result = $this->db->select(OpeningHours::TABLE_NAME,
            [
                OpeningHours::ID_COL,
                OpeningHours::OPEN_COL
            ],
            [
                "AND" => [
                    OpeningHours::DAY_OF_WEEK_COL => General::getCurrentDayOfWeek(),
                    OpeningHours::FROM_TIME_COL."[<=]" => General::getCurrentTime(),
                    OpeningHours::TO_TIME_COL."[>=]" => General::getCurrentTime(),
                    OpeningHours::RESTAURANT_ID_COL => $restoId
                ]
            ]);

        $result = OpeningHours::fromJsonArray($result);
//        $this->log->debug($result);
        foreach ($result as $oh) {
            /** @var OpeningHours $oh*/
            if($oh->open)
                return true;
        }
        return false;
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

    /**
     * @param $restoId
     * @param $paymentMethodId
     * @return bool
     */
    public function getRestaurantHasPaymentMethod($restoId,$paymentMethodId){
        return $this->db->has(RestaurantHasPaymentMethod::TABLE_NAME,
            [
                "AND"=>[
                    RestaurantHasPaymentMethod::RESTAURANT_ID_COL => $restoId,
                    RestaurantHasPaymentMethod::PAYMENT_METHOD_ID_COL => $paymentMethodId
                ]
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

    //region Speciality

    public function getRestaurantSpecialties($restoId){
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
        $result = $this->db->select(Speciality::TABLE_NAME,"*");
        $this->logLastQuery();
        return $result;
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
            "SELECT promotion.id,promotion.name,promotion.description,toDate,fromDate, (select sum(quantity) from promotionusagehistory".
            " WHERE promotionId = promotion.id) as 'usage' FROM ".Promotion::TABLE_NAME.
            " INNER JOIN promotiontype ON promotion.promotiontypeId = promotiontype.id".
            ' WHERE restaurantId = '.$this->db->quote($restoId).
            ' AND fromDate <= '.$date.
            ' AND toDate >= '.$date.
            'LIMIT '.$skip.",".$top;
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComingPromotions($restoId, $skip,$top){
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

    /**
     * @param $restoId Int
     * @return bool
     */
    public function getHasPromotions($restoId)
    {
        $result = $this->getActivePromotions($restoId,0,1);
        $this->log->debug($result);
        return count($result) != 0;
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
            (select sum(slots * quantity) from ".OrderDetail::TABLE_NAME." as od
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
        $this->logLastQuery();
        return $pdoQuery->fetchAll(PDO::FETCH_ASSOC);
        //return $this->db->error();
    }
    //endregion

    //region Photo
    /**
     * @param $id
     * @param $photos
     * @return array
     */
    public function addPhotos($id,$photos){
        foreach ($photos as $photo) {
            $restoPhoto = new RestaurantPhoto();
            $restoPhoto->restaurantId = $id;
            $restoPhoto->url = $photo->name;
            $this->db->insert(RestaurantPhoto::TABLE_NAME,
                RestaurantPhoto::toDbArray($restoPhoto)
            );
        }

        return $this->getPhotos($id);
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function getPhoto($id){
        return $this->db->get(RestaurantPhoto::TABLE_NAME,
            "*",
            [
                RestaurantPhoto::ID_COL => $id
            ]);
    }

    /**
     * @param $Id
     * @return array
     */
    public function getPhotos($Id)
    {
        $photos =  $this->db->select(RestaurantPhoto::TABLE_NAME,
            "*",
            [
                RestaurantPhoto::RESTAURANT_ID_COL => $Id,
                "LIMIT" => [0,10]
            ]);

        $photos = PhotoManagement::getPhotoUrlsForArray($photos,RestaurantPhoto::URL_COL);
        return $photos;
    }

    /**
     * @param $id
     * @return array
     */
    public function deletePhoto($id){
        $photo = $this->getPhoto($id);
        //var_dump($photo);
        if($photo[RestaurantPhoto::URL_COL] <> "")
            PhotoManagement::deletePhoto($photo[RestaurantPhoto::URL_COL]);

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
        return $this->db->get(PaymentMethod::TABLE_NAME,
            "*",
            [
                PaymentMethod::ID_COL => $id
            ]);
    }

    public function getPaymentMethods()
    {
        return $this->db->select(PaymentMethod::TABLE_NAME,"*");
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

    //region Products
    /**
     * @param $restoId
     * @param $skip
     * @param $top
     * @param $query
     * @return array|bool
     * @throws \Exception
     */
    public function getProducts($restoId, $skip, $top,$query)
    {
        $search = ControllerFactory::getSearchController();
        if(!empty($query)){
            $where = $search->getFilterFieldsToMedooWhereArray($query,$fields,false);
            $where["AND"][Product::RESTAURANT_ID_COL] = $restoId;
        }
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
            $prod[Product::PHOTO_COL] = PhotoManagement::getPhotoUrls($prod[Product::PHOTO_COL]);
            $result[$i] = $prod;
        }

        return $result;
    }

    public function getProductsAll($restoId)
    {
        $result = $this->db->select(Product::TABLE_NAME,
            [
                Product::ID_COL,
                Product::NAME_COL,
                Product::PHOTO_COL
            ],
            [
                Product::RESTAURANT_ID_COL => $restoId
            ]);

        $result = PhotoManagement::getPhotoUrlsForArray($result,Product::PHOTO_COL);
        return $result;
    }

    public function getProductsAllByProductType($restoId,$productTypeId)
    {
        $result = $this->db->select(Product::TABLE_NAME,
            [
                "[>]".Promotion::TABLE_NAME => [
                    Product::TABLE_NAME.".".Product::PROMOTION_ID_COL => Promotion::ID_COL
                ]
            ],
            [
                Product::TABLE_NAME.".".Product::ID_COL,
                Product::TABLE_NAME.".".Product::NAME_COL,
                Product::TABLE_NAME.".".Product::DESCRIPTION_COL,
                Product::TABLE_NAME.".".Product::PHOTO_COL,
                Product::TABLE_NAME.".".Product::PRICE_COL,
                Promotion::TABLE_NAME.".".Promotion::ID_COL."(promoId)",
                Promotion::TABLE_NAME.".".Promotion::NAME_COL."(promoName)",
                Promotion::TABLE_NAME.".".Promotion::DISCOUNT_TYPE_COL,
                Promotion::TABLE_NAME.".".Promotion::DISCOUNT_AMOUNT_COL
            ],
            [
                "AND" =>
                    [
                        Product::TABLE_NAME.".".Product::RESTAURANT_ID_COL => $restoId,
                        Product::TABLE_NAME.".".Product::PRODUCT_TYPE_ID => $productTypeId
                    ]
            ]);

        $this->log->debug($result);
        $this->log->debug(count($result));

        for($i = 0; $i < count($result); $i++){
            $this->log->debug($i);
            $discount = Promotion::fromJson($result[$i]);
            if($discount->discountType != null){
                $result[$i]["oldPrice"] = $result[$i][Product::PRICE_COL];
                switch($discount->discountType){
                    case Promotion::DISCOUNT_TYPE_VAL_PERS:
                        $mul = 1 - bcdiv($discount->discountAmount,100);
                        $price = floatval($result[$i][Product::PRICE_COL]);
                        $result[$i][Product::PRICE_COL] = bcmul($price, $mul);
                        break;
                    case Promotion::DISCOUNT_TYPE_VAL_AMOUNT:
                        $result[$i][Product::PRICE_COL] -= $discount->discountAmount;
                }

                if($result[$i][Product::PRICE_COL] < 0)
                    $result[$i][Product::PRICE_COL] = 0;
            }
            $this->log->debug("Count: ".count($result));
        }

        $result = PhotoManagement::getPhotoUrlsForArray($result,Product::PHOTO_COL);
        return $result;
    }
    //endregion


    //region Slots
    public function getSlotTemplates($restoId, $dayOfWeek = -1)
    {
        $where = [
            "AND" =>[
                SlotTemplate::RESTAURANT_ID_COL => $restoId
            ]
        ];

        if($dayOfWeek != -1)
            $where["AND"][SlotTemplate::DAY_OF_WEEK_COL] = $dayOfWeek;

        return $this->db->select(SlotTemplate::TABLE_NAME,
            "*",
            $where);
    }

    public function getSlotOverview($restoId, $date = null,$time = null)
    {
        if($date == null){
            $date = General::getCurrentDate();
            $dayOfWeek = General::getCurrentDayOfWeek();
        }
        else
            $dayOfWeek = date('w', strtotime($date));

        $where = [
            "AND" => [
                SlotTemplate::RESTAURANT_ID_COL => $restoId,
                SlotTemplate::DAY_OF_WEEK_COL => $dayOfWeek,
                "OR" => [
                    "OR #datesIsDate" => [
                        SlotTemplateChange::TABLE_NAME.".".SlotTemplateChange::DATE_COL => $date
                    ],
                    "OR #datesIsNull" => [
                        SlotTemplateChange::TABLE_NAME.".".SlotTemplateChange::DATE_COL => null
                    ]
                ]
            ]
        ];

        if($time != null){
            $where["AND"][SlotTemplate::FROM_TIME_COL."[<]"] = $time;
            $where["AND"][SlotTemplate::TO_TIME_COL."[>=]"] = $time;
        }

        $fields= [
            SlotTemplate::TABLE_NAME.".".SlotTemplate::ID_COL,
            SlotTemplate::FROM_TIME_COL,
            SlotTemplate::TO_TIME_COL,
            SlotTemplate::TABLE_NAME.".".SlotTemplate::QUANTITY_COL,
            SlotTemplateChange::TABLE_NAME.".".SlotTemplateChange::ID_COL."(changeId)",
            SlotTemplateChange::TABLE_NAME.".".SlotTemplateChange::QUANTITY_COL."(changeQty)"
        ];

        $join = [
            "[>]".SlotTemplateChange::TABLE_NAME =>
                [
                    SlotTemplate::TABLE_NAME.".".SlotTemplate::ID_COL => SlotTemplateChange::SLOT_TEMPLATE_ID_COL
                ]
        ];

        if($time == null)
            $result = $this->db->select(SlotTemplate::TABLE_NAME,
                $join,
                $fields,
                $where);
        else
            $result = $this->db->get(SlotTemplate::TABLE_NAME,
                $join,
                $fields,
                $where);

//        $this->logLastQuery();
//        $this->logMedooError();
        $this->log->debug($result);
        return $result;
    }

    /**
     * @param $order Order
     * @param null $date
     * @return int
     */
    public function getSlotUsage($order,$date = null)
    {
        $this->log->debug("Date :".$date);
        if(empty($date))
            $date = $this->db->quote(date($order->orderDateTime));
        else
            $date = $this->db->quote($date);
        $this->log->debug("Date :".$date);
        $query =
            "SELECT SUM(product.slots * orderdetail.quantity) as total FROM orders
            INNER JOIN orderdetail ON orders.id = orderdetail.orderId
            INNER JOIN product ON orderdetail.productId = product.id
            WHERE orders.slottemplateId = ".$order->slottemplateId
            ." and date(orders.orderDateTime) = ".$date
            ." and orders.id != ".$order->id;

        $pdoQuery = $this->db->query($query);

        $this->logLastQuery();
        $this->logMedooError();

        $result = $pdoQuery->fetchColumn(0);
        $this->log->debug($result);
        return $result;
    }
    //endregion

    //region LoyaltyBonus
    public function getLoyaltyBonus($restoId)
    {
        $result = $this->db->select(LoyaltyBonus::TABLE_NAME,
            [
                "[>]".Product::TABLE_NAME => [
                    LoyaltyBonus::PRODUCT_ID_COL => Product::ID_COL
                ]
            ],
            [
                LoyaltyBonus::TABLE_NAME.".".LoyaltyBonus::ID_COL,
                LoyaltyBonus::POINTS_COL,
                LoyaltyBonus::TYPE_COL,
                LoyaltyBonus::AMOUNT_COL,
                LoyaltyBonus::PRODUCT_ID_COL,
                Product::TABLE_NAME.".".Product::NAME_COL."(productName)"
            ],
            [
                LoyaltyBonus::TABLE_NAME.".".LoyaltyBonus::RESTAURANT_ID_COL => $restoId,
                "LIMIT" => [0,1]
            ]);

        $this->log->debug($this->db->last_query());
        return $result;

    }
    //endregion

    //region Coupons
    /**
     * @param $restoId
     * @return array|bool
     */
    public function getCoupons($restoId)
    {
        return $this->db->select(Coupon::TABLE_NAME,
            "*",
            [
                Coupon::RESTAURANT_ID_COL => $restoId
            ]);
    }
    //endregion

}
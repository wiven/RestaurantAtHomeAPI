<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:03
 */

namespace Rath\Entities\Restaurant;


use Rath\Entities\EntityBase;

class Restaurant extends EntityBase
{
    const TABLE_NAME = "restaurant";

    const ID_COL = "id";
    const USER_ID_COL = "userId";
    const KITCHEN_TYPE_ID_COL = "kitchentypeId";
    const ADDRESS_ID_COL = "addressId";

    const NAME_COL = "name";
    const PHONE_COL ="phone";
    const EMAIL_COL ="email";
    const URL_COL = "url";
    const LOGO_PHOTO_COL = "logoPhoto";
    const DOMINATING_COLOR_COL ="dominatingColor";
    const COMMENT_COL = "comment";

//    const PREORDER_COL = "preorder";
//
//    const DELIVERY_COL = "doesDelivery";
//    const DELIVERY_COST_COL = "deliveryCost";
//
//    const DELIVERY_NONE = 0;
//    const DELIVERY_RESTAURANT = 1;
//    const DELIVERY_PLATFORM = 2;

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $kitchentypeId;
    /**
     * @var int
     */
    public $addressId;
    /**
     * @var string
     */
    public $phone;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $logoPhoto;
    /**
     * @var string
     */
    public $dominatingColor;
    /**
     * @var string
     */
    public $comment;
//    /**
//     * @var bool
//     */
//    public $preorder = false;
//    /**
//     * @var int
//     */
//    public $doesDelivery = Restaurant::DELIVERY_NONE;
//    /**
//     * @var float
//     */
//    public $deliveryCost = 0;


    /**
     * @param $resto Restaurant
     * @return array
     */
    static function restaurantToDbArray($resto){
        $array =  [
            Restaurant::NAME_COL => $resto->name,
            Restaurant::KITCHEN_TYPE_ID_COL => $resto->kitchentypeId,
            Restaurant::ADDRESS_ID_COL => $resto->addressId,
            Restaurant::PHONE_COL => $resto->phone,
            Restaurant::EMAIL_COL => $resto->email,
            Restaurant::URL_COL => $resto->url,
            Restaurant::COMMENT_COL => $resto->comment
//            Restaurant::PREORDER_COL => $resto->preorder,
//            Restaurant::DELIVERY_COL => $resto->doesDelivery,
//            Restaurant::DELIVERY_COST_COL => $resto->deliveryCost
        ];

//        if(!empty($resto->dominatingColor))
//            $array[Restaurant::DOMINATING_COLOR_COL] = $resto->dominatingColor;

        if(!empty($resto->logoPhoto))
            $array[Restaurant::LOGO_PHOTO_COL] = $resto->logoPhoto;

        if(!empty($resto->userId))
            $array[Restaurant::USER_ID_COL] = $resto->userId;

        return $array;
    }
}
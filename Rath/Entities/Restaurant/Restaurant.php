<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:03
 */

namespace Rath\Entities\Restaurant;


class Restaurant
{
    const TABLE_NAME = "restaurant";

    const ID_COL = "id";
    const USER_ID_COL = "userId";
    const KITCHEN_TYPE_ID_COL = "kitchentypeId";
    const ADDRESS_ID_COL = "addressId";

    const PHONE_COL ="phone";
    const EMAIL_COL ="email";
    const URL_COL = "url";
    const PHOTO_COL = "photo";
    const DOMINATING_COLOR_COL ="dominatingColor";
    const COMMENT_COL = "comment";

    public $id;
    public $userId;
    public $kitchentypeId;
    public $addressId;
    public $phone;
    public $email;
    public $url;
    public $photo;
    public $dominatingColor;
    public $comment;


    /**
     * @param $resto Restaurant
     * @return array
     */
    static function restaurantToDbArray($resto){
        return [
            USER_ID_COL => $resto->userId,
            KITCHEN_TYPE_ID_COL => $resto->kitchentypeId,
            ADDRESS_ID_COL => $resto->addressId,
            PHONE_COL => $resto->phone,
            EMAIL_COL => $resto->email,
            URL_COL => $resto->url,
            PHOTO_COL => $resto->photo,
            DOMINATING_COLOR_COL => $resto->dominatingColor,
            COMMENT_COL => $resto->comment
        ];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 15/08/2015
 * Time: 13:41
 */

namespace Rath\Entities\Restaurant;


class RestaurantPhoto
{
    const TABLE_NAME  = "restaurantphoto";

    const ID_COL = "id";
    const RESTAURANT_ID_COL = "restaurantId";
    const URL_COL = "url";

    public $id;
    public $restaurantId;
    public $url;

    /**
     * @param $restoPhoto RestaurantPhoto
     * @return array
     */
    public static function toDbArray($restoPhoto)
    {
        return[
            self::RESTAURANT_ID_COL => $restoPhoto->restaurantId,
            self::URL_COL => $restoPhoto->url
        ];
    }
}
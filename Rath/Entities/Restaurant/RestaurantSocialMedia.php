<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:28
 */

namespace Rath\Entities\Restaurant;


class RestaurantSocialMedia
{
    const TABLE_NAME = "restaurantsocialmedia";

    const ID_COL = "id";
    const RESTAURANT_ID_COL = "restaurantId";
    const SOCIAL_MEDIA_TYPE_ID = "socialmediatypeId";
    const URL_COL = "url";

    public $id;
    public $restaurantId;
    public $socialmediatypeId;
    public $url;

    /**
     * @param $soc RestaurantSocialMedia
     * @return array
     */
    public static function toDbArray($soc)
    {
        return [
            self::RESTAURANT_ID_COL => $soc->restaurantId,
            self::SOCIAL_MEDIA_TYPE_ID => $soc->socialmediatypeId,
            self::URL_COL => $soc->url
        ];
    }
}
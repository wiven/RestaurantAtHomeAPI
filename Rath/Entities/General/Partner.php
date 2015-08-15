<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 15/08/2015
 * Time: 12:31
 */

namespace Rath\Entities\General;


class Partner
{
    const TABLE_NAME = "partner";

    const ID_COL  = "id";
    const NAME_COL = "name";
    const PHOTO_COL = "photo";
    const URL_COL = "url";

    public $id;
    public $name;
    public $photo;
    public $url;

    /**
     * @param $partner Partner
     * @return array
     */
    public static function toDbArray($partner)
    {
        return [
            self::NAME_COL => $partner->name,
            self::PHOTO_COL => $partner->photo,
            self::URL_COL => $partner->url
        ];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:30
 */

namespace Rath\Entities\General;


class SocialMediaType
{
    const TABLE_NAME = "socialmediatype";

    const ID_COL = "id";
    const NAME_COL = "name";

    public $id;
    public $name;

    /**
     * @param $socType SocialMediaType
     * @return array
     */
    public static function toDbArray($socType)
    {
        return[
            self::ID_COL => $socType->id,
            self::NAME_COL => $socType->name
        ];
    }

    //region Default Values
    const val_Facebook = 1;
    const val_Twitter = 2;
    const val_Instagram = 3;
    //endregion
}
<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 9/10/2015
 * Time: 9:32 PM
 */

namespace Rath\Entities\AppMgt;


use Rath\Entities\DynamicClass;

class FilterField
{
    const TABLE_NAME = "filterfield";

    const ID_COL  = "id";
    const DATABASE_FIELDNAME_COL = "databaseFieldname";
    const LIKE_COL = "like";

    public $id;
    public $databaseFieldname;
    public $like;

    /**
     * @param $array
     * @return FilterField
     */
    public static function toFilterField($array)
    {
        return new DynamicClass($array);
    }
}
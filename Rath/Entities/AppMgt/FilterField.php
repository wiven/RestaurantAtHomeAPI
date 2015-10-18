<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 9/10/2015
 * Time: 9:32 PM
 */

namespace Rath\Entities\AppMgt;


use Rath\Entities\DynamicClass;
use Rath\Entities\EntityBase;

class FilterField extends EntityBase
{
    const TABLE_NAME = "filterfield";

    const ID_COL  = "id";
    const DATABASE_FIELDNAME_COL = "databaseFieldname";
    const LIKE_COL = "like";

    const TAG_ID_FIELD = 1020;
    const DISTANT_MTX_FROM_CITY_FIELD = 900;
    const PROMOTIONTYPE_ID_FIELD = 1050;

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $databaseFieldname;
    /**
     * @var bool
     */
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
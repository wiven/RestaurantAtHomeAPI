<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 08-Oct-15
 * Time: 07:28 PM
 */

namespace Rath\Entities\Order;


class MollieInfo
{
    const TABLE_NAME = "coupon";

    const ID_COL = "id";
    const MOLLIE_ID_COL = "mollieinfoid";
    const MODE_COL ="mode";
    const METHOD_COL ="method";

    public $id;
    public $mollieId;
    public $mode;
    public $method;

    /**
     * @param $info MollieInfo
     * @return array
     */
    public static function toDbArray($info)
    {
        return[
            self::MOLLIE_ID_COL => $info->mollieId,
            self::MODE_COL => $info->mode,
            self::METHOD_COL => $info->method
        ];
    }
}
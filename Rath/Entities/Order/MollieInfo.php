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
    const TABLE_NAME = "mollieinfo";

    const ID_COL = "id";
    const MOLLIE_ID_COL = "mollieid";
    const MODE_COL ="mode";
    const METHOD_COL ="method";
    const PAYMENT_URL_COL = "paymentUrl";
    const ORDER_ID_COL ="ordersid";

    public $id;
    public $mollieId;
    public $mode;
    public $method;
    public $paymentUrl;
    public $ordersid;

    /**
     * @param $info MollieInfo
     * @return array
     */
    public static function toDbArray($info)
    {
        return[
            self::MOLLIE_ID_COL => $info->mollieId,
            self::ORDER_ID_COL => $info->ordersid,
            self::MODE_COL => $info->mode,
            self::METHOD_COL => $info->method,
            self::PAYMENT_URL_COL => $info->paymentUrl
        ];
    }
}
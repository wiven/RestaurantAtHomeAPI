<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:38
 */

namespace Rath\Entities\Restaurant;


class PaymentMethod
{
    const TABLE_NAME = "paymentmethod";

    const ID_COL = "id";
    const NAME_COL = "name";
    const MOLLIE_ID_COL = "mollieId";

    public $id;
    public $name;
    public $mollieId;

    /**
     * @param $payment PaymentMethod
     * @return array
     */
    public static function toDbArray($payment){
        return [
            PaymentMethod::ID_COL => $payment->id,
            PaymentMethod::NAME_COL => $payment->name,
            PaymentMethod::MOLLIE_ID_COL => $payment->mollieId
        ];
    }
}
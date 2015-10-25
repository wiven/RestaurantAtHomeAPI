<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:38
 */

namespace Rath\Entities\Restaurant;


use Rath\Entities\EntityBase;

class PaymentMethod extends EntityBase
{
    const TABLE_NAME = "paymentmethod";


    const ID_COL = "id";
    const NAME_COL = "name";
    const MOLLIE_ID_COL = "mollieid";

    //handled by resto
    const CASH_PAYMENT_ID = 1;
    //Handled by mollie
    const BANCONTACT_PAYMENT_ID = 2;

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
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
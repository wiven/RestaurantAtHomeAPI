<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 10/08/2015
 * Time: 19:17
 */

namespace Rath\Entities\Order;


class Coupon
{
    const TABLE_NAME = "coupon";

    const ID_COL = "id";
    const START_DATE_COL = "startDate";
    const END_DATE_COL = "endDate";
    const DISCOUNT_TYPE_COL = "discountType";
        const DISCOUNT_TYPE_VAL_PERS = "Percentage";
        const DISCOUNT_TYPE_VAL_AMOUNT = "Amount";
    const DISCOUNT_AMOUT_COL = "discountAmount";
    const QUANTITY_COL = "quantity";

    public $id;
    public $startDate;
    public $endDate;
    public $discountType;
    public $discountAmount;
    public $quantity;

    /**
     * @param $coupon Coupon
     * @return array
     */
    public function toDbArray($coupon)
    {
        return [
            Coupon::START_DATE_COL => $coupon->startDate,
            Coupon::END_DATE_COL => $coupon->endDate,
            Coupon::DISCOUNT_TYPE_COL => $coupon->discountType,
            Coupon::DISCOUNT_AMOUT_COL => $coupon->discountAmount,
            Coupon::DISCOUNT_AMOUT_COL => $coupon->discountAmount,
            Coupon::QUANTITY_COL => $coupon->quantity
        ];
    }
}
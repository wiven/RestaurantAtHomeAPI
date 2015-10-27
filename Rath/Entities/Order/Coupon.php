<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 10/08/2015
 * Time: 19:17
 */

namespace Rath\Entities\Order;


use Rath\Entities\EntityBase;
use Rath\Helpers\General;

class Coupon extends EntityBase
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
    const CODE_COL = "code";
    const RESTAURANT_ID_COL = "restaurantid";

    /**
     * @var int
     */
    public $id;
    /**
     * @var int | null
     */
    public $restaurantid;
    /**
     * @var string
     */
    public $code;
    /**
     * @var string
     */
    public $startDate;
    /**
     * @var string
     */
    public $endDate;
    /**
     * @var string
     */
    public $discountType;
    /**
     * @var float
     */
    public $discountAmount;
    /**
     * @var int
     */
    public $quantity;

    public function isValid()
    {
        $date = General::getCurrentDate();
        return $this->startDate <= $date && $this->endDate >= $date;
    }

    /**
     * @param $coupon Coupon
     * @return array
     */
    public static function toDbArray($coupon)
    {
        $data = [
            Coupon::START_DATE_COL => $coupon->startDate,
            Coupon::END_DATE_COL => $coupon->endDate,
            Coupon::DISCOUNT_TYPE_COL => $coupon->discountType,
            Coupon::DISCOUNT_AMOUT_COL => $coupon->discountAmount,
            Coupon::DISCOUNT_AMOUT_COL => $coupon->discountAmount,
            Coupon::QUANTITY_COL => $coupon->quantity
        ];

        if(isset($coupon->restaurantid))
            $data[self::RESTAURANT_ID_COL] = $coupon->restaurantid;

        if(isset($coupon->code))
            $data[self::CODE_COL] = $coupon->code;

        return $data;
    }
}
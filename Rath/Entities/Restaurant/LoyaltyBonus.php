<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 24-Sep-15
 * Time: 07:24 PM
 */

namespace Rath\Entities\User;


class LoyaltyBonus
{
    const TABLE_NAME = "loyaltybonus";

    const ID_COL = "id";
    const PRODUCT_ID_COL ="productid";
    const RESTAURANT_ID_COL = "restaurantid";
    const QUANTITY_COL = "quantity";

    public $id;
    public $productid;
    public $restaurantid;
    public $quantity;

    /**
     * @param $lp LoyaltyBonus
     * @return array
     */
    public static function toDbInsertArray($lp)
    {
        return [
            self::PRODUCT_ID_COL => $lp->productid,
            self::RESTAURANT_ID_COL => $lp->restaurantid,
            self::QUANTITY_COL => $lp->quantity
        ];
    }

    /**
     * @param $lp LoyaltyBonus
     * @return array
     */
    public static function toDbUpdateArray($lp)
    {
        return[
            self::QUANTITY_COL => $lp->quantity
        ];
    }

}
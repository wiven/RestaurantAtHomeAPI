<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 24-Sep-15
 * Time: 07:24 PM
 */

namespace Rath\Entities\User;


use Rath\Entities\EntityBase;

class LoyaltyPoints extends EntityBase
{
    const TABLE_NAME = "loyaltypoints";

    const ID_COL = "id";
    const USER_ID_COL ="userid";
    const RESTAURANT_ID_COL = "restaurantid";
    const QUANTITY_COL = "quantity";

    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $userid;
    /**
     * @var int
     */
    public $restaurantid;
    /**
     * @var int
     */
    public $quantity;

    /**
     * @param $lp LoyaltyPoints
     * @return array
     */
    public static function toDbInsertArray($lp)
    {
        return [
            self::USER_ID_COL => $lp->userid,
            self::RESTAURANT_ID_COL => $lp->restaurantid,
            self::QUANTITY_COL => $lp->quantity
        ];
    }

    /**
     * @param $lp LoyaltyPoints
     * @return array
     */
    public static function toDbUpdateArray($lp)
    {
        return[
            self::QUANTITY_COL => $lp->quantity
        ];
    }

}
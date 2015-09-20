<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 20-Sep-15
 * Time: 01:40 PM
 */

namespace Rath\Entities\Slots;


class SlotTemplate
{
    const TABLE_NAME = "slottemplate";

    const ID_COL = "id";
    const RESTAURANT_ID_COL = "restaurantId";
    const DAY_OF_WEEK_COL = "dayOfWeek";
    const FROM_TIME_COL = "fromTime";
    const TO_TIME_COL = "toTime";
    const QUANTITY_COL = "quantity";

    public $id;
    public $restaurantId;
    public $dayOfWeek;
    public $fromTime;
    public $toTime;
    public $quantity;

    /**
     * @param $st SlotTemplate
     * @return array
     */
    public static function toDbArray($st){
        return [
            self::RESTAURANT_ID_COL => $st->restaurantId,
            self::DAY_OF_WEEK_COL => $st->dayOfWeek,
            self::FROM_TIME_COL => $st->fromTime,
            self::TO_TIME_COL => $st->toTime,
            self::QUANTITY_COL => $st->quantity
        ];

    }
}
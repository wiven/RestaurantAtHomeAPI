<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 20-Sep-15
 * Time: 01:40 PM
 */

namespace Rath\Entities\Slots;


use Rath\Entities\EntityBase;

class SlotTemplate extends  EntityBase
{
    const TABLE_NAME = "slottemplate";

    const ID_COL = "id";
    const RESTAURANT_ID_COL = "restaurantId";
    const DAY_OF_WEEK_COL = "dayOfWeek";
    const FROM_TIME_COL = "fromTime";
    const TO_TIME_COL = "toTime";
    const QUANTITY_COL = "quantity";

    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $restaurantId;
    /**
     * @var int
     */
    public $dayOfWeek;
    /**
     * @var string
     */
    public $fromTime;
    /**
     * @var string
     */
    public $toTime;
    /**
     * @var int
     */
    public $quantity;
    /**
     * @var int | null
     */
    public $changeId;
    /**
     * @var int | null
     */
    public $changeQty;

    public function getSlotAvailability()
    {
        if($this->changeQty!=0)
            return $this->changeQty;
        return $this->quantity;
    }


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
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:34
 */

namespace Rath\Entities\Restaurant;


use Rath\Entities\EntityBase;

class OpeningHours extends EntityBase
{
    const TABLE_NAME = "openinghours";

    const ID_COL = "id";
    const RESTAURANT_ID_COL = "restaurantId";
    const DAY_OF_WEEK_COL = "dayOfWeek";
    const FROM_TIME_COL = "fromTime";
    const TO_TIME_COL = "toTime";
    const OPEN_COL = "open";

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
     * @var string | null
     */
    public $fromTime;
    /**
     * @var string | null
     */
    public $toTime;
    /**
     * @var bool
     */
    public $open;

    public static function toDbArray($oH){
        return [
            OpeningHours::RESTAURANT_ID_COL => $oH->restaurantId,
            OpeningHours::DAY_OF_WEEK_COL => $oH->dayOfWeek,
            OpeningHours::FROM_TIME_COL => $oH->fromTime,
            OpeningHours::TO_TIME_COL => $oH->toTime,
            OpeningHours::OPEN_COL => $oH->open
        ];
    }
}
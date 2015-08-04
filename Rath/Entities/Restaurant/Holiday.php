<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:33
 */

namespace Rath\Entities\Restaurant;


class Holiday
{
    const TABLE_NAME = "holiday";

    const ID_COL = "id";
    const RESTAURANT_ID_COL = "restaurantId";
    const FROM_DATE_COL = "fromDate";
    const TO_DATE_COL = "toDate";

    public $id;
    public $restaurantId;
    public $fromDate;
    public $toDate;

    /**
     * @param $holiday Holiday
     * @return array
     */
    public static function holidayToDbArray($holiday){
        return [
            Holiday::RESTAURANT_ID_COL => $holiday->restaurantId,
            Holiday::FROM_DATE_COL => $holiday->fromDate,
            Holiday::TO_DATE_COL => $holiday->toDate
        ];
    }
}
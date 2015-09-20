<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 20-Sep-15
 * Time: 01:44 PM
 */

namespace Rath\Entities\Slots;


class SlotTemplateChange
{
    const TABLE_NAME = "slottemplatechange";

    const ID_COL = "id";
    const SLOT_TEMPLATE_ID_COL = "slottemplateId";
    const DATE_COL = "date";
    const QUANTITY_COL = "quantity";

    public $id;
    public $slottemplateId;
    public $date;
    public $quantity;

    /**
     * @param $st SlotTemplateChange
     * @return array
     */
    public static function toDbArray($st){
        return [
            self::SLOT_TEMPLATE_ID_COL => $st->slottemplateId,
            self::DATE_COL => $st->date,
            self::QUANTITY_COL => $st->quantity
        ];
    }
}
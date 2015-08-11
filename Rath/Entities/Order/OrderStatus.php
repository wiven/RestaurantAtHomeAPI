<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 17:52
 */

namespace Rath\Entities\Order;

class OrderStatus
{
    const TABLE_NAME = "orderstatus";

    const ID_COL = "id";
    const NAME_COL = "name";

    public $id;
    public $name;

    /**
     * @param $os OrderStatus
     * @return array
     */
    public function toDbArray($os)
    {
        return [
            OrderStatus::ID_COL => $os->id,
            OrderStatus::NAME_COL => $os->id
        ];
    }

    //region Default Values
    const val_New = 10;
    const val_Accepted = 20;
    const val_InProgress = 30;
    const val_Ready = 40;
    const val_OnRoute = 50;
    const val_Finished = 100;
    //endregion
}
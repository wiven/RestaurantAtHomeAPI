<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:18
 */

namespace Rath\Entities\Product;


class Tag
{
    const TABLE_NAME = "tag";

    const ID_COL = "id";
    const NAME_COL = "name";

    public $id;
    public $name;

    /**
     * @param $payment Tag
     * @return array
     */
    public static function toDbArray($payment){
        return [
            Tag::NAME_COL => $payment->name
        ];
    }
}
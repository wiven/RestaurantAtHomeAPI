<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:11
 */

namespace Rath\Entities\Promotion;


class PromotionType
{
    const TABLE_NAME = "promotiontype";

    const ID_COL = "id";
    const NAME_COL = "name";

    public $id;
    public $name;

    /**
     * @param $promoType PromotionType
     * @return array
     */
    public static function toDbArray($promoType)
    {
        return [
            PromotionType::NAME_COL => $promoType->name
        ];
    }
}
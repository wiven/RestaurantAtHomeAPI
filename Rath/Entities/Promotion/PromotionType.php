<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:11
 */

namespace Rath\Entities\Promotion;


use Rath\Entities\EntityBase;

class PromotionType extends EntityBase
{
    const TABLE_NAME = "promotiontype";

    const ID_COL = "id";
    const NAME_COL = "name";

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
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

    public function __toString()
    {
        return (string)$this->id;
    }
}
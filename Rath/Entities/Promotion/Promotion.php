<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:06
 */

namespace Rath\Entities\Promotion;


use Rath\Entities\EntityBase;

class Promotion extends EntityBase
{
    const TABLE_NAME = "promotion";

    const ID_COL = "id";
    const PROMOTION_TYPE_ID_COL = "promotiontypeId";
    const RESTAURANT_ID_COL = "restaurantId";

    const NAME_COL = "name";
    const FROM_DATE_COL = "fromDate";
    const TO_DATE_COL = "toDate";
    const DESCRIPTION_COL = "description";
    const DISCOUNT_TYPE_COL = "discountType";
        const DISCOUNT_TYPE_VAL_PERS = "Percentage";
        const DISCOUNT_TYPE_VAL_AMOUNT = "Amount";
    const DISCOUNT_AMOUNT_COL = "discountAmount";
    const LOYALTY_POINTS_COL ="loyaltyPoints";

    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $promotiontypeId;
    /**
     * @var int
     */
    public $restaurantId;

    //Used for updating linked products.
    /**
     * @var int
     */
    public $productId;

    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $fromDate;
    /**
     * @var string
     */
    public $toDate;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $discountType;
    /**
     * @var float
     */
    public $discountAmount;
    /**
     * @var int
     */
    public $loyaltyPoints;



    /**
     * @param $promotion Promotion
     * @return array
     */
    public static function toDbArray($promotion)
    {
        return [
            Promotion::NAME_COL => $promotion->name,
            Promotion::PROMOTION_TYPE_ID_COL => $promotion->promotiontypeId,
            Promotion::RESTAURANT_ID_COL => $promotion->restaurantId,
            Promotion::FROM_DATE_COL => $promotion->fromDate,
            Promotion::TO_DATE_COL => $promotion->toDate,
            Promotion::DESCRIPTION_COL => $promotion->description,
            Promotion::DISCOUNT_TYPE_COL => $promotion->discountType,
            Promotion::DISCOUNT_AMOUNT_COL => $promotion->discountAmount,
            Promotion::LOYALTY_POINTS_COL => $promotion->loyaltyPoints
        ];
    }

}

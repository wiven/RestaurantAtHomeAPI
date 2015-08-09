<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:13
 */

namespace Rath\Entities\Promotion;


class PromotionUsageHistory
{
    const TABLE_NAME = "promotionusagehistory";

    const ID_COL = "id";
    const PROMOTION_ID_COL = "promotionId";
    const ORDER_ID_COL = "orderId";
    const QUANTITY_COL = "quantity";

    public $id;
    public $promotionId;
    public $orderId;
    public $quantity;

    /**
     * @param $promHisto PromotionUsageHistory
     * @return array
     */
    public static function toDbArray($promHisto)
    {
        return [
            PromotionUsageHistory::PROMOTION_ID_COL => $promHisto->promotionId,
            PromotionUsageHistory::ORDER_ID_COL => $promHisto->orderId,
            PromotionUsageHistory::QUANTITY_COL => $promHisto->quantity
        ];
    }
}
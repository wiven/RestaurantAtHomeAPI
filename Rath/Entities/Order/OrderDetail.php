<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 20:55
 */

namespace Rath\Entities\Order;


use Rath\Entities\EntityBase;
use Rath\Helpers\General;

class OrderDetail extends EntityBase
{
    const TABLE_NAME = "orderdetail";

    const ID_COL = "id";
    const ORDER_ID_COL = "orderId";
    const PRODUCT_ID_COL = "productId";
    const QUANTITY_COL = "quantity";
//    const UNIT_PRICE_COL = "unitPrice";
//    const LINE_TOTAL_COL = "lineTotal";

    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $orderId;
    /**
     * @var int
     */
    public $productId;
    /**
     * @var string
     */
    public $name;
    /**
     * @var int
     */
    public $quantity;
    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $lineTotal;
    /**
     * @var float | null
     */
    public $oldPrice;

    //Promotion join values
    /**
     * @var string | null
     */
    public $discountType;
    /**
     * @var float | null
     */
    public $discountAmount;

    /**
     * @var string | null
     */
    public $fromDate;
    /**
     * @var string | null
     */
    public $toDate;

    public function promotionValid()
    {
        $date = General::getCurrentDate();
        return ($this->fromDate <= $date && $this->toDate >= $date);
    }


    /**
     * @param $od OrderDetail
     * @return array
     */
    public static function toDbArray($od)
    {
        $data = [
            OrderDetail::ORDER_ID_COL => $od->orderId,
            OrderDetail::QUANTITY_COL => $od->quantity
        ];

        if(isset($od->productId))
            $data[OrderDetail::PRODUCT_ID_COL] = $od->productId;

        return $data;
    }
}
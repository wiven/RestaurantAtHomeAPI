<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 20:55
 */

namespace Rath\Entities\Order;


class OrderDetail
{
    const TABLE_NAME = "orderdetail";

    const ID_COL = "id";
    const ORDER_ID_COL = "orderId";
    const PRODUCT_ID_COL = "productId";
    const QUANTITY_COL = "quantity";
    const UNIT_PRICE_COL = "unitPrice";
    const LINE_TOTAL_COL = "lineTotal";

    public $id;
    public $orderId;
    public $productId;
    public $quantity;
    public $unitPrice;
    public $lineTotal;

    /**
     * @param $od OrderDetail
     * @return array
     */
    public function toDbArray($od)
    {
        return [
            OrderDetail::ORDER_ID_COL => $od->id,
            OrderDetail::PRODUCT_ID_COL => $od->productId,
            OrderDetail::QUANTITY_COL => $od->quantity,
            OrderDetail::UNIT_PRICE_COL => $od->unitPrice,
            OrderDetail::LINE_TOTAL_COL => $od->lineTotal
        ];
    }
}
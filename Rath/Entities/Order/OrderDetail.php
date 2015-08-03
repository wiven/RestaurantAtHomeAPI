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
}
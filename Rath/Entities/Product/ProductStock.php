<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:20
 */

namespace Rath\Entities\Product;


class ProductStock
{
    const TABLE_NAME = "productstock";

    const ID_COL = "id";
    const PRODUCT_ID_COL = "productId";

    const AMOUNT_COL = "amount";
    const DAY_OF_WEEK_COL = "dayOfWeek";
}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:22
 */

namespace Rath\Entities\Product;


class RelatedProducts
{
    const TABLE_NAME = "relatedproducts";

    const PRODUCT_ID_COL = "productId";
    const RELATED_PRODUCT_ID_COL = "relatedProductId";

    public $productId;
    public $relatedProductId;


}
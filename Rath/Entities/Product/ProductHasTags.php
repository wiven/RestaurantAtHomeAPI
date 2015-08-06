<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:19
 */

namespace Rath\Entities\Product;


class ProductHasTags
{
    const TABLE_NAME = "product_has_tag";

    const PRODUCT_ID_COL = "productId";
    const TAG_ID_COL = "tagId";
}
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

    public $id;
    public $productId;
    public $amount;
    public $dayOfWeek;


    /**
     * @param $prodStock ProductStock
     * @return array
     */
    public static function toDbArray($prodStock){
        $array = [
            ProductStock::PRODUCT_ID_COL => $prodStock->productId,
            ProductStock::AMOUNT_COL => $prodStock->amount,
            ProductStock::DAY_OF_WEEK_COL => $prodStock->dayOfWeek
        ];

//        if(!empty($prodStock->id))
//            $array[ProductStock::ID_COL] = $prodStock->id;

        return $array;
    }
}
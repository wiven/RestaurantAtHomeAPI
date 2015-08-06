<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:22
 */

namespace Rath\Entities\Product;


class ProductType
{
    const TABLE_NAME = "producttype";

    const ID_COL = "id";
    const NAME_COL = "name";

    public $id;
    public $name;

    /**
     * @param $prodType ProductType
     * @return array
     */
    public static function toDbArray($prodType){
        return [
            Product::ID_COL => $prodType->id,
            Product::NAME_COL => $prodType->name
        ];
    }
}
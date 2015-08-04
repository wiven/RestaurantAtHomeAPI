<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:14
 */

namespace Rath\Entities\Product;


class Product
{
    const TABLE_NAME = "product";

    const ID_COL = "id";
    const RESTAURANT_ID_COL = "restaurantId";
    const PRODUCT_TYPE_ID = "producttypeId";

    const NAME_COL = "name";
    const DESCRIPTION_COL ="description";
    const PRICE_COL = "price";
    const SLOTS_COL = "slots";

    public $id;
    public $restaurantId;
    public $producttypeId;
    public $name;
    public $description;
    public $price;
    public $slots;

    /**
     * @param $product Product
     * @return array
     */
    static function productToDbArray($product){
        return [
            Product::RESTAURANT_ID_COL => $product->restaurantId,
            Product::PRODUCT_TYPE_ID => $product->producttypeId,
            Product::NAME_COL => $product->name,
            Product::DESCRIPTION_COL => $product->description,
            Product::PRICE_COL => $product->price,
            Product::SLOTS_COL => $product->slots
        ];
    }

}
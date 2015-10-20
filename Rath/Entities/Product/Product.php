<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:14
 */

namespace Rath\Entities\Product;


use Rath\Entities\EntityBase;

class Product extends EntityBase
{
    const TABLE_NAME = "product";

    const ID_COL = "id";
    const RESTAURANT_ID_COL = "restaurantId";
    const PRODUCT_TYPE_ID = "producttypeId";
    const PROMOTION_ID_COL = "promotionId";

    const NAME_COL = "name";
    const DESCRIPTION_COL ="description";
    const LOYALTY_POINTS_COL ="loyaltyPoints";
    const PHOTO_COL = "photo";
    const PRICE_COL = "price";
    const SLOTS_COL = "slots";

    public $id;
    public $restaurantId;
    public $producttypeId;
    public $promotionId;
    public $name;
    public $description;
    public $loyaltyPoints;
    public $photo;
    public $price;
    public $slots;

    /**
     * @param $product Product
     * @return array
     */
    public static function toDbArray($product){
        $data =  [
            Product::RESTAURANT_ID_COL => $product->restaurantId,
            Product::PRODUCT_TYPE_ID => $product->producttypeId,
            Product::NAME_COL => $product->name,
            Product::DESCRIPTION_COL => $product->description,

            Product::PRICE_COL => $product->price,
            Product::SLOTS_COL => $product->slots
        ];

        if(!empty($product->promotionId))
            $data[self::PROMOTION_ID_COL] = $product->promotionId;

        if(!empty($product->photo))
            $data[self::PHOTO_COL] = $product->photo;

        if(!empty($product->loyaltyPoints))
            $data[self::LOYALTY_POINTS_COL] = $product->loyaltyPoints;

        return $data;
    }

    public static function toObject(Array $array)
    {

    }

}
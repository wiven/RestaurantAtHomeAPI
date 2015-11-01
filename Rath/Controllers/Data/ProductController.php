<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 19:39
 */

namespace Rath\Controllers\Data;


use Rath\Controllers\ControllerFactory;
use Rath\Controllers\Data\ControllerBase;
use Rath\Entities\ApiResponse;
use Rath\Entities\Order\Order;
use Rath\Entities\Order\OrderDetail;
use Rath\Entities\Product\Product;
use Rath\Entities\Product\ProductHasTags;
use Rath\Entities\Product\ProductStock;
use Rath\Entities\Product\ProductType;
use Rath\Entities\Product\RelatedProducts;
use Rath\Entities\Product\Tag;
use Rath\Helpers\General;
use Rath\Helpers\PhotoManagement;
use Rath\Libraries\UploadHandler;

class ProductController extends ControllerBase
{
    /**
     * @var UserController
     */
    private $uc;

    /**
     * ProductController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->uc = DataControllerFactory::getUserController();
    }


    //region General

    /**
     * @param $id int
     * @return array|bool
     */
    public function getProduct($id){
        $prod = $this->db->get(Product::TABLE_NAME,
                "*"
            ,
            [
                Product::ID_COL => $id
            ]
        );

        $prod[Product::PHOTO_COL] = PhotoManagement::getPhotoUrls($prod[Product::PHOTO_COL]);
        return $prod;
    }

    /**
     * @param $product Product
     * @return array|bool
     */
    public function addProduct($product){
        $this->uc->checkUserHasRestaurant($product->restaurantId,true);

        $lastId = $this->db->insert(Product::TABLE_NAME,
            Product::toDbArray($product)
        );
        if($lastId != 0)
            return $this->getProduct($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $product Product
     * @return array
     */
    public function updateProduct($product){
        $this->uc->checkUserHasProduct($product->id,true);
        $this->uc->checkUserHasRestaurant($product->restaurantId,true);

        $change = $this->db->update(Product::TABLE_NAME,
            Product::toDbArray($product),
            [
                Product::ID_COL => $product->id
            ]
        );
        if($change == 0)
            return $this->db->error();
        else
            return $this->getProduct($product->id);
    }

    public function deleteProduct($id){
        $this->db->delete(Product::TABLE_NAME,
            [
                Product::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region Product Tags
    /**
     * @param $productId
     * @param $tagId
     * @return array
     */
    public function addProductTag($productId, $tagId)
    {
        $this->db->insert(ProductHasTags::TABLE_NAME,
            [
                ProductHasTags::PRODUCT_ID_COL => $productId,
                ProductHasTags::TAG_ID_COL => $tagId
            ]);
        return $this->db->error();
    }

    /**
     * @param $productId
     * @return array|bool
     */
    public function getProductTags($productId)
    {
        return $this->db->select(ProductHasTags::TABLE_NAME,
            [
                "[><]".Tag::TABLE_NAME =>
                [
                    ProductHasTags::TAG_ID_COL => Tag::ID_COL
                ]
            ],
            [
                Tag::ID_COL,
                Tag::NAME_COL
            ],
            [
                ProductHasTags::PRODUCT_ID_COL => $productId
            ]);
    }

    public function deleteProductTag($productId, $tagId)
    {
        $this->db->delete(ProductHasTags::TABLE_NAME,
            [
                "AND"=>[
                    ProductHasTags::PRODUCT_ID_COL => $productId,
                    ProductHasTags::TAG_ID_COL => $tagId
                ]

            ]);
        return $this->db->error();
    }
    //endregion

    //region Product Stock
    /**
     * @param $prodId
     * @return array|bool
     */
    public function getProductStock($prodId)
    {
        return $this->db->select(ProductStock::TABLE_NAME,
            [
                ProductStock::ID_COL,
                ProductStock::DAY_OF_WEEK_COL,
                ProductStock::AMOUNT_COL
            ],
            [
                ProductStock::PRODUCT_ID_COL => $prodId
            ]);
    }

    /**
     * @param $prodId
     * @return array|bool
     */
    public function getProductStockForDay($prodId, $dayOfWeek)
    {
        return $this->db->select(ProductStock::TABLE_NAME,
            [
                ProductStock::ID_COL,
                ProductStock::DAY_OF_WEEK_COL,
                ProductStock::AMOUNT_COL
            ],
            [
                "AND" =>[
                    ProductStock::PRODUCT_ID_COL => $prodId,
                    ProductStock::DAY_OF_WEEK_COL => $dayOfWeek
                ]

            ]);
    }

    public function getSingleProductStock($id)
    {
        return $this->db->select(ProductStock::TABLE_NAME,
            "*",
            [
                ProductStock::ID_COL => $id
            ]);
    }

    /**
     * @param $prodStock ProductStock
     * @return array
     */
    public function addProductStock($prodStock)
    {
        $this->uc->checkUserHasProduct($prodStock->productId,true);

        $lastId = $this->db->insert(ProductStock::TABLE_NAME,
            ProductStock::toDbArray($prodStock));

        if($lastId != 0)
            return $this->getSingleProductStock($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $prodStock ProductStock
     * @return array
     */
    public function updateProductStock($prodStock)
    {
        $this->uc->checkUserHasProduct($prodStock->productId,true);

        $this->db->update(ProductStock::TABLE_NAME,
            ProductStock::toDbArray($prodStock),
            [
                ProductStock::ID_COL => $prodStock->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deletePoductStock($id)
    {
        $this->db->delete(ProductStock::TABLE_NAME,
            [
                ProductStock::ID_COL => $id
            ]);
        return $this->db->error();
    }

    public function getProductStockUsage($date, $productId)
    {
        $response = new ApiResponse();

        $stock = $this->getProductStockForDay($productId,General::getCurrentDayOfWeek());
        if(!isset($stock[ProductStock::ID_COL])){
            $response->code = 400;
            $response->message = "There is no stock for the product ".$productId." available.";
            return $response;
        }

        $result = $this->db->sum(OrderDetail::TABLE_NAME,
            [
                "[><]".Order::TABLE_NAME => [
                    OrderDetail::TABLE_NAME.".".OrderDetail::ORDER_ID_COL => Order::ID_COL
                ]
            ],
            [
                OrderDetail::TABLE_NAME.".".OrderDetail::QUANTITY_COL
            ],
            [
                "AND"=>
                [
                    "date(".Order::ORDER_DATETIME_COL.")" => $date,
                    OrderDetail::PRODUCT_ID_COL => $productId
                ]
            ]);

        if(gettype($result) == General::booleanType){
            $this->logMedooError();
            $this->logLastQuery();
            $response->code = 401;
            $response->message = "Something went wrong getting the product usage.";
            return $response;
        }

        $response->code= 200;
        $response->message = "product stock check success";
        $response->data = [
            "available" => $stock[ProductStock::AMOUNT_COL] - $result
        ];
        $this->log->debug($response);
        return $response;
    }
    //endregion

    //region Related Products
    public function getRelatedProducts($productId)
    {
        return $this->db->select(RelatedProducts::TABLE_NAME,
            [
                "[><]".Product::TABLE_NAME =>
                [
                    RelatedProducts::RELATED_PRODUCT_ID_COL => Product::ID_COL
                ]
            ],
            [
                Product::ID_COL,
                Product::PRODUCT_TYPE_ID,
                Product::NAME_COL,
                Product::DESCRIPTION_COL,
                Product::PRICE_COL,
                Product::SLOTS_COL
            ],
            [
                RelatedProducts::PRODUCT_ID_COL => $productId
            ]
            );
    }

    public function addRelatedProduct($productId, $relProdId)
    {
        $this->uc->checkUserHasProduct($relProdId,true);

        $this->db->insert(RelatedProducts::TABLE_NAME,
            [
                RelatedProducts::PRODUCT_ID_COL => $productId,
                RelatedProducts::RELATED_PRODUCT_ID_COL => $relProdId
            ]);
        return $this->db->error();
    }

    public function deleteRelatedProduct($prodId, $relProdId)
    {
        $this->uc->checkUserHasProduct($relProdId,true);

        $this->db->delete(RelatedProducts::TABLE_NAME,
            [
                "AND" =>
                [
                    RelatedProducts::PRODUCT_ID_COL => $prodId,
                    RelatedProducts::RELATED_PRODUCT_ID_COL => $relProdId
                ]
            ]);
        return $this->db->error();
    }
    //endregion

    //region Photo
    public function updateProductPhoto($id, $photoUrl)
    {
        $prod = $this->db->get(Product::TABLE_NAME,
            [
                Product::ID_COL,
                Product::PHOTO_COL
            ],
            [
                Product::ID_COL => $id
            ]);

        if($prod[Product::PHOTO_COL] <> "")
           PhotoManagement::deletePhoto($prod[Product::PHOTO_COL]);

//        var_dump($photoUrl);
        $this->db->update(Product::TABLE_NAME,
            [
                Product::PHOTO_COL => $photoUrl
            ],
            [
                Product::ID_COL => $id
            ]);

        return $this->getProduct($id);
    }
    //endregion

    //region Product Type (App Management)
    public function getProductTypes()
    {
        return $this->db->select(ProductType::TABLE_NAME,"*");
    }

    public function getProductType($id){
        return $this->db->select(ProductType::TABLE_NAME,
            "*",
            [
                ProductType::ID_COL => $id
            ]);
    }

    /**
     * @param $prodType ProductType
     * @return array
     */
    public function addProductType($prodType)
    {
        $this->db->insert(ProductType::TABLE_NAME,
            ProductType::toDbArray($prodType));
        return $this->db->error();
    }

    /**
     * @param $prodType ProductType
     * @return array
     */
    public function updateProductType($prodType)
    {
        $this->db->update(ProductType::TABLE_NAME,
            [
                ProductType::NAME_COL => $prodType->name
            ],
            [
                ProductType::ID_COL => $prodType->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deletePoductType($id)
    {
        $this->db->delete(ProductType::TABLE_NAME,
            [
                ProductType::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region Tag (Application Management)
    /**
     * @param $id int
     * @return array|bool
     */
    public function getTag($id){
        return $this->db->select(Tag::TABLE_NAME,
            "*",
            [
                Tag::ID_COL => $id
            ]);
    }

    /**
     * @return array|bool
     */
    public function getAllTags(){
        return $this->db->select(Tag::TABLE_NAME, "*");
    }

    /**
     * @param $tag
     * @return array
     */
    public function addTag($tag){
        $this->db->insert(Tag::TABLE_NAME,
            Tag::toDbArray($tag));
        return $this->db->error();
    }

    /**
     * @param $tag Tag
     * @return array
     */
    public function updateTag($tag)
    {
        $this->db->update(Tag::TABLE_NAME,
            [
                Tag::NAME_COL => $tag->name
            ],
            [
                Tag::ID_COL => $tag->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $id int
     * @return array
     */
    public function deleteTag($id){
        $this->db->delete(Tag::TABLE_NAME,
            [
                Tag::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion
}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 19:39
 */

namespace Rath\Controllers;


use Rath\Entities\Product\Product;
use Rath\Entities\Product\ProductHasTags;
use Rath\Entities\Product\ProductStock;
use Rath\Entities\Product\ProductType;
use Rath\Entities\Product\Tag;

class ProductController extends ControllerBase
{
    //region General
    /**
     * @param $id
     * @return array|bool
     */
    public function getProduct($id){
        return $this->db->select(Product::TABLE_NAME,

                "*"
            ,
            [
                Product::ID_COL => $id
            ]
        );
    }

    /**
     * @param $product Product
     * @return array|bool
     */
    public function addProduct($product){
        $lastId = $this->db->insert(Product::TABLE_NAME,
            Product::productToDbArray($product)
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
        $this->db->update(Product::TABLE_NAME,
            Product::productToDbArray($product),
            [
                Product::ID_COL => $product->id
            ]
        );
        return $this->db->error();
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
            ]);
    }

    public function deleteProductTag($productId, $tagId)
    {
        $this->db->delete(ProductHasTags::TABLE_NAME,
            [
                ProductHasTags::PRODUCT_ID_COL => $productId,
                ProductHasTags::TAG_ID_COL => $tagId
            ]);
        return $this->db->error();
    }
    //endregion

    //region Product Stock
    public function getProductStock($prodId)
    {
        $this->db->select(ProductStock::TABLE_NAME,
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
     * @param $prodStock ProductStock
     * @return array
     */
    public function addProductStock($prodStock)
    {
        $this->db->insert(ProductStock::TABLE_NAME,
            ProductStock::toDbArray($prodStock));
        return $this->db->error();
    }

    /**
     * @param $prodStock ProductStock
     * @return array
     */
    public function updateProductStock($prodStock)
    {
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
    //endregion

    //region Product Type (App Management)
    public function getProductTypes()
    {
        $this->db->select(ProductType::TABLE_NAME,"*");
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
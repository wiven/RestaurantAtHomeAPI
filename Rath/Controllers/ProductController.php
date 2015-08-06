<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 19:39
 */

namespace Rath\Controllers;


use Rath\Entities\Product\Product;

class ProductController extends ControllerBase
{
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
}
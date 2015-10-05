<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 10/08/2015
 * Time: 18:46
 */

namespace Rath\Controllers\Data;


use Rath\Entities\Order\Order;
use Rath\Entities\Order\OrderDetail;
use Rath\Entities\Product\Product;
use Rath\Exceptions\OrderDetailException;
use Rath\Helpers\General;
use Rath\Entities\DynamicClass;

class OrderController extends ControllerBase
{
    //Todo: Order manipulation should return the order?
    //TODO: change the field subset to better suite UI.

    //region Order
    /**
     * @param $order Order
     * @return array|bool
     */
    public function createOrder($order)
    {
        unset($order->submitted); //ensure no unpaid orders get through
        $lastId = $this->db->insert(Order::TABLE_NAME,
            Order::toDbArray($order));
        if($lastId != 0)
            return $this->getOrder($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $id
     * @return bool | array
     */
    public function getOrder($id)
    {
        return $this->db->get(Order::TABLE_NAME,
            "*",
            [
                Order::ID_COL => $id
            ]);
    }

    public function getOrderWithLines($id)
    {
        $order = $this->getOrder($id);
        $order["lines"] = $this->getOrderLines($id);
        return $order;
    }

    /**
     * @param $order Order
     * @return array
     */
    public function updateOrder($order)
    {
        $this->db->update(Order::TABLE_NAME,
            Order::toDbUpdateArray($order),
            [
                Order::ID_COL => $order->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $id
     * @param $submitted boolean
     * @return array
     */
    public function updateOrderSubmitState($id,$submitted)
    {
        $this->db->update(Order::TABLE_NAME,
            [
                Order::SUBMITTED_COL => $submitted
            ],
            [
                Order::ID_COL => $id
            ]);
        return $this->db->error();
    }

    public function deleteOrder($id)
    {
        $this->db->delete(Order::TABLE_NAME,
            [
                Order::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region OrderDetail
    /**
     * @param $orderLine OrderDetail
     * @return array|bool
     */
    public function addOrderDetailLine($orderLine)
    {
        $this->checkOrderLinePrice($orderLine);
        $lastId = $this->db->insert(OrderDetail::TABLE_NAME,
            OrderDetail::toDbArray($orderLine));

        if($lastId != 0)
            return $this->getOrderDetailLine($lastId);
        else
            return $this->db->error();
    }

    public function getOrderDetailLine($id)
    {
        return $this->db->get(OrderDetail::TABLE_NAME,
            "*",
            [
                OrderDetail::ID_COL => $id
            ]);
    }

    public function getOrderLines($orderId)
    {
        return $this->db->select(OrderDetail::TABLE_NAME,
            "*",
            [
                OrderDetail::ORDER_ID_COL => $orderId
            ]);
    }

    /**
     * @param $orderLine OrderDetail
     * @return array
     * @throws OrderDetailException
     */
    public function updateOrderDetailLine($orderLine)
    {
        $this->checkOrderLinePrice($orderLine);
        $this->db->update(OrderDetail::TABLE_NAME,
            OrderDetail::toDbArray($orderLine),
            [
                OrderDetail::ID_COL => $orderLine->id
            ]);
        return $this->db->error();
    }

    public function deleteOrderDetailLine($id)
    {
        $this->db->delete(OrderDetail::TABLE_NAME,
            [
                OrderDetail::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    /**
     * @param $orderLine OrderDetail
     * @throws OrderDetailException
     * @throws \Exception
     */
    private function checkOrderLinePrice($orderLine)
    {
        $prod = DataControllerFactory::getProductController();

        $prodArray = $prod->getProduct($orderLine->productId);
        if(gettype($prodArray) != General::arrayType or empty($prodArray))
            throw new \Exception("Product doesn't exist");

        $product = new DynamicClass($prodArray);

        if($product->price != $orderLine->unitPrice)
            throw new OrderDetailException("Product & Unit price don't match.");

        $lineTotal = bcmul($orderLine->unitPrice,$orderLine->quantity);
//        var_dump($lineTotal);
//        var_dump($orderLine->lineTotal);
//        var_dump($lineTotal != $orderLine->lineTotal);
        if($lineTotal != $orderLine->lineTotal)
            throw new OrderDetailException("Order Detail total isn't correct.");
    }
}
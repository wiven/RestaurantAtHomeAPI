<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 10/08/2015
 * Time: 18:46
 */

namespace Rath\Controllers\Data;


use Rath\Entities\Order\Coupon;
use Rath\Entities\Order\Order;
use Rath\Entities\Order\OrderDetail;
use Rath\Entities\Product\Product;
use Rath\Entities\Slots\SlotTemplate;
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

    /**
     * @param $id
     * @return bool | array
     */
    public function getOrderPublic($id,$includeIds = false)
    {
        $fields = [
            Order::TABLE_NAME.".".Order::ID_COL,
            Order::TABLE_NAME.".".Order::ORDER_STATUS_ID_COL,
            Order::TABLE_NAME.".".Order::AMOUNT_COL,
            Order::TABLE_NAME.".".Order::ORDER_DATETIME_COL,
            Order::TABLE_NAME.".".Order::COMMENT_COL,
            Order::TABLE_NAME.".".Order::COUPON_ID,
            Order::TABLE_NAME.".".Order::SUBMITTED_COL,
            Order::TABLE_NAME.".".Order::CREATION_DATE_TIME_COL,
            Order::TABLE_NAME.".".Order::SLOT_TEMPLATE_ID_COL,
            SlotTemplate::FROM_TIME_COL."(slotFromTime)",
            SlotTemplate::TO_TIME_COL."(slotToTime)"
        ];

        if($includeIds){
            array_push($fields,Order::ADDRESS_ID_COL);
            array_push($fields,Order::USER_ID_COL);
            array_push($fields,Order::MOLLIE_ID_COL);
        }

        $order = $this->db->get(Order::TABLE_NAME,
            [
                "[>]".SlotTemplate::TABLE_NAME =>[
                    Order::TABLE_NAME.".".Order::SLOT_TEMPLATE_ID_COL => SlotTemplate::ID_COL
                ]
            ],
            $fields,
            [
                Order::TABLE_NAME.".".Order::ID_COL => $id
            ]);
        //$this->log->debug($this->db->last_query());
        return $order;
    }

    public function getOrderDetail($id)
    {
        $mc = DataControllerFactory::getMollieInfoController();
        $uc = DataControllerFactory::getUserController();
        $gc = DataControllerFactory::getGeneralController();
        $cc = DataControllerFactory::getCouponController();

        $order = $this->getOrderPublic($id,true);

        if(!isset($order[Order::ID_COL]))
            return [];

        $order["lines"] = $this->getOrderLines($id);

        if(isset($order[Order::MOLLIE_ID_COL]))
            if($order[Order::MOLLIE_ID_COL] != null)
                $order["paymentInfo"] = $mc->getMollieInfoPublic($order[Order::MOLLIE_ID_COL]);
            else
                $order["paymentInfo"] = "Cash";

        if(isset($order[Order::COUPON_ID]))
            if($order[Order::COUPON_ID] != null)
                $order["couponDetail"] = $cc->getCoupon(Order::COUPON_ID);
            else
                $order["couponDetail"] = null;

        if(isset($order[Order::USER_ID_COL]))
            $order["userDetails"] = $uc->getUserDetails($order[Order::USER_ID_COL]);
        if(isset($order[Order::ADDRESS_ID_COL]))
            $order["addressDetails"] = $gc->getAddress($order[Order::ADDRESS_ID_COL]);

        unset($order[Order::ADDRESS_ID_COL]);
        unset($order[Order::USER_ID_COL]);
        unset($order[Order::MOLLIE_ID_COL]);
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
            [
                "[><]".Product::TABLE_NAME =>[
                    OrderDetail::PRODUCT_ID_COL => Product::ID_COL
                ]
            ],
            [
                OrderDetail::ORDER_ID_COL,
                OrderDetail::TABLE_NAME.".".OrderDetail::ID_COL,
                OrderDetail::PRODUCT_ID_COL,
                Product::NAME_COL,
                OrderDetail::QUANTITY_COL,
                OrderDetail::UNIT_PRICE_COL,
                OrderDetail::LINE_TOTAL_COL
            ],
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
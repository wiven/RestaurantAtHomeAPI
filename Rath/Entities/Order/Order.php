<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 17:53
 */

namespace Rath\Entities\Order;


class Order
{
    const TABLE_NAME = "order";

    const ID_COL = "id";
    const USER_ID_COL = "userId";
    const RESTAURANT_ID_COL = "restaurantId";
    const ORDER_STATUS_ID_COL = "orderStatusId";
    const AMOUNT_COL = "amount";
    const ORDER_DATETIME_COL = "orderDateTime";
    const COMMENT_COL = "comment";
    const ADDRESS_ID_COL = "addressId";
    const COUPON_ID = "couponId";
    const CREATION_DATE_TIME_COL = "creationDateTime";
    const SUBMITTED_COL = "submitted";

    public $id;
    public $userId;
    public $restaurantId;
    public $orderStatusId;
    public $amount;
    public $orderDateTime;
    public $comment;
    public $addressId;
    public $couponId;
    public $creationDateTime;
    public $submitted;

    public $lines;

    /**
     * @param $order Order
     * @return array
     */
    public static function toDbArray($order)
    {
        $data =[
            Order::USER_ID_COL => $order->userId,
            Order::RESTAURANT_ID_COL => $order->restaurantId,
            Order::ORDER_STATUS_ID_COL => $order->orderStatusId,
            Order::AMOUNT_COL => $order->amount,
            Order::ORDER_DATETIME_COL => $order->orderDateTime,
            Order::COMMENT_COL => $order->comment,
            Order::ADDRESS_ID_COL => $order->addressId,
            Order::CREATION_DATE_TIME_COL => date("Y-m-d H:i:s")
        ];

        if(!empty($order->couponId))
            $data[Order::COUPON_ID] = $order->couponId;

        //not allowed through api!
        //if(!empty($order->submitted))
            //$data[Order::SUBMITTED_COL] = $order->submitted;

        return $data;
    }
}

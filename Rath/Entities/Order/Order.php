<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 17:53
 */

namespace Rath\Entities\Order;


use DateTime;
use Rath\Controllers\Data\DataControllerFactory;
use Rath\Entities\EntityBase;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Slim\Middleware\Authorization;

class Order extends EntityBase
{
    const TABLE_NAME = "orders"; //change for syntax problems in sql

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
    const SLOT_TEMPLATE_ID_COL = "slottemplateId";
    const MOLLIE_ID_COL = "mollieinfoid";
    const PAYMENT_METHOD_ID = "paymentmethodid";
    const PAYMENT_STATUS_COL = "paymentStatus";
        const PAYMENT_STATUS_VAL_PENDING = "Pending";
        const PAYMENT_STATUS_VAL_PAYED = "Payed";

    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $restaurantId;
    /**
     * @var int
     */
    public $orderStatusId;
    /**
     * @var float
     */
    public $amount;
//    /**
//     * @var DateTime | null
//     */
    public $orderDateTime;
    /**
     * @var string
     */
    public $comment;
    /**
     * @var int
     */
    public $addressId;
    /**
     * @var int | null
     */
    public $couponId;
//    /**
//     * @var DateTime | null
//     */
    public $creationDateTime;
    /**
     * @var bool
     */
    public $submitted;
    /**
     * @var int
     */
    public $slottemplateId;
    /**
     * @var int
     */
    public $mollieinfoid;
    /**
     * @var int
     */
    public $paymentmethodid;

    /**
     * @var string | null
     */
    public $paymentStatus;

    public $lines;

    /**
     * @param $order Order
     * @return string
     */
    public static function getOrderDescription($order)
    {
        $rc = DataControllerFactory::getRestaurantController();
        $resto = $rc->getRestaurant($order->restaurantId);
        return "Order ".$order->id." - ".$resto[Restaurant::NAME_COL]." - ".$order->amount." EURO";
    }

    /**
     * @param $order Order
     * @return array
     */
    public static function toDbArray($order)
    {
        $data =[
            Order::USER_ID_COL => Authorization::$userId,
            Order::RESTAURANT_ID_COL => $order->restaurantId,
            Order::ORDER_STATUS_ID_COL => $order->orderStatusId,
//            Order::AMOUNT_COL => $order->amount,
//            Order::ORDER_DATETIME_COL => $order->orderDateTime,
//            Order::COMMENT_COL => $order->comment,
//            Order::ADDRESS_ID_COL => $order->addressId,
            Order::CREATION_DATE_TIME_COL => date("Y-m-d H:i:s")
        ];

        if(!empty($order->couponId))
            $data[Order::COUPON_ID] = $order->couponId;

        if(!empty($order->slottemplateId))
            $data[self::SLOT_TEMPLATE_ID_COL] = $order->slottemplateId;

        if(!empty($order->mollieinfoid))
            $data[self::MOLLIE_ID_COL] = $order->mollieinfoid;

        if(!empty($order->paymentmethodid))
            $data[self::PAYMENT_METHOD_ID] = $order->paymentmethodid;

        //not allowed through api!
        //if(!empty($order->submitted))
            //$data[Order::SUBMITTED_COL] = $order->submitted;

        return $data;
    }

    /**
     * @param $order Order
     * @param bool $submit
     * @return array
     */
    public static function toDbUpdateArray($order,$submit = false)
    {
        $data = [];

        if(!empty($order->addressId))
            $data[Order::ADDRESS_ID_COL] = $order->addressId;

        if(!empty($order->comment))
            $data[Order::COMMENT_COL] = $order->comment;

        if(!empty($order->orderDateTime))
            $data[Order::ORDER_DATETIME_COL] = $order->orderDateTime;

        if(!empty($order->amount))
            $data[Order::AMOUNT_COL] = $order->amount;

        if(!empty($order->orderStatusId))
            $data[Order::ORDER_STATUS_ID_COL] = $order->orderStatusId;

        if(!empty($order->couponId))
            $data[Order::COUPON_ID] = $order->couponId;

        if(!empty($order->slottemplateId))
            $data[self::SLOT_TEMPLATE_ID_COL] = $order->slottemplateId;

        if(!empty($order->mollieinfoid))
            $data[self::MOLLIE_ID_COL] = $order->mollieinfoid;

        if(!empty($order->paymentmethodid))
            $data[self::PAYMENT_METHOD_ID] = $order->paymentmethodid;



        if($submit)
        {
            if(!empty($order->submitted))
                $data[self::SUBMITTED_COL] = $order->submitted;
            if(!empty($order->paymentStatus))
                $data[self::PAYMENT_STATUS_COL] = $order->paymentStatus;

        }

        return $data;
    }
}

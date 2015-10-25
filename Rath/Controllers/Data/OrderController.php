<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 10/08/2015
 * Time: 18:46
 */

namespace Rath\Controllers\Data;


use Rath\Controllers\PaymentController;
use Rath\Entities\ApiResponse;
use Rath\Entities\General\Address;
use Rath\Entities\Order\Coupon;
use Rath\Entities\Order\Order;
use Rath\Entities\Order\OrderDetail;
use Rath\Entities\Order\OrderStatus;
use Rath\Entities\Product\Product;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Restaurant\PaymentMethod;
use Rath\Entities\Slots\SlotTemplate;
use Rath\Exceptions\OrderDetailException;
use Rath\Helpers\General;
use Rath\Entities\DynamicClass;
use Rath\Slim\Middleware\Authorization;

class OrderController extends ControllerBase
{
    /**
     * @var ProductController
     */
    private $pc;

    /**
     * OrderController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pc = DataControllerFactory::getProductController();
    }



    //Todo: Order manipulation should return the order?
    //TODO: change the field subset to better suite UI.

    //region Order
    /**
     * @param $order Order
     * @return array|bool
     */
    public function createOrder($order)
    {
        $order->submitted = false;
        $order->orderStatusId = OrderStatus::val_New;

        $lastId = $this->db->insert(Order::TABLE_NAME,
            Order::toDbArray($order));
        if($lastId != 0)
            return $this->getOrderDetail($lastId);
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
            Order::TABLE_NAME.".".Order::RESTAURANT_ID_COL,
            Order::TABLE_NAME.".".Order::ORDER_STATUS_ID_COL,
            Order::TABLE_NAME.".".Order::AMOUNT_COL,
            Order::TABLE_NAME.".".Order::ORDER_DATETIME_COL,
            Order::TABLE_NAME.".".Order::COMMENT_COL,
            Order::TABLE_NAME.".".Order::COUPON_ID,
            Order::TABLE_NAME.".".Order::SUBMITTED_COL,
            Order::TABLE_NAME.".".Order::CREATION_DATE_TIME_COL,
            Order::TABLE_NAME.".".Order::PAYMENT_METHOD_ID,
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

    public function getOrderDetail($id, $full = false)
    {
        $apiResponse = new ApiResponse();
        $order = $this->getOrderPublic($id,true);

        if(!isset($order[Order::ID_COL])){
            $apiResponse->code = 404;
            $apiResponse->message = "order could not be found with id: ".(string)$id;
            return $apiResponse;
        }
        /** @var Order $order */
        $order = Order::fromJson($order);

        /** @var OrderDetail[] $lines */
        $lines = $this->getOrderLines($id);
        $orderTotal = from($lines)
            ->sum(function($line){
                /* @var OrderDetail $line */
                return $line->lineTotal;
            });

        if($order->amount != $orderTotal){
            $order->amount = $orderTotal;
            $this->updateOrder($order);
        }

        $order->lines = $lines;

        if($full)
        {
            $mc = DataControllerFactory::getMollieInfoController();
            $uc = DataControllerFactory::getUserController();
            $gc = DataControllerFactory::getGeneralController();
            $cc = DataControllerFactory::getCouponController();

            if($order->mollieinfoid != 0)
                $order->paymentInfo = $mc->getMollieInfoPublic($order->mollieinfoid);
            else
                $order->paymentInfo = null;


            if($order->couponId != 0)
                $order->couponDetail = $cc->getCoupon($order->couponId);
            else
                $order->couponDetail = null;


            $order->userDetails = $uc->getUserDetails($order->userId);

            if($order->addressId != 0)
                $order->addressDetail = $gc->getAddress($order->addressId);
            else
                $order->addressDetail = null;

            $this->log->debug($order);

            unset($order->addressId);
            unset($order->mollieinfoid);
        }
        unset($order->userId);

        return $order;
    }

    /**
     * @param $order Order
     * @param bool $submit
     * @return array
     */
    public function updateOrder($order, $submit = false)
    {
        $this->db->update(Order::TABLE_NAME,
            Order::toDbUpdateArray($order,$submit),
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

    /**
     * @param $order Order
     * @return ApiResponse
     */
    public function submitOrder($order)
    {
        $response = new ApiResponse();
        $error = false;

        /** @var Order $dbOrder */
        $dbOrder = $this->getOrder($order->id);
        $this->log->debug($dbOrder);
        if(isset($dbOrder[Order::ID_COL]))
        {
            $dbOrder = Order::fromJson($dbOrder);
        }
        else {
            $error = true;
        }

        if($dbOrder->paymentStatus != null or $error)
        {
            $response->code = 400;
            $response->message = "Order already submitted";
            return $response;
        }

        //Transfer new info
        $dbOrder->comment = $order->comment;
        $dbOrder->addressId = $order->addressId;
        $dbOrder->couponCode = $order->couponCode;
        $dbOrder->orderDateTime = $order->orderDateTime;
        $dbOrder->paymentmethodid = $order->paymentmethodid;

        if(!$this->updateFinalizationData($dbOrder,$response))
            return $response;

        $links = null;
        if($dbOrder->paymentmethodid != PaymentMethod::CASH_PAYMENT_ID)
        {
            $pc = new PaymentController();
            $links = $pc->CreateMollieTransaction($dbOrder);
            if($links != null)
            {
                $dbOrder->paymentStatus = Order::PAYMENT_STATUS_VAL_PENDING;
                $this->updateOrder($dbOrder,true);
            }else{
                $response->code = 500;
                $response->message = "Something went wrong in submitting the order";
                return $response;
            }
        }
        else
            $dbOrder->submitted = true;

        $dbOrder->paymentStatus = Order::PAYMENT_STATUS_VAL_PENDING;
        $this->updateOrder($dbOrder,true);

        $response->code = 200;
        $response->message = "Order submitted succesfully";
        $response->data = $links;

        return $response;
    }

    public function checkOrderPayment($id)
    {
        $response = new ApiResponse();

        /** @var Order $order */
        $order = $this->getOrderPublic($id);
        if(isset($order[Order::ID_COL]))
        {
            $order = Order::fromJson($order);
        } else
        {
            $response->code = 400;
            $response->message = "Order could not be found";
            return $response;
        }

        if($order->paymentStatus == Order::PAYMENT_STATUS_VAL_PAYED){
            $response->code = 200;
            $response->message = "Your order has been payed";
            $response->data = $order;
            return $response;
        }

        $response->code = 400;
        $response->message = "Your order hasn't been payed";
        $response->data = $order;
        return $response;
    }

    /**
     * @param $order Order
     * @param $response ApiResponse
     * @return bool
     */
    public function updateFinalizationData(&$order,&$response)
    {
        $gc = DataControllerFactory::getGeneralController();
        $rc = DataControllerFactory::getRestaurantController();

        $this->log->debug($order);
        //check addressId
        /** @var Address $address */
        $address = $gc->getAddress($order->addressId,false);
        $this->log->debug($address);
        if(isset($address[Address::ID_COL]))
            $address = Address::fromJson($address);
        else{
            $response->code = 300;
            $response->message = "Address could not be found.";
            return false;

        }
        if($address->userId != Authorization::$userId){
            $response->code = 301;
            $response->message = "Address doesn't belong to the user";
            return false;
        }

        //Check order date Time & slots
        if(empty($order->orderDateTime)){
            $response->code = 310;
            $response->message = "No order date & time supplied";
            return false;
        }
        if($order->orderDateTime < General::getCurrentDateTime()){
            $response->code = 313;
            $response->message = "You cannot order in the past.";
            return false;
        }
        $orderDT = new \DateTime($order->orderDateTime);
        /** @var SlotTemplate $restoSlot */
        $restoSlot = $rc->getSlotOverview($order->restaurantId,$orderDT->format(General::dateFormat),$orderDT->format(General::timeFormat));
        if(isset($restoSlot[SlotTemplate::ID_COL]))

            $restoSlot = SlotTemplate::fromJson($restoSlot);
        else{
            $response->code = 311;
            $response->message = "there are no slots available on this time";
            return false;
        }
        $order->slottemplateId = $restoSlot->id;

        /** @var int $orderWeight */
        $orderWeight = $this->getSlotWeight($order->id);
        /** @var int $slotUsage */
        $slotUsage = $rc->getSlotUsage($order);
        if($restoSlot->getSlotAvailability() < ($slotUsage + $orderWeight))
        {
            $response->code = 312;
            $response->message = "The selected slot is full.";
            return false;
        }

        //Check Coupon
        $cc = DataControllerFactory::getCouponController();
        /** @var Coupon $coupon */
        $coupon = $cc->checkCodeIsValid($order->couponCode,$order->restaurantId);
        if($coupon == null){
            $response->code = 320;
            $response->message = "Invalid coupon code";
            return false;
        }

        $this->log->debug($coupon);
        if($cc->getCouponUsage($coupon->id) < $coupon->quantity)
            $order->couponId = $coupon->id;
        else{
            $response->code = 321;
            $response->message = "Coupon code used up";
            return false;
        }

        //check paymentMethod
        if(!$rc->getRestaurantHasPaymentMethod($order->restaurantId,$order->paymentmethodid)){
            $response->code = 330;
            $response->message = "Selected paymentmethod isn't allowed";
            return false;
        }

        $this->log->debug("Validated order before update");
        $this->log->debug($order);
        $this->updateOrder($order);
        //TODO: Product stock


        return true;
    }
    //endregion

    //region OrderDetail (lines)
    /**
     * @param $orderLine OrderDetail
     * @return array|bool
     */
    public function addOrderDetailLine($orderLine)
    {
        $apiResponse = new ApiResponse();

        /** @var Product $product */
        $product = $this->pc->getProduct($orderLine->productId);
        $this->log->debug($product);
        if(!isset($product[Product::ID_COL])){
            $apiResponse->code = 406;
            $apiResponse->message = "The product id isn't known.";
            return $apiResponse;
        }
        $product = Product::fromJson($product);

        /** @var Order $order */
        $order = $this->getOrder($orderLine->orderId);
        $this->log->debug($order);
        if(isset($order[Order::ID_COL])){
            $order= Order::fromJson($order);
            if($order->restaurantId != $product->restaurantId){
                $apiResponse->code = 406;
                $apiResponse->message = "You can only buy products from one restaurant in one order.";
                return $apiResponse;
            }
            if($order->paymentStatus != null || $order->submitted){
                $apiResponse->code = 407;
                $apiResponse->message = "Order is submitted and cannot be changed";
                return $apiResponse;
            }
        }
        else{
            $apiResponse->code = 404;
            $apiResponse->message = "Supplied order No not found.";
            return $apiResponse;
        }


        /** @var OrderDetail $dbOrderLine */
        $dbOrderLine = $this->updateOrderDetailLineByLineInfo($orderLine);
        $this->log->debug($dbOrderLine);
        if(isset($dbOrderLine->id)){
            $orderLine->id = $dbOrderLine->id;
            if($dbOrderLine->promotionValid())
                $orderLine->quantity = $orderLine->quantity + $dbOrderLine->quantity;
            if($orderLine->quantity < 0){
                $apiResponse->code = 406;
                $apiResponse->message = "The quantity provided will result in a negative value on a order line.";
                return $apiResponse;
            }

            $this->updateOrderDetailLine($orderLine);
        }
        else{
            $this->log->debug($orderLine);
            $lastId = $this->db->insert(OrderDetail::TABLE_NAME,
                OrderDetail::toDbArray($orderLine));

            if($lastId != 0)
                return $this->getOrderDetail($order->id);
            else
                return $this->db->error();
        }

        return $this->getOrderDetail($order->id);
    }

    public function getOrderDetailLine($id)
    {
        return $this->db->get(OrderDetail::TABLE_NAME,
            "*",
            [
                OrderDetail::ID_COL => $id
            ]);
    }

    /**
     * @param $orderDetail OrderDetail
     * @return bool
     */
    public function updateOrderDetailLineByLineInfo(&$orderDetail)
    {
        $result = $this->db->get(OrderDetail::TABLE_NAME,
            [
                "[>]".Promotion::TABLE_NAME =>[
                    Product::TABLE_NAME.".".Product::PROMOTION_ID_COL=> Promotion::ID_COL
                ]
            ],
            [
                OrderDetail::TABLE_NAME.".".OrderDetail::ID_COL,
                OrderDetail::TABLE_NAME.".".OrderDetail::QUANTITY_COL,
                Promotion::TABLE_NAME.".".Promotion::FROM_DATE_COL,
                Promotion::TABLE_NAME.".".Promotion::TO_DATE_COL
            ],
            [
                "AND" => [
                    OrderDetail::ORDER_ID_COL => $orderDetail->orderId,
                    OrderDetail::PRODUCT_ID_COL => $orderDetail->productId
                ]
            ]);


        if(isset($result[OrderDetail::ID_COL])){
            return OrderDetail::fromJson($result);
        }
        return false;
    }

    public function getOrderLines($orderId)
    {
        $where = [
            "AND" => [
                OrderDetail::ORDER_ID_COL => $orderId
            ]
        ];
        //$this->addDefaultPromotionFilters($where); doesn't show product if a promotion is passed!

        /** @var OrderDetail[] $orderDetails */
        $orderDetails =  $this->db->select(OrderDetail::TABLE_NAME,
            [
                "[><]".Product::TABLE_NAME =>[
                    OrderDetail::PRODUCT_ID_COL => Product::ID_COL
                ],
                "[>]".Promotion::TABLE_NAME =>[
                    Product::TABLE_NAME.".".Product::PROMOTION_ID_COL=> Promotion::ID_COL
                ]
            ],
            [
                OrderDetail::ORDER_ID_COL,
                OrderDetail::TABLE_NAME.".".OrderDetail::ID_COL,
                OrderDetail::PRODUCT_ID_COL,
                Product::TABLE_NAME.".".Product::NAME_COL,
                Product::PRICE_COL,
                OrderDetail::QUANTITY_COL,
                Promotion::TABLE_NAME.".".Promotion::ID_COL."(promotionId)",
                Promotion::TABLE_NAME.".".Promotion::DISCOUNT_TYPE_COL,
                Promotion::TABLE_NAME.".".Promotion::DISCOUNT_AMOUNT_COL
            ],
            $where
            );

        $this->logLastQuery();
        $this->logMedooError();
        $this->log->debug($orderDetails);
        if(count($orderDetails) != 0)
            $orderDetails = OrderDetail::fromJsonArray($orderDetails);
        else
            return [];

        foreach($orderDetails as $detail){
            //check price (promotion?)
            if($detail->discountType != null){
                $detail->oldPrice = $detail->price;
                switch($detail->discountType){
                    case Promotion::DISCOUNT_TYPE_VAL_PERS:
                        $mul = 1 - bcdiv($detail->discountAmount,100);
                        $detail->price = bcmul($detail->price, $mul);
                        break;
                    case Promotion::DISCOUNT_TYPE_VAL_AMOUNT:
                        $detail->price -= $detail->discountAmount;
                }
                if($detail->price < 0)
                    $detail->price = 0;
            }

            $detail->lineTotal = bcmul($detail->price,$detail->quantity);
        }
        return $orderDetails;
    }

    /**
     * @param $where array
     */
    public function addDefaultPromotionFilters(&$where)
    {
        $date = General::getCurrentDate();
        $where["AND"]["OR #from"] = [
            "OR #fromIsData" => [
                Promotion::TABLE_NAME.".".Promotion::FROM_DATE_COL."[<=]" => $date
            ],
            "OR #fromIsNull" => [
                Promotion::TABLE_NAME.".".Promotion::FROM_DATE_COL => null
            ]
        ];
        $where["AND"]["OR #to"] = [
            "OR #fromIsData" => [
                Promotion::TABLE_NAME.".".Promotion::TO_DATE_COL."[>=]" => $date
            ],
            "OR #fromIsNull" => [
                Promotion::TABLE_NAME.".".Promotion::TO_DATE_COL => null
            ]
        ];
    }
    /**
     * @param $orderLine OrderDetail
     * @return array
     * @throws OrderDetailException
     */
    public function updateOrderDetailLine($orderLine)
    {
//        $this->checkOrderLinePrice($orderLine);
        $this->db->update(OrderDetail::TABLE_NAME,
            OrderDetail::toDbArray($orderLine),
            [
                "AND"=> [
                    OrderDetail::ID_COL => $orderLine->id,
                    OrderDetail::ORDER_ID_COL => $orderLine->orderId
                ]
            ]);
        return $this->getOrderDetail($orderLine->orderId);
    }

    public function deleteOrderDetailLine($orderId,$id)
    {
        $response = new ApiResponse();

        /** @var Order $order */
        $order = $this->getOrder($orderId);
        if(isset($order[Order::ID_COL]))
            $order = Order::fromJson($order);

        if($order->submitted or $order->paymentStatus != null)
        {
            $response->code = 406;
            $response->message = "This line cannot be deteled.";
            return $response;
        }

        $changes = $this->db->delete(OrderDetail::TABLE_NAME,
            [
                "AND"=>[
                    OrderDetail::ID_COL => $id,
                    OrderDetail::ORDER_ID_COL => $orderId
                ]
            ]);


        if($changes == 0) {
            $response->code = 406;
            $response->message = "Deletion failed.";
            $response->data = $this->db->error();
            return $response;
        }

        return $this->getOrderDetail($orderId);
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


    /**
     * @param $orderId int
     * @return bool|int
     */
    public function getSlotWeight($orderId)
    {
        //TODO: replace with constants
        $query =
            "SELECT SUM(product.slots * orderdetail.quantity) as total FROM orders
            INNER JOIN orderdetail ON orders.id = orderdetail.orderId
            INNER JOIN product ON orderdetail.productId = product.id
            WHERE orders.id = ".$orderId.";";

        $pdoQuery = $this->db->query($query);
        $result = $pdoQuery->fetchColumn(0);

        $this->logLastQuery();
        $this->logMedooError();
        $this->log->debug($result);
        return $result;
    }
}
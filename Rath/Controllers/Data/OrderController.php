<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 10/08/2015
 * Time: 18:46
 */

namespace Rath\Controllers\Data;


use Exception;
use Rath\Controllers\PaymentController;
use Rath\Entities\ApiResponse;
use Rath\Entities\General\Address;
use Rath\Entities\Order\Coupon;
use Rath\Entities\Order\MollieInfo;
use Rath\Entities\Order\Order;
use Rath\Entities\Order\OrderDetail;
use Rath\Entities\Order\OrderStatus;
use Rath\Entities\Product\Product;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Restaurant\PaymentMethod;
use Rath\Entities\Restaurant\Restaurant;
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
     * @var UserController
     */
    private $uc;

    /**
     * OrderController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pc = DataControllerFactory::getProductController();
        $this->uc = DataControllerFactory::getUserController();
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
        $result = $this->uc->getUserActiveOrder();
        if(!isset($result->code))
        {
            $response = new ApiResponse();
            $response->code = 400;
            $response->message = "There is still an open order.";
            return $response;
        }

        $order->submitted = false;
        $order->orderStatusId = OrderStatus::val_New;

        // Cost is 0 by default so don't need to set it otherwise
//        if ($order->needsDelivery != Order::DELIVERY_NONE) {
//            $order->deliveryCost = $this->getRestaurantDeliveryPrice($order);
//        }

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
            Order::TABLE_NAME.".".Order::PAYMENT_STATUS_COL,
//            Order::TABLE_NAME.".".Order::DELIVERY_COL,
//            Order::TABLE_NAME.".".Order::DELIVERY_COST_COL,
            SlotTemplate::FROM_TIME_COL."(slotFromTime)",
            SlotTemplate::TO_TIME_COL."(slotToTime)"
        ];

        if($includeIds){
            array_push($fields,Order::ADDRESS_ID_COL);
            array_push($fields,Order::USER_ID_COL);
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

        $cc = DataControllerFactory::getCouponController();
        if($order->couponId != 0){
            /** @var Coupon $coupon */
            $coupon = $cc->getCoupon($order->couponId);
            if(isset($coupon[Coupon::ID_COL])) {
                $coupon = Coupon::fromJson($coupon);
                if ($coupon->isValid()) {
                    $order->origionalAmount = $orderTotal;
                    switch ($coupon->discountType) {
                        case Coupon::DISCOUNT_TYPE_VAL_AMOUNT:
                            $orderTotal -= $coupon->discountAmount;
                            if ($orderTotal < 0)
                                $orderTotal = 0;
                            break;
                        case Coupon::DISCOUNT_TYPE_VAL_PERS:
                            $mul = 1 - bcdiv($coupon->discountAmount, 100);
                            $orderTotal = bcmul($orderTotal, $mul);
                            break;
                    }
                }
            }
        }


        if($order->amount != $orderTotal){
            $order->amount = $orderTotal;
            $this->updateOrderAmount($order);
        }
        $order->lines = $lines;

        if($full)
        {
            $this->log->debug("Full details");
            $uc = DataControllerFactory::getUserController();
            $gc = DataControllerFactory::getGeneralController();

            $order->paymentInfo = $this->getMollieInfoPublic($order->id);

            if($order->couponId != 0)
                $order->couponDetail = $cc->getCoupon($order->couponId);
            else
                $order->couponDetail = null;

            $order->userDetails = $uc->getUserDetails($order->userId);

            if($order->addressId != 0)
                $order->addressDetail = $gc->getAddress($order->addressId);
            else
                $order->addressDetail = null;

            //if($order->slottemplateId == 0)
            $this->getFirstAvailableSlot($order);

            $this->log->debug($order);

            unset($order->addressId);
        }
        unset($order->userId);

        return $order;
    }

    /**
     * @param $order Order
     * @return array
     */
    public function updateOrderFull($order)
    {
        $this->log->debug(Order::toDbUpdateArray($order));

//        if ($order->needsDelivery != Order::DELIVERY_NONE) {
//            $order->deliveryCost = $this->getRestaurantDeliveryPrice($order);
//        } else {
//            // Need to reset to 0 in case order had delivery first
//            $order->deliveryCost = 0;
//        }

        $this->db->update(Order::TABLE_NAME,
            Order::toDbUpdateArray($order),
            [
                Order::ID_COL => $order->id
            ]);
        $this->logMedooError();
        return $this->db->error();
    }

    public function updateOrderPaymentState($order)
    {
        $this->db->update(Order::TABLE_NAME,
            [
                Order::SUBMITTED_COL => $order->submitted,
                Order::PAYMENT_STATUS_COL => $order->paymentStatus
            ],
            [
                Order::ID_COL => $order->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $order Order
     * @return array
     */
    public function updateOrderAmount($order)
    {
        $this->db->update(Order::TABLE_NAME,
            [
                Order::AMOUNT_COL => $order->amount
            ],
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

        /** @var Order $dbOrder */
        $dbOrder = $this->getOrder($order->id);
        $this->log->debug($dbOrder);
        if(isset($dbOrder[Order::ID_COL]))
        {
            $dbOrder = Order::fromJson($dbOrder);
        }
        else {
            $response->code = 400;
            $response->message = "Order could not be found";
            return $response;
        }

        if($dbOrder->paymentStatus != null)
        {
            $response->code = 400;
            $response->message = "Order already submitted for payment";
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
            if($links == null)
            {
                $response->code = 500;
                $response->message = "Something went wrong in submitting the order";
                return $response;
            }
        }
        else
            $dbOrder->submitted = true;

        $dbOrder->paymentStatus = Order::PAYMENT_STATUS_VAL_PENDING;
        $this->log->debug("Before full update");
        $this->log->debug($dbOrder);
        $this->updateOrderFull($dbOrder);

        try {
            if(!$this->sendOrderConfirmation($dbOrder, $links->paymentUrl))
            {
                $response->code = 201;
                $response->message = "Order submitted successfully but confirmation mail failed.";
                $response->data = $links;
                return $response;
            }
        } catch (Exception $e) {
            $response->code = 201;
            $response->message = "Order submitted successfully but confirmation mail failed.";
            $response->data = $links;
            return $response;
        }

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
     * @throws \Exception
     */
    public function sendOrderConfirmation($order,$paymentLink)
    {
        $rc = DataControllerFactory::getRestaurantController();
        $gc = DataControllerFactory::getGeneralController();

        $this->log->debug($order);

        /** @var Restaurant $resto */
        $resto = $rc->getRestaurant($order->restaurantId,false);
        $this->log->debug($resto);
        if(isset($resto[Restaurant::ID_COL]))
            $resto = Restaurant::fromJson($resto);
        /** @var Address $address */
        $address = $gc->getAddress($resto->addressId);
        if(isset($address[Address::ID_COL]))
            $address = Address::fromJson($address);
        $this->log->debug($address);

        $date = new \DateTime($order->orderDateTime);

        $subject = 'Restaurant At Home - '.Order::getOrderDescription($order);
        $from = SEND_FROM_EMAIL;

        $headers = "MIME-Version: 1.0"."\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8"."\r\n";
        $headers .= "From: " . strip_tags($from) . "\r\n";
        //$headers .= "CC: susan@example.com\r\n";

        $message = file_get_contents(ORDER_EMAIL_TEMPLATE);

        $message = str_replace("%%date%%",$date->format(General::dateFormat),$message);
        $message = str_replace("%%hour%%",$date->format('H'),$message);
        $message = str_replace("%%min%%",$date->format('i'),$message);

        $message = str_replace("%%restoName%%",$resto->name,$message);
        $message = str_replace("%%street%%",$address->street,$message);
        $message = str_replace("%%number%%",$address->number,$message);
        if(!empty($address->addition))
            $message = str_replace("%%addition%%",'/'.$address->addition,$message);
        else
            $message = str_replace("%%addition%%",'',$message);
        $message = str_replace("%%postCode%%",$address->postcode,$message);
        $message = str_replace("%%city%%",$address->city,$message);
        $message = str_replace("%%phone%%",$resto->phone,$message);

        $url = General::getBaseUrl()."/paymentsuccess?id=".$order->id;
        $message = str_replace("%%orderLink%%",$url,$message);

        if($paymentLink != '')
            $message = str_replace("%%paymentLink%%",
                "<span>Indien er iets fout liep tijdens de betaling kan u deze steeds herroepen via volgende <a href='".$paymentLink."'>link</a>.</span>"
                ,$message);

        if($message === false)
            throw new \Exception("Failed to read email template");

        return mail(Authorization::$user->email,$subject,$message,$headers);
    }


    //region Validation

    /**
     * @param $order Order
     * @param $response ApiResponse
     * @return bool
     */
    public function updateFinalizationData(&$order,&$response)
    {
        $gc = DataControllerFactory::getGeneralController();
        $rc = DataControllerFactory::getRestaurantController();

        //Check Order has products
        $order->lines = $this->getOrderLines($order->id);
        if(count($order->lines) == 0){
            $response->code = 335;
            $response->message = "Order doesn't have any products";
            return false;
        }

        $this->log->debug($order);
        if(!$this->validateAddress($order,$response,$gc))
            return false;

        if(!$this->validateSlotTemplate($order,$response,$rc))
            return false;




        //check paymentMethod
        if(!$rc->getRestaurantHasPaymentMethod($order->restaurantId,$order->paymentmethodid)){
            $response->code = 330;
            $response->message = "Selected paymentmethod isn't allowed";
            return false;
        }

        $this->log->debug("Validated order before update");
        $this->log->debug($order);
        $this->updateOrderFull($order);

//        if(!$this->validateProductStock($order,$response))
//            return false;

        return true;
    }

    /**
     * @param $order Order
     * @param $response ApiResponse
     * @param $gc GeneralController
     * @return bool
     */
    public function validateAddress(&$order,&$response,$gc)
    {
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
        return true;
    }

    /**
     * @param $order Order
     * @param $response ApiResponse
     * @param $rc RestaurantController
     * @return bool
     */
    public function validateSlotTemplate(&$order,&$response,$rc)
    {
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

        return true;
    }

    public function validateProductStock(&$order,&$response)
    {
        //check product stock
        $pc = DataControllerFactory::getProductController();
        $stockErrors = [];
        foreach($order->lines as $line){
            /** @var ApiResponse $usageResult */
            $usageResult = $pc->getProductStockUsage(date($order->orderDateTime),$line->productId);
            if($usageResult->code != 200){
                $usageResult->orderLineId = $line->id;
                array_push($stockErrors, [
                    $usageResult
                ]);
            }
        }
        if(count($stockErrors) != 0){
            $response->code = 340;
            $response->message = "There are stock issues with your order";
            $response->data = $stockErrors;
            return false;
        }
        return true;
    }

    public function validateCouponCode(&$order,&$response)
    {
        //Check Coupon
        if(!empty($order->couponCode))
            return true; // no validation needed for emtpy codes

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

        return true;
    }
    //endregion
    //endregion

    //region OrderDetail (lines)
    /**
     * @param $orderLine OrderDetail
     * @return array|bool
     */
    public function addOrderDetailLine($orderLine)
    {
        $this->uc->checkUserHasOrder($orderLine->orderId,true);

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
        $this->uc->checkUserHasOrder($orderLine->orderId,true);

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


    //region Mollie Info
    public function getMollieInfoPublic($id)
    {
        return $this->db->get(MollieInfo::TABLE_NAME,
            [
                MollieInfo::MODE_COL,
                MollieInfo::METHOD_COL,
                MollieInfo::PAYMENT_URL_COL
            ],
            [
                MollieInfo::ORDER_ID_COL => $id
            ]);
    }
    //endregion



    //region Loyalty
    /**
     * @param $orderId
     * @return bool|int
     */
    public function getOrderLoyaltyPoints($orderId)
    {
        return $this->db->sum(OrderDetail::TABLE_NAME,
            [
                "[><]".Product::TABLE_NAME =>[
                    OrderDetail::PRODUCT_ID_COL => Product::ID_COL
                ]
            ],
            [
                Product::TABLE_NAME.".".Product::LOYALTY_POINTS_COL
            ],
            [
                OrderDetail::ORDER_ID_COL => $orderId
            ]);
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


    //region Slots

    /**
     * @param $order Order
     */
    public function getFirstAvailableSlot(&$order)
    {
        if($order->submitted)
            return;

        $rc = DataControllerFactory::getRestaurantController();

        $date = General::getCurrentDate();
        $time = General::getCurrentTime();
        if(!empty($order->orderDateTime))
        {
            $orderDT = new \DateTime($order->orderDateTime);
            $date = $orderDT->format(General::dateFormat);
            $time = $orderDT->format(General::timeFormat);
        }

        $this->log->debug("Date: ".$date);
        $this->log->debug("Time: ".$time);
        /** @var SlotTemplate[] $restoSlots */
        $restoSlots = $rc->getSlotOverview($order->restaurantId,$date);
        if(count($restoSlots) != 0)
            $restoSlots = SlotTemplate::fromJsonArray($restoSlots);
        else
            return;

        $minSlot = false;
        $suggestedSlotIndex = -1;
        $slotWeight = $this->getSlotWeight($order->id);
        for($i = 0; $i < count($restoSlots); $i++)
        {
            /** @var SlotTemplate $slot */
            $slot = $restoSlots[$i];
            if(($slot->fromTime > $time) or $minSlot)
            {
                if(!$minSlot)
                    $minSlot = true;
                $order->slottemplateId = $slot->id;
                $slotUsage = $rc->getSlotUsage($order,$date);
                if($slot->getSlotAvailability() >= ($slotUsage + $slotWeight))
                {
                    $suggestedSlotIndex = $i;
                    break;
                }
            }
        }

        if($suggestedSlotIndex == -1)
            return;

        $availableSlot = $restoSlots[$suggestedSlotIndex];
        $order->slottemplateId = $availableSlot->id;
        $order->orderDateTime = $date." ".$availableSlot->toTime;

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
    //endregion

//    public function getRestaurantDeliveryPrice($order){
//        $resto = $this->db->get("restaurant",
//            [
//                "deliveryCost"
//            ], [
//                "id" => $order->restaurantId
//            ]
//        );
//
//        return json_decode($resto)->{"deliveryCost"};
//    }
}
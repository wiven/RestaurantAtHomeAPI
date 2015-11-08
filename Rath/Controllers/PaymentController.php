<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 02-Oct-15
 * Time: 02:43 PM
 */

namespace Rath\Controllers;


use Exception;
use Mollie_API_Client;
use Mollie_API_Exception;
use Mollie_API_Object_Payment;
use Mollie_API_Resource_Payments;
use Rath\Controllers\Data\ControllerBase;
use Rath\Controllers\Data\DataControllerFactory;
use Rath\Entities\Order\MollieInfo;
use Rath\Entities\Order\Order;
use Rath\Entities\Restaurant\PaymentMethod;
use Rath\Helpers\General;
use Slim\Slim;

class PaymentController extends ControllerBase
{
    /**
     * @var Mollie_API_Client
     */
    private $mollie;

    public function __construct()
    {
        parent::__construct();
        $this->log->debug("Constructor of PaymentController");

        $this->mollie = new Mollie_API_Client();
        $this->mollie->setApiKey("test_aHZvwBcwqfXVk4FYTLrBvcArBVcwRg");
    }

    /**
     * @param $order Order
     * @return null|object
     * @throws \Exception
     */
    public function CreateMollieTransaction(&$order)
    {
//        $order = new Order();
//        $order->paymentStatus = null;
//        echo "isset: ".(string)isset($order->paymentStatus);
//        echo "empty: ".(string)empty($order->paymentStatus);
//
//
//
//        return "OK";
        try {
            $this->log->debug($order);
            $rc = DataControllerFactory::getRestaurantController();
            /** @var PaymentMethod $paymMethod */
            $paymMethod = $rc->getPaymentMethod($order->paymentmethodid);
            if(isset($paymMethod[PaymentMethod::ID_COL]))
                $paymMethod = PaymentMethod::fromJson($paymMethod);

            $this->log->debug($paymMethod);

            $webhook = $this->getMollieWebhookUrl();
            if (!isset($webhook))
                throw new \Exception("Invalid platform to test payments");

            //TODO: add payment method parameter
            $data = [
                "amount" => $order->amount,
//                "amount" => $order->amount + $order->deliveryCost,
                "description" => Order::getOrderDescription($order),
                "redirectUrl" => "http://playground.restaurantathome.be/paymentsuccess?id=".$order->id, //TODO:Parameter?
                "webhookUrl" => $webhook,
                "method" => $paymMethod->mollieId,
                "metadata" => [
                    "orderId" => $order->id,
                    "restoId" => $order->restaurantId,
                ]
            ];
            $this->log->debug($data);
            $payment = $this->mollie->payments->create($data);
            $this->log->debug($payment);
            $mollieInfoId = $this->paymentInfoToDatabase($order->id,$payment);

            $this->log->debug($mollieInfoId);

            if($mollieInfoId == false)
                throw new \Exception("Failed to insert mollieInfo");

            return $payment->links;

        } catch (Mollie_API_Exception  $e) {
            $this->log->error(json_last_error_msg() );
            $this->log->error("Unable to create Mollie Payment",$e);
        } catch(\Exception $ex)
        {
            $this->log->error(json_last_error_msg() );
            $this->log->error("Unable to create Mollie Payment",$ex);
        }
        return null;
    }

    /**
     * @param $payment Mollie_API_Object_Payment
     * @return bool
     */
    private function paymentInfoToDatabase($orderId,$payment)
    {
        $this->log->debug("Inserting payment");
        $mic = DataControllerFactory::getMollieInfoController();

        $info = new MollieInfo();
        $info->mollieId = $payment->id;
        $info->method = $payment->method;
        $info->mode = $payment->mode;
        $info->paymentUrl = $payment->links->paymentUrl;
        $info->ordersid = $orderId;
        $result = $mic->createMollieInfo($info);

        $this->logLastQuery();
        $this->logMedooError();
        return isset($result[MollieInfo::ID_COL]);
    }

    /**
     * @param $app Slim
     * @return array
     */
    public function handleMollieWebhook($app)
    {
        try {
            $oc = DataControllerFactory::getOrderController();
            $uc = DataControllerFactory::getUserController();

            $this->log->debug("Get mollie payment info");
            $payment = $this->mollie->payments->get($_POST["id"]);

            $this->log->debug($payment);
            $orderId = $payment->metadata->orderId;
            /** @var Order $order */
            $order = $oc->getOrder($orderId);
            if (isset($order[Order::ID_COL]))
                $order = Order::fromJson($order);

            if ($payment->isPaid()) {
                $this->log->debug("Payment ok, start final handling");
                $order->submitted = true;
                $order->paymentStatus = Order::PAYMENT_STATUS_VAL_PAYED;
                $oc->updateOrderPaymentState($order);

                //assign loyalty points
                $points = $oc->getOrderLoyaltyPoints($order->id);
                if(gettype($points) == General::integerType)
                    $uc->addLoyaltyPoints($order->restaurantId,$points);

            } elseif (!$payment->isOpen() ||
                       $payment->isCancelled() ||
                        $payment->isExpired()) {
                $this->log->debug("isn't paid & not open, canceled or expired -> aborted");
                $order->submitted = false;
                $order->paymentStatus = null;
                $oc->updateOrderPaymentState($order);
            }
        } catch (Exception $e) {
            $this->log->fatal("Something went wrong excepting a user payment!",$e);
            $this->logLastQuery();
            $this->logMedooError();
            $app->halt(500,"error processing response");
        }

        return ["Ok"];
    }

    public function logMolliePaymentMethods()
    {
        $this->log->debug("function: ".__FUNCTION__);
        try {
            $payMethods = $this->mollie->methods->all();
            if(count($payMethods) == 0)
                return ["status" => "No Payment methods found (".count($payMethods).")"];

            foreach ($payMethods as $method) {
                $this->log->info($method->description . ' (' . $method->id . ')');
            }
            return ["status" => "Ok"];
        } catch (Mollie_API_Exception $e) {
            $this->log->error("Unable to create Mollie Payment",$e);
            return ["status" => "failed"];
        }
    }

    public function getMollieWebhookUrl()
    {
        return "http://playground.restaurantathome.be/api/order/paymenthook/";
        switch(APP_MODE){
            case "APIDEV":
                return "test";
            case "TEST":
                return "test";
            default :
                return "http://playground.restaurantathome.be/api/order/paymenthook/";
        }
    }
}
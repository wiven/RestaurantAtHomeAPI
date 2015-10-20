<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 02-Oct-15
 * Time: 02:43 PM
 */

namespace Rath\Controllers;


use Mollie_API_Client;
use Mollie_API_Exception;
use Rath\Controllers\Data\ControllerBase;
use Rath\Entities\Order\Order;

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
     * @throws \Exception
     */
    public function CreateMollieTransaction($order)
    {
        try {
            $this->log->debug($order);
            $webhook = $this->getMollieWebhookUrl();
            if (!isset($webhook))
                throw new \Exception("Invalid platform to test payments");
            $data = [
                "amount" => $order->amount,
                "description" => Order::getOrderDescription($order),
                "redirectUrl" => "http://playground.restaurantathome.be", //TODO:Parameter?
                "webhookUrl" => $webhook,
                "metadata" => [
                    "orderId" => $order->id
                ]
            ];
            $this->log->debug($data);
            $payment = $this->mollie->payments->create($data);
            $this->log->debug($payment);

            $change = $this->db->update(Order::TABLE_NAME,
                [
                    Order::MOLLIE_ID_COL => $payment->id
                ],
                [
                    Order::ID_COL => $order->id
                ]);
            if($change == 0){
                $this->log->error($this->db->last_query());
                $this->log->error($this->db->error());
            }

        } catch (Mollie_API_Exception  $e) {
            $this->log->error(json_last_error_msg() );
            $this->log->error("Unable to create Mollie Payment",$e);
        }
    }

    public function handleMollieWebhook()
    {
        $payment = $this->mollie->payments->get($_POST["id"]);

        $orderId = $payment->metadata->orderId;
        if($payment->isPaid())
        {
            //Payment ok, start final handling
        }
        elseif(!$payment->isOpen())
        {
            // isn't paid & not open -> aborted
        }
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
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:20
 */

namespace Rath\Controllers\Data;

use Rath\Controllers\UserPermissionController;
use Rath\Entities\General\Address;
use Rath\Entities\Order\Order;
use Rath\Entities\Product\Product;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Entities\Slots\SlotTemplate;
use Rath\Entities\Slots\SlotTemplateChange;
use Rath\Entities\User\LoyaltyPoints;
use Rath\Helpers\General;
use Rath\helpers\MedooFactory;
use Rath\Entities\User\User;
use Rath\Entities\User\UserPermission;
use Rath\Entities\ApiResponse;
use Rath\Libraries\medoo;
use Rath\Slim\Middleware\Authorization;
use Slim\Route;
use Slim\Slim;


class UserController extends ControllerBase
{
    /**
     * @var Slim
     */
    private $app;

    /**
     * UserController constructor.
     * @param Slim $app
     */
    public function __construct($app)
    {
        parent::__construct();
//        var_dump("App empty in UC: ");
//        var_dump(empty($app));
        $this->app = $app;
    }

    //region Security

    public function checkUserHasRestaurant($restoId,$halt = false)
    {
        $result = $this->db->get(Restaurant::TABLE_NAME,
            [
                Restaurant::ID_COL,
                Restaurant::USER_ID_COL
            ],
            [
                "AND" => [
                    Restaurant::USER_ID_COL => Authorization::$userId,
                    Restaurant::ID_COL => $restoId
                ]
            ]);

        if($halt && !isset($result[Restaurant::ID_COL]))
            $this->app->halt(401,json_encode(UserPermissionController::GetPermissionErrorMessage("restaurant")));

        return isset($result[Restaurant::ID_COL]);
    }

    public function checkUserHasOrder($orderId,$halt = false)
    {
        $result = $this->db->get(Order::TABLE_NAME,
            [
                Order::ID_COL
            ],
            [
                "AND" =>[
                    Order::ID_COL => $orderId,
                    Order::USER_ID_COL => Authorization::$userId
                ]
            ]);

        if($halt && !isset($result[Order::ID_COL]))
            $this->app->halt(401,json_encode(UserPermissionController::GetPermissionErrorMessage("order")));

        return isset($result[Order::ID_COL]);
    }

    public function checkUserHasProduct($productId,$halt = false)
    {
        $result = $this->db->get(Product::TABLE_NAME,
            [
                "[><]".Restaurant::TABLE_NAME => [
                    Product::TABLE_NAME.".".Product::RESTAURANT_ID_COL => Restaurant::ID_COL
                ]
            ],
            [
                Product::TABLE_NAME.".".Product::ID_COL
            ],
            [
                "AND" => [
                    Product::TABLE_NAME.".".Product::ID_COL => $productId,
                    Restaurant::USER_ID_COL => Authorization::$userId
                ]
            ]);
//        var_dump(empty($this->app));
        if($halt && !isset($result[Product::ID_COL]))
            $this->app->halt(401,json_encode(UserPermissionController::GetPermissionErrorMessage("products")));

        return isset($result[Product::ID_COL]);
    }

    public function checkUserHasPromotion($promoId,$halt = false)
    {
        $result = $this->db->get(Promotion::TABLE_NAME,
            [
                "[><]".Restaurant::TABLE_NAME => [
                    Promotion::TABLE_NAME.".".Promotion::RESTAURANT_ID_COL => Restaurant::ID_COL
                ]
            ],
            [
                Promotion::TABLE_NAME.".".Promotion::ID_COL
            ],
            [
                "AND" => [
                    Promotion::TABLE_NAME.".".Promotion::ID_COL => $promoId,
                    Restaurant::USER_ID_COL => Authorization::$userId
                ]
            ]);

        if($halt && !isset($result[Promotion::ID_COL]))
            $this->app->halt(401,json_encode(UserPermissionController::GetPermissionErrorMessage("promotions")));

        return isset($result[Product::ID_COL]);
    }

    public function checkUserHasSlotTemplate($slotTemplateId, $halt = false)
    {
        $result = $this->db->get(SlotTemplate::TABLE_NAME,
            [
                "[><]".Restaurant::TABLE_NAME => [
                    SlotTemplate::TABLE_NAME.".".SlotTemplate::RESTAURANT_ID_COL => Restaurant::ID_COL
                ]
            ],
            [
                SlotTemplate::TABLE_NAME.".".SlotTemplate::ID_COL
            ],
            [
                "AND" => [
                    SlotTemplate::TABLE_NAME.".".SlotTemplate::ID_COL => $slotTemplateId,
                    Restaurant::USER_ID_COL => Authorization::$userId
                ]
            ]);

        if($halt && !isset($result[SlotTemplate::ID_COL]))
            $this->app->halt(401,json_encode(UserPermissionController::GetPermissionErrorMessage("slottemplate")));

        return isset($result[SlotTemplate::ID_COL]);
    }

    public function checkUserHasSlotTemplateChange($slotTemplateChangeId, $halt = false)
    {
        $result = $this->db->get(SlotTemplateChange::TABLE_NAME,
            [
                "[><]".SlotTemplate::TABLE_NAME => [
                    SlotTemplateChange::TABLE_NAME.".".SlotTemplateChange::SLOT_TEMPLATE_ID_COL => SlotTemplate::ID_COL
                ],
                "[><]".Restaurant::TABLE_NAME => [
                    SlotTemplate::TABLE_NAME.".".SlotTemplate::RESTAURANT_ID_COL => Restaurant::ID_COL
                ]
            ],
            [
                SlotTemplateChange::TABLE_NAME.".".SlotTemplateChange::ID_COL
            ],
            [
                "AND" => [
                    SlotTemplateChange::TABLE_NAME.".".SlotTemplateChange::ID_COL => $slotTemplateChangeId,
                    Restaurant::USER_ID_COL => Authorization::$userId
                ]
            ]);

        if($halt && !isset($result[SlotTemplateChange::ID_COL]))
            $this->app->halt(401,json_encode(UserPermissionController::GetPermissionErrorMessage("slottemplate")));

        return isset($result[SlotTemplateChange::ID_COL]);
    }
    //endregion


    public function authenticateUser($email,$password,$socialLogin = false){
        $response = new ApiResponse();

        if($socialLogin){
            $response->code = 500;
            $response->message = "Social login not available";
            return $response;
        }

        $para = [
            User::EMAIL_COL => $email,
            User::SOCIAL_LOGIN_COL => $socialLogin
        ];

        if(!$socialLogin)
            $para[User::PASSWORD_COL] = $password;


        $user = $this->db->get(User::TABLE_NAME,
            [
                User::ID_COL,
                User::HASH_COL,
                User::EMAIL_COL,
                User::NAME_COL,
                User::SURNAME_COL,
                User::TYPE_COL
            ],[
                "AND" => $para
            ]
        );

        $this->log->debug($user);
        if(isset($user[User::HASH_COL]))
        {
            Authorization::$userId = $user[User::ID_COL];
            $this->log->Debug(Authorization::$user);
            if($user[User::TYPE_COL] == User::TYPE_VALUE_RESTO){
                $restos = $this->getUserRestaurants();
                if(count($restos) != 0)
                    $user["resto"] = $restos[0];
                else
                    $user["resto"] = null;
            }

            unset($user[User::ID_COL]);
            return $user;
        }
        else{
            $this->logMedooError();
            $response->code = 401;
            $response->message = "User login failed";
            return $response;
        }
    }

    //region CRUD
    /**
     * @param $user User
     * @return string
     */
    public function createUser($user){
//        var_dump($user); //TODO: Remove
        $response = new ApiResponse();

        $email = $user->email;//[User::EMAIL_COL]; //TODO: Validate Email;

        $dbUser = $this->db->get(User::TABLE_NAME,
            [
                User::ID_COL,
                User::EMAIL_COL,
            ],[
                "AND" => [
                    User::EMAIL_COL => $email,
                ]
            ]
            );
//        echo "user value: ";
//        var_dump($dbUser);

        if(isset($dbUser[User::ID_COL])){
            $response->code = 2;
            $response->message = "User with email ".$email." already exists.";
            return $response;
        }

//        echo "Insert user";
        $userId = $this->getNextUserId();
        $hashString = hash(HASH_ALGO,$userId.$user->email.time());
//        var_dump($hashString);
        $data = [
            User::ID_COL => $userId,
            User::NAME_COL => $user->name,
            User::SURNAME_COL => $user->surname,
            User::EMAIL_COL => $user->email,
            User::TYPE_COL => $user->type,
            User::HASH_COL => $hashString,
            User::PHONE_NO_COL => $user->phoneNo
        ];
        if(!$user->socialLogin)
            $data[User::PASSWORD_COL]= $user->password; //Already MD5
//        echo "Data to insert: ";
//        var_dump($data);
        $a = $this->db->insert(User::TABLE_NAME,$data);
//        var_dump('Insert Result: '.$a);

        return $this->getUserByEmail($user->email);
    }

    public function updateUser($user)
    {
        //TODO: add validation and fault capture

        $data = [
            User::NAME_COL => $user->name,
            User::SURNAME_COL => $user->surname,
            User::TYPE_COL => $user->type,
            User::PHONE_NO_COL => $user->phoneNo
        ];

        if(property_exists($user,"password"))
            $data[User::PASSWORD_COL] = $user->password; //Already hashed

        $this->db->update(User::TABLE_NAME,
            $data,
            [
                User::HASH_COL => $user->hash,
            ]);
        return $this->getUserByHash($user->hash);
    }

    public function deleteUser($hash)
    {
        $response = new ApiResponse();

        $this->db->delete(User::TABLE_NAME,
            [
                User::HASH_COL => $hash,
            ]);
        $response->code = 200;
        $response->message = "User successfully removed.";
        return $response;
    }
    //endregion

    public function getUserByEmail($email,$internal = false){
        $param = [
            User::HASH_COL,
            User::EMAIL_COL,
            User::NAME_COL,
            User::SURNAME_COL,
            User::PHONE_NO_COL,
            User::TYPE_COL,
            User::SOCIAL_LOGIN_COL
        ];

        if($internal){
            array_push($param, User::ID_COL);
        }

        $user = $this->db->get(User::TABLE_NAME,
            $param,
            [
                User::EMAIL_COL => $email
            ]
        );
        return $user;
    }

    public function getUserByHash($hash){
        $user = $this->db->get(User::TABLE_NAME,
            [
                User::HASH_COL,
                User::EMAIL_COL,
                User::NAME_COL,
                User::SURNAME_COL,
                User::PHONE_NO_COL,
                User::TYPE_COL,
                User::SOCIAL_LOGIN_COL,
                User::ADMIN_COL,
                User::EXCLUSIVE_PERMISSION_COL
            ],[
                User::HASH_COL => $hash
            ]
        );
        return $user;
    }

    private function getNextUserId(){
        $lastId = $this->db->get(User::TABLE_NAME,
            User::ID_COL,
            [
                "ORDER" => [User::ID_COL.' DESC']
            ]);
        //var_dump($lastId+1);
        return $lastId+1;
    }

    /**
     * @param $hash string
     * @param $route Route
     * @return bool
     */
    public function checkUserPermissions($hash,$route){
        $result = $this->getUserByHash($hash);
//        var_dump($result);

        if(!isset($user[User::EMAIL_COL]))
            return false;

        if($user[User::ADMIN_COL]) //allow full access
            return true;

        if($user[User::EXCLUSIVE_PERMISSION_COL])
            return false; //TODO: implement possibility to add user specific permissions



        $result = $this->db->select(UserPermission::TABLE_NAME,
            [
                UserPermission::DISABLED_COL
            ],
            [
                "AND" => [
                    UserPermission::USER_TYPE_COL => $user[User::TYPE_COL],
                    UserPermission::ROUTE_COL => $route->getName()
                ]
            ]);
//        var_dump($result);
        if(!($result))
            return false;
        return ($result[0][UserPermission::DISABLED_COL] == 0);
    }

    public function getUserDetails($id,$includeAddress = false)
    {
        $user = $this->db->select(User::TABLE_NAME,
            [
                User::EMAIL_COL,
                User::NAME_COL,
                User::SURNAME_COL,
                User::PHONE_NO_COL
            ],[
                User::ID_COL => $id
            ]
        );

        if($includeAddress)
            $user["addresses"] = $this->getUserAddresses($id);

        return $user;
    }

    public function getUserIdByHash($hash){
        $user = $this->db->get(User::TABLE_NAME,
            [
                User::ID_COL,
                User::ADMIN_COL,
                User::TYPE_COL,
                User::EMAIL_COL
            ],
            [
                User::HASH_COL => $hash
            ]);
        return $user;
    }


    public function getUserRestaurants()
    {
        return $this->db->select(Restaurant::TABLE_NAME,
            [
                Restaurant::ID_COL,
                Restaurant::NAME_COL,
                Restaurant::KITCHEN_TYPE_ID_COL,
                Restaurant::ADDRESS_ID_COL,
                Restaurant::LOGO_PHOTO_COL,
                Restaurant::PHONE_COL,
                Restaurant::EMAIL_COL,
                Restaurant::URL_COL,
                Restaurant::DOMINATING_COLOR_COL,
                Restaurant::COMMENT_COL
            ],
            [
                Restaurant::USER_ID_COL => Authorization::$userId
            ]);
    }

    public function getUserActiveOrder()
    {
        $orderIds = $this->db->get(Order::TABLE_NAME,
            [
                Order::ID_COL
            ],
            [
                "AND" => [
                    Order::USER_ID_COL => Authorization::$userId,
                    Order::SUBMITTED_COL => false
                ]
            ]);
        $this->logLastQuery();
        $this->log->debug($orderIds);
        if(isset($orderIds[Order::ID_COL])){
            $oc = DataControllerFactory::getOrderController();
            return $oc->getOrderDetail($orderIds[Order::ID_COL]);
        }

        $response = new ApiResponse();
        $response->code = 404;
        $response->message = "No active order for this user";
        return $response;
    }

    //region Password Recovery

    /**
     * @param $email
     * @return ApiResponse
     */
    public function sendUserPasswordRecoveryMail($email)
    {
        $response = new ApiResponse();

        $user = $this->getUserByEmail($email,true);

        if(!isset($user[User::EMAIL_COL]))
        {
            $response->code = 404;
            $response->message = "The entered email address isn't registered";
            return $response;
        }
        $user = json_decode(json_encode($user),false);

        if($user->socialLogin){
            $response->code = 417;
            $response->message = "This is social login and cannot ask for a password reset.";
            return $response;
        }

        $url = General::getBaseUrl()."/user/passwordrecovery?key=".$this->createRecoveryHash($user);

        try{
            $this->sendRecoveryEmail($user,$url);
        }
        catch(\Exception $e){
            $this->log->error("Error sending recovery mail!".json_encode($user),$e);
            $response->code = 500;
            $response->message = "Something went wrong sending the recovery email";
            return $response;
        }

        $response->code = 200;
        $response->message = "Email send";
        return $response;
    }

    public function handleUserPasswordRecoveryChange($recoveryHash, $userInfo)
    {
        $response = new ApiResponse();
        $user = $this->db->get(User::TABLE_NAME,
            [
                User::ID_COL,
                User::RECOVERY_HASH_COL,
                User::RECOVERY_REQUEST_DT_COL
            ],
            [
                User::RECOVERY_HASH_COL => $recoveryHash
            ]);

        if(isset($user[User::ID_COL]))
            $user = json_decode(json_encode($user),false);
        else{
            $response->code = 404;
            $response->message = "Unknow recovery key";
            return $response;
        }

        if(!$this->checkRecoveryStillValid($user))
        {
            $response->code = 408;
            $response->message = "The reset link has expired";
            return $response;
        }

        $this->db->update(User::TABLE_NAME,
            [
                User::RECOVERY_REQUEST_DT_COL => null,
                User::RECOVERY_HASH_COL => null,
                User::PASSWORD_COL => $userInfo->password //TODO:: Password encryption
            ],
            [
                User::ID_COL => $user->id
            ]);

        $response->code = 200;
        $response->message = "Password change success";
        return $response;
    }

    /**
     * @param $user User
     * @return bool
     */
    private function checkRecoveryStillValid($user)
    {
        $creation = new \DateTime($user->recoveryRequestDT);
        $now = new \DateTime();
        $diff = $now->diff($creation);

        if($diff->h > 24)
            return false;
        return true;
    }

    /**
     * @param $user User
     * @return string
     */
    private function createRecoveryHash($user)
    {
        $recoveryHash = hash(HASH_ALGO,uniqid(rand(), true));
        $this->db->update(User::TABLE_NAME,
            [
                User::RECOVERY_HASH_COL => $recoveryHash,
                User::RECOVERY_REQUEST_DT_COL => General::getCurrentDateTime()
            ],
            [
                User::ID_COL => $user->id
            ]);
        return $recoveryHash;
    }

    /**
     * @param $user User
     * @param $recoveryUrl
     * @return bool
     * @throws \Exception
     */
    private function sendRecoveryEmail($user,$recoveryUrl)
    {
        $subject = 'Restaurant At Home - Password Recovery';
        $from = SEND_FROM_EMAIL;

        $headers = "MIME-Version: 1.0"."\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8"."\r\n";
        $headers .= "From: " . strip_tags($from) . "\r\n";
        //$headers .= "CC: susan@example.com\r\n";

        $message = file_get_contents(EMAIL_TEMPLATE);
        $message = str_replace("%%EMAIL%%",$user->email,$message);
        $message = str_replace("%%URL%%",$recoveryUrl,$message);

        if($message === false)
            throw new \Exception("Failed to read email template");

        return mail($user->email,$subject,$message,$headers);
    }
    //endregion

    //region LoyaltyPoints

    /**
     * @return array|bool
     */
    public function getLoyaltyPoints()
    {
        $result =  $this->db->select(LoyaltyPoints::TABLE_NAME,
            [
                "[><]".Restaurant::TABLE_NAME => [
                    LoyaltyPoints::RESTAURANT_ID_COL => Restaurant::ID_COL
                ]
            ],
            [
                Restaurant::TABLE_NAME.".".Restaurant::ID_COL."(restoId)",
                Restaurant::NAME_COL,
                LoyaltyPoints::QUANTITY_COL
            ],
            [
                LoyaltyPoints::TABLE_NAME.".".LoyaltyPoints::USER_ID_COL => Authorization::$userId
            ]);
//        var_dump($this->db->last_query());
//        var_dump($this->db->error());

        return $result;
    }

    /**
     * @return array|bool
     */
    public function getLoyaltyPoint($restoId)
    {
        $result =  $this->db->select(LoyaltyPoints::TABLE_NAME,
            [
                LoyaltyPoints::ID_COL,
                LoyaltyPoints::QUANTITY_COL
            ],
            [
                "AND" =>[
                    LoyaltyPoints::TABLE_NAME.".".LoyaltyPoints::USER_ID_COL => Authorization::$userId,
                    LoyaltyPoints::RESTAURANT_ID_COL =>$restoId
                ]
            ]);
//        var_dump($this->db->last_query());
//        var_dump($this->db->error());

        return $result;
    }

    /**
     * @param $restoId
     * @param $qty
     */
    public function addLoyaltyPoints($restoId, $qty)
    {
        $lpc = DataControllerFactory::getLoyaltyPointsController();
        $existing = $this->getLoyaltyPoint($restoId);
        if(!isset($existing[LoyaltyPoints::ID_COL]))
        {
            $point = new LoyaltyPoints();
            $point->userid = Authorization::$userId;
            $point->restaurantid = $restoId;
            $point->quantity = $qty;
            $lpc->insertLoyaltyPointsEntry($point);
        }
        else
        {
            /** @var LoyaltyPoints $point */
            $point = LoyaltyPoints::fromJson($existing);
            $point->quantity += $qty;
            $lpc->updateLoyaltyPoints($point);
        }
    }

    //endregion

    //region Address
    /**
     * @param $userId
     * @return array|bool
     */
    public function getUserAddresses($userId)
    {
        $result = $this->db->select(Address::TABLE_NAME,
            [
                Address::ID_COL,
                Address::STREET_COL,
                Address::NUMBER_COL,
                Address::ADDITION_COL,
                Address::POSTCODE_COL,
                Address::CITY_COL,
                Address::LATITUDE_COL,
                Address::LONGITUDE_COL
            ],
            [
                Address::USER_ID_COL => $userId
            ]);

        return $result;
    }
    //endregion
}

//$para = [
//    User::EMAIL_COL."[=]" => $email,
//    User::SOCIAL_LOGIN_COL."[=]" => $socialLogin
//];
//
//if(!$socialLogin)
//    $para[User::PASSWORD_COL."[=]"] = $password;

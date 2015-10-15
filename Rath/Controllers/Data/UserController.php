<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:20
 */

namespace Rath\Controllers\Data;

use Rath\Entities\General\Address;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Entities\User\LoyaltyPoints;
use Rath\Helpers\General;
use Rath\helpers\MedooFactory;
use Rath\Entities\User\User;
use Rath\Entities\User\UserPermission;
use Rath\Entities\ApiResponse;
use Rath\Libraries\medoo;
use Rath\Slim\Middleware\Authorization;
use Slim\Route;


class UserController extends ControllerBase
{

    /**
     * @SWG\Get(
     *     path="/login/{email}/{password}/{socialmediaLogin}",
     *     summary="Login a user",
     *     description="checks a users information and returns it",
     *     tags={"User"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of pet to return",
     *         in="path",
     *         name="petId",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="pet response",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/Pet")
     *         ),
     *         @SWG\Header(header="x-expires", type="string")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/Error"
     *         )
     *     )
     * )
     */
    public function authenticateUser($email,$password,$socialLogin){
        $para = [
            User::EMAIL_COL => $email,
            User::SOCIAL_LOGIN_COL => $socialLogin
        ];

        if(!$socialLogin)
            $para[User::PASSWORD_COL] = $password;

        $user = $this->db->select(User::TABLE_NAME,
            [
                User::HASH_COL,
                User::EMAIL_COL,
                User::NAME_COL,
                User::SURNAME_COL,
                User::TYPE_COL
            ],[
                "AND" => $para
            ]
            );
//        var_dump($para);
//        echo "User: ".$user[User::NAME_COL]." Surname: ".$user[User::SURNAME_COL];
//        die(var_dump($user));

//        var_dump($user);
        //TODO: See when to send the Admin parameter.
        return $user;
    }

    /**
     * @param $user User
     * @return string
     */
    public function createUser($user){
//        var_dump($user); //TODO: Remove
        $response = new ApiResponse();

        $email = $user->email;//[User::EMAIL_COL]; //TODO: Validate Email;

        $dbUser = $this->db->select(User::TABLE_NAME,
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

        if($dbUser){
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

        return UserController::getUserByEmail($user->email);
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
            $data[User::PASSWORD_COL] = $user->password; //Already MD5

        $this->db->update(User::TABLE_NAME,
            $data,
            [
                User::HASH_COL => $user->hash,
            ]);
        return UserController::getUserByHash($user->hash);
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
        $result = UserController::getUserByHash($hash);
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
                User::ID_COL
            ],
            [
                User::HASH_COL => $hash
            ]);
        $this->log->debug($user);
        return $user;
    }

    public function checkUserHasRestaurant($userId, $restoId)
    {
        $result = $this->db->get(Restaurant::TABLE_NAME,
            [
                Restaurant::ID_COL,
                Restaurant::USER_ID_COL
            ],
            [
                "AND" => [
                    Restaurant::USER_ID_COL => $userId,
                    Restaurant::ID_COL => $restoId
                ]
            ]);

        return isset($result[Restaurant::ID_COL]);
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

        $url = "http://restaurantathome.be/user/passwordrecovery?key=".$this->createRecoveryHash($user); //TODO: Param - Url to recovery

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
     * @throws \Exception
     */
    private function sendRecoveryEmail($user,$recoveryUrl)
    {
        //TODo: read template html

        $subject = 'Restaurant At Home - Password Recovery';
        $from = "info@restaurantathome.be"; //Todo: Param - from email

        $headers = "MIME-Version: 1.0"."\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8"."\r\n";
        $headers .= "From: " . strip_tags($from) . "\r\n";
        $headers .= "Reply-To: ". strip_tags($from) . "\r\n";
        //$headers .= "CC: susan@example.com\r\n";

        $message = file_get_contents(EMAIL_TEMPLATE);
        $message = str_replace("%%EMAIL%%",$user->email,$message);
        $message = str_replace("%%URL%%",$recoveryUrl,$message);

        if($message === false)
            throw new \Exception("Failed to read email template");

        mail($user->email,$subject,$message,$headers);
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



    //endregion

    //region Address
    /**
     * @param $userId
     * @return array|bool
     */
    public function getUserAddresses($userId)
    {
        return $this->db->select(Address::TABLE_NAME,
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

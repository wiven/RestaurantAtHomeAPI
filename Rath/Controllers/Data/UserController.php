<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:20
 */

namespace Rath\Controllers\Data;

use Rath\Entities\Restaurant\Restaurant;
use Rath\Entities\User\LoyaltyPoints;
use Rath\helpers\MedooFactory;
use Rath\Entities\User\User;
use Rath\Entities\User\UserPermission;
use Rath\Entities\ApiResponse;
use Rath\Libraries\medoo;
use Rath\Slim\Middleware\Authorization;


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
     * @param $user
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
        $hashString = sha1($userId.$user->email.time());
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
        $response->code = 1;
        $response->message = "User successfully removed.";
        return $response;
    }

    public function getUserByEmail($email){
        $user = $this->db->select(User::TABLE_NAME,
            [
                User::HASH_COL,
                User::EMAIL_COL,
                User::NAME_COL,
                User::SURNAME_COL,
                User::PHONE_NO_COL,
                User::TYPE_COL,
                User::SOCIAL_LOGIN_COL
            ],[
                User::EMAIL_COL => $email
            ]
        );
        return $user;
    }

    public function getUserByHash($hash){
        $user = $this->db->select(User::TABLE_NAME,
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

    public function checkUserPermissions($hash,$route){
        $result = UserController::getUserByHash($hash);
        $result = array_filter($result);
//        var_dump($result);

        if(!empty($result)>0)
            $user = $result[0];
        else
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
                    UserPermission::ROUTE_COL => $route
                ]
            ]);
//        var_dump($result);
        if(!($result))
            return false;
        return ($result[0][UserPermission::DISABLED_COL] == 0);
    }

    public function getUserIdByHash($hash){
        $user = $this->db->select(User::TABLE_NAME,
            [
                User::ID_COL
            ],
            [
                User::HASH_COL => $hash
            ]);
        return array_filter($user);
    }

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

}

//$para = [
//    User::EMAIL_COL."[=]" => $email,
//    User::SOCIAL_LOGIN_COL."[=]" => $socialLogin
//];
//
//if(!$socialLogin)
//    $para[User::PASSWORD_COL."[=]"] = $password;

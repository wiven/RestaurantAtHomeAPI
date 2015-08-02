<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:20
 */

//namespace Rath\Controllers;
//use Rath\helpers\MedooFactory as MedooFactory;

require_once APPLICATION_PATH.'/Rath/Libraries/medoo.min.php';
require_once APPLICATION_PATH.'/Rath/Helpers/MedooFactory.php';
require_once APPLICATION_PATH.'/Rath/Entities/User.php';
require_once APPLICATION_PATH.'/Rath/Entities/ApiResponse.php';

class UserController
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
    static function AuthenticateUser($email,$password,$socialLogin){
        $db = MedooFactory::CreateMedooInstance();

        $para = [
            User::EMAIL_COL => $email,
            User::SOCIAL_LOGIN_COL => $socialLogin
        ];

        if(!$socialLogin)
            $para[User::PASSWORD_COL] = $password;

        $user = $db->select(User::TABLE_NAME,
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
    static function CreateUser($user){
//        var_dump($user); //TODO: Remove
        $response = new ApiResponse();
        $db = MedooFactory::CreateMedooInstance();

        $email = $user->email;//[User::EMAIL_COL]; //TODO: Validate Email;

        $dbUser = $db->select(User::TABLE_NAME,
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
        $userId = UserController::GetNextUserId($db);
        $hashString = sha1($userId.$user->email.time());
        var_dump($hashString);
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
        $a = $db->insert(\User::TABLE_NAME,$data);
//        var_dump('Insert Result: '.$a);

        return UserController::GetUserByEmail($user->email);
    }

    static function UpdateUser($user)
    {
        //TODO: add validation and fault capture
        $db = MedooFactory::CreateMedooInstance();

        $data = [
            User::NAME_COL => $user->name,
            User::SURNAME_COL => $user->surname,
            User::TYPE_COL => $user->type,
            User::PHONE_NO_COL => $user->phoneNo
        ];

        if(property_exists($user,"password"))
            $data[User::PASSWORD_COL] = $user->password; //Already MD5

        $db->update(User::TABLE_NAME,
            $data,
            [
                User::HASH_COL => $user->hash,
            ]);
        return UserController::GetuserByHash($user->hash);
    }

    static function DeleteUser($hash)
    {
        $response = new ApiResponse();
        $db = MedooFactory::CreateMedooInstance();

        $db->delete(User::TABLE_NAME,
            [
                User::HASH_COL => $hash,
            ]);
        $response->code = 1;
        $response->message = "User successfully removed.";
        return $response;
    }

    static function GetUserByEmail($email){
        $db = MedooFactory::CreateMedooInstance();
        $user = $db->select(User::TABLE_NAME,
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

    static function GetuserByHash($hash){
        $db = MedooFactory::CreateMedooInstance();
        $user = $db->select(User::TABLE_NAME,
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

    private static function GetNextUserId(medoo $db){
        $lastId = $db->get(User::TABLE_NAME,
            User::ID_COL,
            [
                "ORDER" => [User::ID_COL.' DESC']
            ]);
        //var_dump($lastId+1);
        return $lastId+1;
    }

    static function CheckUserPermissions($hash,$route){
        $result = UserController::GetuserByHash($hash);
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

        $db = MedooFactory::CreateMedooInstance();
        $result = $db->select(UserPermission::TABLE_NAME,
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
}

//$para = [
//    User::EMAIL_COL."[=]" => $email,
//    User::SOCIAL_LOGIN_COL."[=]" => $socialLogin
//];
//
//if(!$socialLogin)
//    $para[User::PASSWORD_COL."[=]"] = $password;

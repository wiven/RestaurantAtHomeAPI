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

class LoginController
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

    static function CreateUser($user){
        $db = MedooFactory::CreateMedooInstance();

        $email = $user[User::EMAIL_COL]; //TODO: Validate Email;

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
        if($dbUser)
            return "User with email ".$email." already exists.";

        $db->insert(\User::TABLE_NAME,
            [
                User::NAME_COL => $user[User::NAME_COL],
                User::SURNAME_COL => $user[User::SURNAME_COL],
                User::EMAIL_COL => $user[User::EMAIL_COL],
                User::PASSWORD_COL => $user[User::PASSWORD_COL],
                User::TYPE_COL => $user[User::TYPE_COL]
            ]);
    }

    static function UpdateUser($user)
    {
        $db = MedooFactory::CreateMedooInstance();

        $email = $user[User::EMAIL_COL]; //TODO: Validate Email;

        $db->update(User::TABLE_NAME,
            [
                User::NAME_COL => $user[User::NAME_COL],
                User::SURNAME_COL => $user[User::SURNAME_COL],
                User::PASSWORD_COL => $user[User::PASSWORD_COL],
                User::TYPE_COL => $user[User::TYPE_COL]
            ],
            [
                User::EMAIL_COL => $email,
            ]
            );
    }

    static function DeleteUser($user)
    {
        $db = MedooFactory::CreateMedooInstance();

        $email = $user[User::EMAIL_COL]; //TODO: Validate Email;

        $db->delete(User::TABLE_NAME,
            [
                User::EMAIL_COL => $email,
            ]);

    }

}

//$para = [
//    User::EMAIL_COL."[=]" => $email,
//    User::SOCIAL_LOGIN_COL."[=]" => $socialLogin
//];
//
//if(!$socialLogin)
//    $para[User::PASSWORD_COL."[=]"] = $password;

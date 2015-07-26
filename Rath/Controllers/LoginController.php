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
                User::ID_COL,
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
        $user[0]["idHash"] = sha1($user[0][User::ID_COL]);
//        var_dump($user);
        return $user;
    }
}

//$para = [
//    User::EMAIL_COL."[=]" => $email,
//    User::SOCIAL_LOGIN_COL."[=]" => $socialLogin
//];
//
//if(!$socialLogin)
//    $para[User::PASSWORD_COL."[=]"] = $password;

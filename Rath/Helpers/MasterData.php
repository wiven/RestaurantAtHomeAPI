<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:40
 */

//namespace Rath\helpers;
//use Rath\helpers\MedooFactory as MedooFactory;

require_once APPLICATION_PATH.'/Rath/Libraries/medoo.min.php';

//include_once 'MedooFactory.php';
require_once APPLICATION_PATH.'/Rath/Entities/User.php';
require_once APPLICATION_PATH.'/Rath/Entities/Base.php';
require_once APPLICATION_PATH.'/Rath/Entities/UserPermission.php';
require_once 'MedooFactory.php';

class MasterData
{
    static  function CreateDemoData(){
        $db = MedooFactory::CreateMedooInstance();

        MasterData::InsertDemoUsers($db);
        MasterData::InsertDefaultRoutPermissions($db);

    }

    private static function InsertDemoUsers(medoo $db){
        $user = new User();
        $user->name = "Thomas";
        $user->password = md5("10Centimeter");
        $user->surname = "De Pauw";
        $user->email = "thdepauw@hotmail.com";
        $user->phoneNo = "+154689456489";
        $user->type = "Client";
        $user->admin = 0;
        $user->socialLogin = false;
        UserController::CreateUser($user);

        $user = new User();
        $user->name = "Wim";
        $user->password = md5("10Centimeter");
        $user->surname = "Vandevenne";
        $user->email = "wim.vandevenne@gmail.com";
        $user->phoneNo = "+21354891384";
        $user->type = "Client";
        $user->socialLogin = 1;
        $user->admin = true;
        UserController::CreateUser($user);

        $user = new User();
        $user->name = "Frederik";
        $user->password = md5("10Centimeter");
        $user->surname = "Deroover";
        $user->email = "derooverfrederik@gmail.com";
        $user->phoneNo = "+158946548942";
        $user->type = "Client";
        $user->socialLogin = 0;
        $user->admin = true;
        UserController::CreateUser($user);
    }

    private static function InsertDefaultRoutPermissions(medoo $db){
        $permissions = [];
        $row = 1;
        if (($handle = fopen(APPLICATION_PATH."/Resources/Database/DefaultUserPermissions.csv.txt", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
//                $num = count($data);
//                echo "<p> $num fields in line $row: <br /></p>\n";
//                for ($c=0; $c < $num; $c++) {
//                    echo $data[$c] . "<br />\n";
//                }
                $row++;
                //Add to array
                $perm = new UserPermission();
                $perm->userType = $data[0];
                $perm->route = $data[1];
                $permissions[$row] = $perm;
            }
            fclose($handle);
        } else{
            throw new Exception("Unable to read Permission CSV");
        }
        var_dump($permissions);
        UserPermissionController::InsertUserPermissionSets($permissions);
    }
}
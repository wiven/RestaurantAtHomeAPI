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

    private function InsertDemoUsers(medoo $db){
        $db->insert(\User::TABLE_NAME,
            [
                User::NAME_COL => "Thomas",
                User::SURNAME_COL => "De Pauw",
                User::EMAIL_COL => "thdepauw@hotmail.com",
                User::PASSWORD_COL => md5("10Centimeter"),
                User::ADMIN_COL => true,
                User::HASH_COL => sha1("thdepauw@hotmail.com")
            ]);
        $db->insert(\User::TABLE_NAME,
            [
                \User::NAME_COL => "Wim",
                \User::SURNAME_COL => "Vandevenne",
                \User::EMAIL_COL => "wim.vandevenne@gmail.com",
                \User::PASSWORD_COL => md5("10Centimeter"),
                \User::ADMIN_COL => true,
                User::HASH_COL => sha1("wim.vandevenne@gmail.com")
            ]);
    }

    private function InsertDefaultRoutPermissions(medoo $db){
        $db->insert(UserPermission::TABLE_NAME, [
            [
                UserPermission::USER_TYPE_COL => UserPermission::USER_TYPE_VAL_Client,
                UserPermission::ROUTE_COL => "user"
            ],
            [
                UserPermission::USER_TYPE_COL => UserPermission::USER_TYPE_VAL_Client,
                UserPermission::ROUTE_COL => "login"
            ],
            [
                UserPermission::USER_TYPE_COL => UserPermission::USER_TYPE_VAL_Resto,
                UserPermission::ROUTE_COL => "user"
            ],
            [
                UserPermission::USER_TYPE_COL => UserPermission::USER_TYPE_VAL_Resto,
                UserPermission::ROUTE_COL => "login"
            ]
        ]);
    }
}
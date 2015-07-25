<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:40
 */

namespace Rath\helpers;

use Rath\helpers\MedooFactory as MedooFactory;
require APPLICATION_PATH.'/Rath/Libraries/medoo.min.php';

class MasterData
{
    static  function CreateDemoData(){
        $db = MedooFactory::CreateMedooInstance();
//        $db = new \medoo([
//            // required
//            'database_type' => 'mysql',
//            'database_name' => 'rathdev',
//            'server' => 'localhost',
//            'username' => 'root',
//            'password' => '',
//            'charset' => 'utf8',
//
//            // optional
//            'port' => 3306,
//            // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
////            'option' => [
////                PDO::ATTR_CASE => PDO::CASE_NATURAL
////            ]
//        ]);

        MasterData::InsertDemoUsers($db);

    }

    private function InsertDemoUsers($db){
        $db->insert(\User::TABLE_NAME,
            [
                \User::NAME_COL => "Thomas",
                \User::SURNAME_COL => "De Pauw",
                \User::EMAIL_COL => "thdepauw@hotmail.com",
                \User::PASSWORD_COL => md5("10Centimeter"),
                \User::ADMIN_COL => true
            ],
            [
                \User::NAME_COL => "Wim",
                \User::SURNAME_COL => "Vandevenne",
                \User::EMAIL_COL => "wim.vandevenne@gmail.com",
                \User::ADMIN_COL => true
            ]);
    }
}
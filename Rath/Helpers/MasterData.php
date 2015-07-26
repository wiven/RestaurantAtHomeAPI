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
require_once 'MedooFactory.php';

class MasterData
{
    static  function CreateDemoData(){
        $db = MedooFactory::CreateMedooInstance();

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
            ]);
        $db->insert(\User::TABLE_NAME,
            [
                \User::NAME_COL => "Wim",
                \User::SURNAME_COL => "Vandevenne",
                \User::EMAIL_COL => "wim.vandevenne@gmail.com",
                \User::PASSWORD_COL => md5("10Centimeter"),
                \User::ADMIN_COL => true
            ]);
    }
}
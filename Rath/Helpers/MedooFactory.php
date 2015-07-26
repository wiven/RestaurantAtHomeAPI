<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:23
 */

//namespace Rath\helpers;

require_once APPLICATION_PATH.'\Rath\Libraries\medoo.min.php';

class MedooFactory
{
    //TODO: Create developer variable.

    static function CreateMedooInstance(){
        $db = new \medoo([
            // required
            'database_type' => 'mysql',
            'database_name' => 'rathdev',
            'server' => 'localhost',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',

            // optional
            'port' => 3306,
            // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
//            'option' => [
//                PDO::ATTR_CASE => PDO::CASE_NATURAL
//            ]
        ]);
        return $db;
    }
}
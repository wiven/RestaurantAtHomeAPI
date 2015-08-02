<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:23
 */

//namespace Rath\helpers;

require_once APPLICATION_PATH.'/Rath/Libraries/medoo.min.php';

class MedooFactory
{
    //TODO: Create developer variable.

    static function CreateMedooInstance(){
        if(APPLICATION_MODE == 'LOCAL')
            return new \medoo([
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
        else if(APPLICATION_MODE == 'APIDEV')
            return new \medoo([
                // required
                'database_type' => 'mysql',
                'database_name' => 'deb84843n3_rathdev',
                'server' => 'localhost',
                'username' => 'deb84843n3_tdp',
                'password' => 'gEcDgPOy',
                'charset' => 'utf8',

                // optional
                'port' => 3306,
                // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
    //            'option' => [
    //                PDO::ATTR_CASE => PDO::CASE_NATURAL
    //            ]
            ]);
        else
            throw new Exception("Application Mode not defined.");
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:23
 */

namespace Rath\helpers;

use Logger;
use Rath\Libraries\medoo;
use Exception;


class MedooFactory
{
    /**
     * @var medoo
     */
    private static $db;

    /**
     * @return medoo
     * @throws Exception
     */
    static function getMedooInstance(){
        if(empty(MedooFactory::$db))
            MedooFactory::createMedooInstance();
        return MedooFactory::$db;
    }

    private static function createMedooInstance(){
        if(APP_MODE == 'LOCAL')
            MedooFactory::$db =  new medoo([
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
        else if(APP_MODE == 'APIDEV')
            MedooFactory::$db =  new medoo([
                // required
                'database_type' => 'mysql',
                'database_name' => 'ID188346_rathdev',
                'server' => 'mysql006.hosting.combell.com',
                'username' => 'ID188346_rathdev',
                'password' => 'azertyWiven10',
                'charset' => 'utf8',

                // optional
                'port' => 3306,
                // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
                //            'option' => [
                //                PDO::ATTR_CASE => PDO::CASE_NATURAL
                //            ]
            ]);
        else if(APP_MODE == 'TEST')
         MedooFactory::$db = new medoo([
             // required
             'database_type' => 'mysql',
             'database_name' => 'ID188346_rattest',
             'server' => 'mysql038.hosting.combell.com',
             'username' => 'ID188346_rattest',
             'password' => 'AzertyWiven10',
             'charset' => 'utf8',

             // optional
             'port' => 3306,
             // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
             //            'option' => [
             //                PDO::ATTR_CASE => PDO::CASE_NATURAL
             //
         ]);
        else
            throw new Exception("Application Mode not defined.");
    }
}
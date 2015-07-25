<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:20
 */

namespace Rath\Controllers;

use Rath\helpers\MedooFactory as MedooFactory;

require '..\Libraries\medoo.min.php';

class LoginController
{
    static function AuthenticateUser($userInfo){
        $medoo = MedooFactory::CreateMedooInstance();

    }
}
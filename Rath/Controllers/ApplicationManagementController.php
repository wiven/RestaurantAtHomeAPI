<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 2/08/2015
 * Time: 11:02
 */

namespace Rath\Controllers;

use Rath\Entities\ApiResponse;

class ApplicationManagementController
{
    public static function GetStatus(){
        return ['ack'=> time()];
    }
}
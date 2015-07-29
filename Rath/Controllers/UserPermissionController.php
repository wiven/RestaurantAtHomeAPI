<?php

require_once APPLICATION_PATH.'/Rath/Entities/ApiResponse.php';
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 29/07/2015
 * Time: 20:42
 */

//namespace Rath\Controllers;


class UserPermissionController
{
    static function GetPermissionErrorMessage($route){
        $response = new ApiResponse();
        $response->code = 403;
        $response->message = "Access denied to route: ".$route;
        return $response;
    }
}
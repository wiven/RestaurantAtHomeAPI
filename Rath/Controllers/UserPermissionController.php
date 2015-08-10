<?php

//require_once APP_PATH.'/Rath/Entities/ApiResponse.php';
//require_once APP_PATH . '/Rath/Libraries/medoo.php';
//require_once APP_PATH.'/Rath/Helpers/MedooFactory.php';
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 29/07/2015
 * Time: 20:42
 */

namespace Rath\Controllers;

use Rath\helpers\MedooFactory;
use Rath\Entities\User\UserPermission;
use Rath\Entities\User\User;
use Rath\Entities\ApiResponse;

class UserPermissionController
{
    static function InsertUserPermissionSet(UserPermission $permission){
        $db = MedooFactory::getMedooInstance();
        $db->insert(UserPermission::TABLE_NAME,
            [
                UserPermission::USER_TYPE_COL => $permission->userType,
                UserPermission::ROUTE_COL => $permission->route,
                UserPermission::DISABLED_COL => $permission->disabled
            ]);
    }

    /**
     * @param $permissions UserPermission[]
     */
    static  function InsertUserPermissionSets(array $permissions){
        $db = MedooFactory::getMedooInstance();
        foreach($permissions as $permission ){
//            echo "inserting:";
//            var_dump($permission);
            $db->insert(UserPermission::TABLE_NAME,
                [
                    UserPermission::USER_TYPE_COL => $permission->userType,
                    UserPermission::ROUTE_COL => $permission->route,
                    UserPermission::DISABLED_COL => $permission->disabled
                ]);
        }
    }

    static function InsertUserPermissionSetForUser(UserPermission $permission, User $user){
        //TODO: implement when required
    }

    static function GetPermissionErrorMessage($route){
        $response = new ApiResponse();
        $response->code = 403;
        $response->message = "Access denied to route: ".$route;
        return $response;
    }
}
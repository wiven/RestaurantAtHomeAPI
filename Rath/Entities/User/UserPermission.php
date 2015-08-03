<?php

//require_once 'ApiResponse.php';

namespace Rath\Entities\User;

/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 29/07/2015
 * Time: 18:44
 */
class UserPermission
{
    const TABLE_NAME = "userpermission";

    const ID_COL = "id";
    const USER_TYPE_COL = "userType";
        const USER_TYPE_VAL_Client = "Client";
        const USER_TYPE_VAL_Resto = "Resto";
    const ROUTE_COL = "route";
    const DISABLED_COL = "disabled";

    function __construct(){
        $this->disabled = 0;
    }

    public $id;

    public $userType;

    public $route;

    public $disabled;
}
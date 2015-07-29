<?php

require_once 'ApiResponse.php';

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
}
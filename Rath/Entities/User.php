<?php

/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:45
 */
require 'Base.php';

class User extends Base
{
    const TABLE_NAME = "user";

    const ID_COL = "id";
    const NAME_COL = "name";
    const SURNAME_COL = "surname";
    const PHONE_NO_COL = "phoneNo";
    const TYPE_COL = "type";
    const EMAIL_COL = "email";
    const PASSWORD_COL = "password";
    const ADMIN_COL = "admin";
    const SOCIAL_LOGIN_COL = 'socialLogin';

}
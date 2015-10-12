<?php

/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:45
 */

namespace Rath\Entities\User;

use Rath\Entities\DynamicClass;

class User extends DynamicClass
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
    const HASH_COL = "hash";
    const EXCLUSIVE_PERMISSION_COL = "exclusivePermissions";
    const RECOVERY_HASH_COL = "recoveryHash";
    const RECOVERY_REQUEST_DT_COL = "recoveryRequestDT";

    public $id;
    public $name;
    public $surname;
    public $phoneNo;
    public $type;
    public $email;
    public $password;
    public $admin;
    public $socialLogin;
    public $hash;
    public $exclusivePermissions;
    public $recoveryHash;
    public $recoveryRequestDT;

}
<?php

/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:45
 */

namespace Rath\Entities\User;

use Rath\Entities\EntityBase;

class User extends EntityBase
{
    const TABLE_NAME = "user";


    const ID_COL = "id";
    const NAME_COL = "name";
    const SURNAME_COL = "surname";
    const PHONE_NO_COL = "phoneNo";
    const TYPE_COL = "type";
        const TYPE_VALUE_CLIENT = "Client";
        const TYPE_VALUE_RESTO = "Resto";
    const EMAIL_COL = "email";
    const PASSWORD_COL = "password";
    const ADMIN_COL = "admin";
    const SOCIAL_LOGIN_COL = 'socialLogin';
    const HASH_COL = "hash";
    const EXCLUSIVE_PERMISSION_COL = "exclusivePermissions";
    const RECOVERY_HASH_COL = "recoveryHash";
    const RECOVERY_REQUEST_DT_COL = "recoveryRequestDT";

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $surname;
    /**
     * @var string
     */
    public $phoneNo;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $password;
    /**
     * @var bool
     */
    public $admin;
    /**
     * @var bool
     */
    public $socialLogin;
    /**
     * @var string
     */
    public $hash;
    /**
     * @var bool
     */
    public $exclusivePermissions;
    /**
     * @var string
     */
    public $recoveryHash;
    /**
     * @var string
     */
    public $recoveryRequestDT;

}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:46
 */

namespace Rath\Controllers\Data;


use Rath\helpers\MedooFactory;
use Rath\Libraries\medoo;

abstract class ControllerBase
{
    /**
     * @var medoo
     */
    protected $db;

    public function __construct(){
        $this->db = MedooFactory::getMedooInstance();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:46
 */

namespace Rath\Controllers\Data;


use Logger;
use Rath\Helpers\MedooFactory;
use Rath\Libraries\medoo;

abstract class ControllerBase
{
    /**
     * @var medoo
     */
    protected $db;

    /**
     * @var Logger
     */
    protected $log;

    public function __construct(){
        $this->db = MedooFactory::getMedooInstance();
        $this->log = Logger::getLogger(get_called_class());
    }

    public function logLastQuery()
    {
        $this->log->debug($this->db->last_query());
    }

    public function logMedooError()
    {
        $this->log->debug($this->db->error());
    }
}
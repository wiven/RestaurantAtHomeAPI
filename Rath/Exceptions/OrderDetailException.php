<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 17/08/2015
 * Time: 18:26
 */

namespace Rath\Exceptions;


use Exception;

class OrderDetailException extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
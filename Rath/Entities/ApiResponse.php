<?php

namespace Rath\Entities;

/**
 * @SWG\Definition(
 *   @SWG\Xml(name="##default")
 * )
 */
class ApiResponse
{
    function __construct(){ }

    function __construct1($code,$message){
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @SWG\Property(format="int32")
     * @var int
     */
    public $code;

    /**
     * @SWG\Property
     * @var string
     */
    public $message;

    /**
     * @SWG\Property
     * @var string
     */
    public $data;
}

<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:02
 */

namespace Rath\helpers;

use Slim;
use Rath\Entities\ApiResponse;

class CrossDomainAjax
{
    /**
     * @param $app Slim\Slim
     * @param $data array
     */
    static function PrintCrossDomainCall($app, $data){
        $callback = $app->request()->get('callback');
        $app->contentType('application/javascript');
        echo sprintf("%s(%s)", $callback, json_encode($data));
    }

}
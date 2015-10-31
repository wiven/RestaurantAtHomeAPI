<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 31-Oct-15
 * Time: 11:51 AM
 */

namespace Rath\Controllers;


use Rath\Controllers\Data\DataControllerFactory;

class HomeController
{
    public function getHomeView()
    {
        $rc = DataControllerFactory::getRestaurantController();

        $restoSpotlight = $rc->getRestaurantInTheSpotlight();

        return[
            "spotlight" => $restoSpotlight
        ];
    }
}
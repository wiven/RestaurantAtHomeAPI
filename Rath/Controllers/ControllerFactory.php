<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 11/08/2015
 * Time: 21:04
 */

namespace Rath\Controllers;


class ControllerFactory
{
    /**
     * @var DashboardController
     */
    private static $dashboardController;

    /**
     * @return DashboardController
     */
    public static function getDashboardController()
    {
        if(empty(self::$dashboardController))
            self::$dashboardController = new DashboardController();
        return self::$dashboardController;
    }


}
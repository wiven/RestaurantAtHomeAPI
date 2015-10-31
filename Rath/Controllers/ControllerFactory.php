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

    /**
     * @var SearchController
     */
    private static $searchController;

    /**
     * @return SearchController
     */
    public static function getSearchController()
    {
        if(empty(self::$searchController))
            self::$searchController = new SearchController();
        return self::$searchController;
    }

    /**
     * @var ApplicationManagementController
     */
    private static $appManagementController;

    public static function getAppManagementController()
    {
        if(empty(self::$appManagementController))
            self::$appManagementController = new ApplicationManagementController();
        return self::$appManagementController;
    }

    /**
     * @var PaymentController
     */
    private static $paymentController;

    public static function getPaymentController()
    {
        if(empty(self::$paymentController))
            self::$paymentController = new PaymentController();
        return self::$paymentController;
    }

    /**
     * @var RestaurantDetailController
     */
    private static $RestaurantDetailController;

    public static function getRestaurantDetailController()
    {
        if(empty(self::$RestaurantDetailController))
            self::$RestaurantDetailController = new RestaurantDetailController();
        return self::$RestaurantDetailController;
    }

    /**
     * @var HomeController
     */
    private static $HomeController;

    public static function getHomeController()
    {
        if(empty(self::$HomeController))
            self::$HomeController = new HomeController();
        return self::$HomeController;
    }

}
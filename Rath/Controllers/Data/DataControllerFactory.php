<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 9/08/2015
 * Time: 20:22
 */

namespace Rath\Controllers\Data;


class DataControllerFactory
{
    /**
     * @var UserController
     */
    private static $userController;

    /**
     * @var ProductController
     */
    private static $productController;

    /**
     * @var PromotionController
     */
    private static $promotionController;

    /**
     * @var RestaurantController
     */
    private static $restaurantController;

    /**
     * @var OrderController
     */
    private static $orderController;

    /**
     * @return UserController
     */
    public static function getUserController()
    {
        if(empty(self::$userController))
            self::$userController = new UserController();
        return self::$userController;
    }

    /**
     * @return ProductController
     */
    public static function getProductController()
    {
        if(empty(self::$productController))
            self::$productController = new ProductController();
        return self::$productController;
    }

    /**
     * @return PromotionController
     */
    public static function getPromotionController()
    {
        if(empty(self::$promotionController))
            self::$promotionController = new PromotionController();
        return self::$promotionController;
    }

    /**
     * @return RestaurantController
     */
    public static function getRestaurantController()
    {
        if(empty(self::$restaurantController))
            self::$restaurantController = new RestaurantController();
        return self::$restaurantController;
    }

    /**
     * @return OrderController
     */
    public static function getOrderController()
    {
        if(empty(self::$orderController))
            self::$orderController = new OrderController();
        return self::$orderController;
    }


}
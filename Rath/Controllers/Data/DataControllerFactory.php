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
     * @return ProductController
     */
    public static function getProductController()
    {
        if(isEmpty(self::$productController))
            self::$productController = new ProductController();
        return self::$productController;
    }

    /**
     * @return PromotionController
     */
    public static function getPromotionController()
    {
        if(isEmpty(self::$promotionController))
            self::$promotionController = new PromotionController();
        return self::$promotionController;
    }

    /**
     * @return RestaurantController
     */
    public static function getRestaurantController()
    {
        if(isEmpty(self::$restaurantController))
            self::$restaurantController = new RestaurantController();
        return self::$restaurantController;
    }

    /**
     * @return OrderController
     */
    public static function getOrderController()
    {
        if(isEmpty(self::$orderController))
            self::$orderController = new OrderController();
        return self::$orderController;
    }


}
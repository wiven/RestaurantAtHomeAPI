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
     * @var GeneralController
     */
    private static $generalController;

    /**
     * @var DefaultDataController
     */
    private static $defaultDataController;

    /**
     * @var FilterFieldController
     */
    private static $filterFieldController;

    /**
     * @var SlotController
     */
    private static $slotController;

    /**
     * @var LoyaltyPointsController
     */
    private static $loyaltyPointsController;

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

    /**
     * @return GeneralController
     */
    public static function getGeneralController()
    {
        if(empty(self::$generalController))
            self::$generalController = new GeneralController();
        return self::$generalController;
    }

    /**
     * @return DefaultDataController
     */
    public static function getDefaultDataController()
    {
        if(empty(self::$defaultDataController))
            self::$defaultDataController = new DefaultDataController();
        return self::$defaultDataController;
    }

    public static function getFilterFieldController()
    {
        if(empty(self::$filterFieldController))
            self::$filterFieldController = new FilterFieldController();
        return self::$filterFieldController;
    }

    public static function getSlotController()
    {
        if(empty(self::$slotController))
            self::$slotController = new SlotController();
        return self::$slotController;
    }

    public static function getLoyaltyPointsController()
    {
        if(empty(self::$loyaltyPointsController))
            self::$loyaltyPointsController = new LoyaltyPointsController();
        return self::$loyaltyPointsController;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 9/08/2015
 * Time: 18:55
 */

namespace Rath\Controllers;


use Rath\Controllers\Data\DataControllerFactory;
use Rath\Entities\Order\Order;
use Rath\Entities\Order\OrderStatus;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Helpers\General;

class DashboardController
{
    public function getNewOrderCount($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();
        return $rc->getNewOrderCount($restoId);
    }

    public function getOverviewContent($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();
        $promo = DataControllerFactory::getPromotionController();
        $gen = DataControllerFactory::getGeneralController();

        //Geather promotion info
        $activePromo = $rc->getActivePromotions($restoId);
//        if(!empty($activePromo))
//            foreach ($activePromo as $promotion) {
//                $promotion["usage"] = $promo->getPromotionUsageCount($promotion[Promotion::ID_COL]);
//            }
//        else
//            $activePromo = [];

        $newOrderCount = $rc->getNewOrderCount($restoId);
        $openOrdersForToday = $rc->getOrdersForToday($restoId,OrderStatus::val_Accepted,OrderStatus::val_OnRoute);
        $partners = $gen->getAllPartnersPaged(0,4);

        return[
            "newOrders" => $newOrderCount,
            "activePromos" => $activePromo,
            "openOrders" => $openOrdersForToday,
            "partners" => $partners
        ];
    }

    public function getProfileContent($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();

        $restaurant = $rc->getRestaurant($restoId);
        $openingHours = $rc->getOpeningHours($restoId);
        $photos = $rc->getPhotos($restoId);
        $address = $rc->getAddress($restaurant[Restaurant::ADDRESS_ID_COL]);
        $paymentMethods = $rc->getRestaurantPaymentMethods($restoId);
        //TODO: social media

        return[
            "restaurantInfo" => $restaurant,
            "addressInfo" => $address,
            "openingHours" => $openingHours,
            "paymentMethods" => $paymentMethods,
            "photos" => $photos
        ];
    }

    public function getProductContent($restoId,$count,$skip)
    {
        //TODO: add filter
        $prod = DataControllerFactory::getProductController();
        return $prod->getRestaurantProducts($restoId,$count,$skip);
    }

    public function getPromotionContent($restoId, $count, $skip)
    {
        $rc = DataControllerFactory::getRestaurantController();
        return $rc->getPromotions($restoId,$count,$skip);
    }

}
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

        //Geather promotion info
        $activePromo = $rc->getActivePromotions($restoId);
        if(!empty($activePromo))
            foreach ($activePromo as $promotion) {
                $promotion["usage"] = $promo->getPromotionUsageCount($promotion[Promotion::ID_COL]);
            }
        else
            $activePromo = [];

        $newOrderCount = $rc->getNewOrderCount($restoId);

        $openOrdersForToday = $rc->getOrdersForToday($restoId,OrderStatus::val_Accepted,OrderStatus::val_OnRoute);
        //$ordersFinished =  $rc->getOrdersForToday($restoId,OrderStatus::val_Finished,OrderStatus::val_Finished);

        return[
            $newOrderCount,
            $activePromo,
            $openOrdersForToday

        ];
    }

    public function getProductContent($restoId,$count,$skip)
    {
        $prod = DataControllerFactory::getProductController();
        return $prod->getRestaurantProducts($restoId,$count,$skip);
    }

    public function getPromotionContent($restoId, $count, $skip)
    {
        $rc = DataControllerFactory::getRestaurantController();
        return $rc->getPromotions($restoId,$count,$skip);
    }

}
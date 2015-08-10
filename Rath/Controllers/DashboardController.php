<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 9/08/2015
 * Time: 18:55
 */

namespace Rath\Controllers;


use Rath\Controllers\Data\DataControllerFactory;
use Rath\Entities\Promotion\Promotion;

class DashboardController
{
    public function getOverviewContent($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();
        $promo = DataControllerFactory::getPromotionController();

        //Geather promotion info
        $activePromo = $rc->getActivePromotions($restoId);
        foreach ($activePromo as $promotion) {
            $promotion["usage"] = $promo->getPromotionUsageCount($promotion[Promotion::ID_COL]);
        }

        return[
            $activePromo
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
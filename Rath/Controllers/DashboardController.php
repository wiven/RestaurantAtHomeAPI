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
use Rath\Entities\Restaurant\LoyaltyBonus;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Helpers\General;

class DashboardController
{
    public function getNewOrderCount($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();
        return [
            "count" => $rc->getNewOrderCount($restoId)
        ];
    }

    public function getOverviewContent($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();
        $gen = DataControllerFactory::getGeneralController();

        $newOrderCount = $rc->getNewOrderCount($restoId);
        $activePromo = $rc->getActivePromotions($restoId,0,5);
        $openOrdersForToday = $rc->getOrders($restoId,OrderStatus::val_Accepted,OrderStatus::val_OnRoute,0,5);
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
        $socialMedia = $rc->getAllSocialMedia($restoId);
        $specialties = $rc->getRestaurantSpecialties($restoId);

        return[
            "restaurantInfo" => $restaurant,
            "addressInfo" => $address,
            "openingHours" => $openingHours,
            "paymentMethods" => $paymentMethods,
            "socialMedia" => $socialMedia,
            "specialties" => $specialties,
            "photos" => $photos
        ];
    }

    public function getProductContent($restoId,$skip,$top,$query)
    {
        $resto = DataControllerFactory::getRestaurantController();
        return [
            "products" => $resto->getProducts($restoId,$skip,$top,$query)
            ];
    }

    public function getOrderContent($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();

        $newOrders = $rc->getOrders($restoId,OrderStatus::val_New,OrderStatus::val_New,0,5,false);
        $inProcess = $rc->getOrders($restoId,OrderStatus::val_Accepted,OrderStatus::val_InProgress,0,5,false);
        $ready = $rc->getOrders($restoId,OrderStatus::val_Ready,OrderStatus::val_OnRoute,0,5,false);
        $finished = $rc->getOrders($restoId,OrderStatus::val_Finished,OrderStatus::val_Finished,0,5,false);

        return[
            "new" => $newOrders,
            "inProgress" => $inProcess,
            "ready" => $ready,
            "finished" => $finished
        ];
    }

    public  function getSlotContent($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();
//        $slotTemplates = $rc->getSlotTemplates($restoId,General::getCurrentDayOfWeek());
        return $rc->getSlotOverview($restoId);

    }

    public function getLoyaltyContent($restoId)
    {
        $rc = DataControllerFactory::getRestaurantController();
        $lbc = DataControllerFactory::getLoyaltyBonusController();
        $result = $rc->getLoyaltyBonus($restoId);

        if(empty($result)){
            $lb = new LoyaltyBonus();
            $lb->restaurantid = $restoId;
            $lb->type = LoyaltyBonus::TYPE_VALUE_AMOUNT;
            $lb->points = 0;
            $lb->amount = 0;
            $result = $lbc->createLoyaltyBonus($lb);
        }
        return $result;
    }

    public function getPromotionContent($restoId, $skip,$top)
    {
        $rc = DataControllerFactory::getRestaurantController();

        $active = $rc->getActivePromotions($restoId,$skip,$top);
        $coming = $rc->getComingPromotions($restoId,$skip,$top);
        $passed = $rc->getPassedPromotions($restoId,$skip,$top);

        return [
            "passed" => $passed,
            "active" => $active,
            "coming" => $coming
        ];
    }

    public function getPartners($skip,$top)
    {
        $gen = DataControllerFactory::getGeneralController();
        return[
            "partners" => $gen->getAllPartnersPaged($skip,$top)
        ];
    }

}
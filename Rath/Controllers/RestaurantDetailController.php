<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 17-Oct-15
 * Time: 02:07 PM
 */

namespace Rath\Controllers;


use Rath\Controllers\Data\ControllerBase;
use Rath\Controllers\Data\DataControllerFactory;
use Rath\Entities\Product\Product;
use Rath\Entities\Product\ProductType;

class RestaurantDetailController extends ControllerBase
{
    /**
     * @param $restoId int
     * @return array
     */
    public function getRestaurantDetailView($restoId)
    {
        $dc = ControllerFactory::getDashboardController();
        $rc = DataControllerFactory::getRestaurantController();
        $pc = DataControllerFactory::getProductController();

        /*
         * build product information
         * product Types
         *  - Products
         *      - Tags
         */

        $productTypes = $pc->getProductTypes();
        for ($i = 0; $i < count($productTypes); $i++)
        {
            $products = $rc->getProductsAllByProductType($restoId,$productTypes[$i][ProductType::ID_COL]);
            for($j = 0; $j < count($products); $j++)
            {
                   $products[$j]["tags"] = $pc->getProductTags($products[$j][Product::ID_COL]);
            }
            $productTypes[$i]["products"] = $products;
        }

        return[
            "restaurantDetails" => $dc->getProfileContent($restoId),
            "promotions" => $rc->getActivePromotions($restoId,0,50),
            "productTypes" => $productTypes
        ];
    }
}
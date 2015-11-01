<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 9/08/2015
 * Time: 14:37
 */

namespace Rath\Controllers\Data;;


use Rath\Controllers\Data\ControllerBase;
use Rath\Entities\Product\Product;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Promotion\PromotionType;
use Rath\Entities\Promotion\PromotionUsageHistory;
use Rath\Helpers\General;

class PromotionController Extends ControllerBase
{
    /**
     * @var UserController
     */
    private $uc;

    /**
     * ProductController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->uc = DataControllerFactory::getUserController();
    }


    //region Promotions
    public function getPromotion($id)
    {
        $promo = $this->db->get(Promotion::TABLE_NAME,"*",
            [
                Promotion::ID_COL => $id
            ]);
        $promo["products"] = $this->db->select(
            Product::TABLE_NAME,
            [
                Product::ID_COL,
                Product::NAME_COL
            ],
            [
                Product::PROMOTION_ID_COL => $promo[Promotion::ID_COL]
            ]
        );
        return $promo;
    }


    /**
     * @param $promo Promotion
     * @return array|bool
     */
    public function addPromotion($promo)
    {
        $this->uc->checkUserHasRestaurant($promo->restaurantId,true);
        //Todo: validations

        $lastId = $this->db->insert(Promotion::TABLE_NAME,
            Promotion::toDbArray($promo));
        if($lastId != 0){
            $promo->id = $lastId;
            $this->updateLinkedProducts($promo);
            return $this->getPromotion($lastId);
        }
        else
            return $this->db->error();
    }

    /**
     * @param $promo Promotion
     * @return array
     */
    public function updatePromotion($promo)
    {
        $this->uc->checkUserHasRestaurant($promo->restaurantId,true);
        $this->uc->checkUserHasPromotion($promo->id,true);

        //Todo: validations
        $this->db->update(Promotion::TABLE_NAME,
            Promotion::toDbArray($promo),
            [
                Promotion::ID_COL => $promo->id
            ]);

        $this->updateLinkedProducts($promo);
        return $this->db->error();
    }

    /**
     * @param $promo Promotion
     */
    public function updateLinkedProducts($promo)
    {
        $this->db->update(Product::TABLE_NAME,
            [
                Product::PROMOTION_ID_COL => null
            ],
            [
                Product::PROMOTION_ID_COL => $promo->id
            ]);

        $this->db->update(Product::TABLE_NAME,
            [
                Product::PROMOTION_ID_COL => $promo->id
            ],
            [
                Product::ID_COL => $promo->productId
            ]);
    }

    public function deletePromotion($id)
    {
        $this->db->delete(Promotion::TABLE_NAME,
            [
                Promotion::ID_COL => $id
            ]);
        return $this->db->error();
    }

    //endregion

    //region Promotion Usage History
    /**
     * @param $promHisto PromotionUsageHistory
     * @return array
     */
    public function addPromotionUsageHistory($promHisto)
    {
        $this->db->insert(PromotionUsageHistory::TABLE_NAME,
            PromotionUsageHistory::toDbArray($promHisto));
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deletePromotionUsageHistory($id)
    {
        $this->db->delete(PromotionUsageHistory::TABLE_NAME,
            [
                PromotionUsageHistory::ID_COL => $id
            ]);
        return $this->db->error();
    }

    /**
     * @param $promotionId
     * @return bool|int
     */
    public function getPromotionUsageCount($promotionId)
    {
        return $this->db->sum(PromotionUsageHistory::TABLE_NAME,
            PromotionUsageHistory::QUANTITY_COL,
            [
                PromotionUsageHistory::PROMOTION_ID_COL => $promotionId
            ]);
    }
    //endregion

    //region Promotion Types (App Management)
    /**
     * @param $promoType PromotionType
     * @return array|bool
     */
    public function addPromotionType($promoType)
    {
        $lastId = $this->db->insert(PromotionType::TABLE_NAME,
            PromotionType::toDbArray($promoType));

        if($lastId != 0)
            return $this->getPromotionType($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function getPromotionType($id)
    {
        return $this->db->select(PromotionType::TABLE_NAME,
            "*",
            [
                PromotionType::ID_COL => $id
            ]);
    }

    /**
     * @return array|bool
     */
    public function getAllpromotionTypes()
    {
        return $this->db->select(PromotionType::TABLE_NAME,"*");
    }

    /**
     * @param $promoType PromotionType
     * @return array
     */
    public function updatePromotionType($promoType)
    {
        $this->db->update(PromotionType::TABLE_NAME,
            PromotionType::toDbArray($promoType),
            [
                PromotionType::ID_COL => $promoType->id
            ]);
        return $this->db->error();
    }

    public function deletePromotionType($id)
    {
        $this->db->delete(PromotionType::TABLE_NAME,
            [
                PromotionType::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

}
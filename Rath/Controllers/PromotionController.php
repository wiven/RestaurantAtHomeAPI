<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 9/08/2015
 * Time: 14:37
 */

namespace Rath\Controllers;


use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Promotion\PromotionType;
use Rath\Entities\Promotion\PromotionUsageHistory;

class PromotionController Extends ControllerBase
{
    //region Promotions
    public function getPromotion($id)
    {
        return $this->db->select(Promotion::TABLE_NAME,"*",
            [
                Promotion::ID_COL => $id
            ]);
    }

    /**
     * @param $promo Promotion
     */
    public function addPromotion($promo)
    {
        $lastId = $this->db->insert(Promotion::TABLE_NAME,
            Promotion::toDbArray($promo));
        if($lastId != 0)
            return $this->getPromotion($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $promo Promotion
     * @return array
     */
    public function updatePromotion($promo)
    {
        $this->db->update(Promotion::TABLE_NAME,
            Promotion::toDbArray($promo),
            [
                Promotion::ID_COL => $promo->id
            ]);
        return $this->db->error();
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
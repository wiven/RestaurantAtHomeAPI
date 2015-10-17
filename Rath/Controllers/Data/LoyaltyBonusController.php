<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 25-Sep-15
 * Time: 05:40 PM
 */

namespace Rath\Controllers\Data;


use Rath\Entities\ApiResponse;
use Rath\Entities\Product\Product;
use Rath\Entities\Restaurant\LoyaltyBonus;
use Swagger\Annotations\Response;

class LoyaltyBonusController extends ControllerBase
{
//region LoyaltyBonus
    /**
     * @param $lb LoyaltyBonus
     * @return array|bool
     */
    public function createLoyaltyBonus($lb)
    {
        $valid = $lb->validate();
        if($valid->code != 200)
            return $valid;

        $lastId = $this->db->insert(LoyaltyBonus::TABLE_NAME,
            LoyaltyBonus::toDbInsertArray($lb));

        if($lastId != 0)
            return $this->getLoyaltyBonus($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $id
     * @return bool
     */
    public function getLoyaltyBonus($id)
    {
        $result = $this->db->get(LoyaltyBonus::TABLE_NAME,
            [
                "[>]".Product::TABLE_NAME => [
                    LoyaltyBonus::PRODUCT_ID_COL => Product::ID_COL
                ]
            ],
            [
                LoyaltyBonus::TABLE_NAME.".".LoyaltyBonus::ID_COL,
                LoyaltyBonus::TABLE_NAME.".".LoyaltyBonus::RESTAURANT_ID_COL,
                LoyaltyBonus::POINTS_COL,
                LoyaltyBonus::TYPE_COL,
                LoyaltyBonus::AMOUNT_COL,
                LoyaltyBonus::PRODUCT_ID_COL,
                Product::TABLE_NAME.".".Product::NAME_COL."(productName)"
            ],
            [
                LoyaltyBonus::TABLE_NAME.".".LoyaltyBonus::ID_COL => $id
            ]);
        $this->logLastQuery();
        return $result;
    }

    /**
     * @param $lb LoyaltyBonus
     * @return array|bool
     */
    public function updateLoyaltyBonus($lb)
    {
        $valid = $lb->validate();
        if($valid->code != 200)
            return $valid;

        $change = $this->db->update(LoyaltyBonus::TABLE_NAME,
            LoyaltyBonus::toDbUpdateArray($lb));

        if($change != 0)
            return $this->getLoyaltyBonus($lb->id);
        else
            return $this->db->error();
    }

    public function deleteLoyaltyBonus($id)
    {
        return $this->db->delete(LoyaltyBonus::TABLE_NAME,
            [
                LoyaltyBonus::ID_COL => $id
            ]);
    }
    //endregion


}
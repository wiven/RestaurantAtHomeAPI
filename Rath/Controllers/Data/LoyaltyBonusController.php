<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 25-Sep-15
 * Time: 05:40 PM
 */

namespace Rath\Controllers\Data;


use Rath\Entities\User\LoyaltyBonus;

class LoyaltyBonusController extends ControllerBase
{
//region LoyaltyBonus
    /**
     * @param $lb LoyaltyBonus
     * @return array|bool
     */
    public function addLoyaltyBonus($lb)
    {
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
        return $this->db->get(LoyaltyBonus::TABLE_NAME,
            "*",
            [
                LoyaltyBonus::ID_COL => $id
            ]);
    }

    /**
     * @param $lb LoyaltyBonus
     * @return array|bool
     */
    public function updateLoyaltyBonus($lb)
    {
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
<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 24-Sep-15
 * Time: 07:31 PM
 */

namespace Rath\Controllers\Data;


use Rath\Entities\User\LoyaltyPoints;

class LoyaltyPointsController extends ControllerBase
{
    /**
     * @param $lp LoyaltyPoints
     * @return array|bool
     */
    public function insertLoyaltyPointsEntry($lp)
    {
        $lastId = $this->db->insert(LoyaltyPoints::TABLE_NAME,
            LoyaltyPoints::toDbInsertArray($lp)
        );

        if($lastId != 0)
            return $this->getLoyaltyPoints($lastId);
        else
            return $this->db->error();
    }

    public function getLoyaltyPoints($id)
    {
        return $this->db->get(LoyaltyPoints::TABLE_NAME,
            [
                LoyaltyPoints::ID_COL => $id
            ]);
    }

    /**
     * @param $lp LoyaltyPoints
     * @return array|bool
     */
    public function updateLoyaltyPoints($lp)
    {
        $change = $this->db->update(LoyaltyPoints::TABLE_NAME,
            LoyaltyPoints::toDbUpdateArray($lp));

        if($change != 0)
            return $this->getLoyaltyPoints($lp->id);
        else
            return $this->db->error();
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteLoyaltyPoint($id)
    {
        $change = $this->db->delete(LoyaltyPoints::TABLE_NAME,
            [
                LoyaltyPoints::ID_COL => $id
            ]);

        return $change != 0;
    }

}
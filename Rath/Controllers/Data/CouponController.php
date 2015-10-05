<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 02-Oct-15
 * Time: 04:57 PM
 */

namespace Rath\Controllers\Data;


use Rath\Entities\Order\Coupon;
use Rath\Helpers\General;

class CouponController extends ControllerBase
{
    //region Coupon
    public function getCoupon($id)
    {
        return $this->db->get(Coupon::TABLE_NAME,
            "*",
            [
                Coupon::ID_COL => $id
            ]);
    }

    /**
     * @param $coupon Coupon
     * @return array|void
     */
    public function createCoupon($coupon)
    {
        $lastId = $this->db->insert(Coupon::TABLE_NAME,
            Coupon::toDbArray($coupon));

        if($lastId != 0)
            return $this->getCoupon($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $coupon Coupon
     * @return array|bool
     */
    public function updateCoupon($coupon)
    {
        unset($coupon->code);
        $change = $this->db->update(Coupon::TABLE_NAME,
            Coupon::toDbArray($coupon));
        if($change != 0)
            return $this->getCoupon($coupon->id);
        else
            return $this->db->error();
    }

    public function deleteCoupon($id)
    {
        $this->db->delete(Coupon::TABLE_NAME,
            [
                Coupon::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    public function generateCode()
    {
        $res = '';
        do {
            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $res = "RATH" . General::getCurrentYear();
            for ($i = 0; $i < 10; $i++) {
                $res .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
        }while (!$this->validateCode($res));

        return $res;
    }

    /**
     * - Check that a code isn't already used.
     * @param $code
     * @param bool $boolResponse
     * @return bool
     */
    public function validateCode($code,$boolResponse = true)
    {
        $result = $this->db->get(Coupon::TABLE_NAME,
            [
                Coupon::ID_COL, Coupon::CODE_COL
            ],
            [
                Coupon::CODE_COL => $code
            ]);

        if($boolResponse)
            return !isset($result[Coupon::ID_COL]);
        else
            return[
                "available" => !isset($result[Coupon::ID_COL])
            ];

    }
}
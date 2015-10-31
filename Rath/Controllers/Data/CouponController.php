<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 02-Oct-15
 * Time: 04:57 PM
 */

namespace Rath\Controllers\Data;


use Rath\Entities\Order\Coupon;
use Rath\Entities\Order\Order;
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

    public function getCouponByCode($code)
    {
        return $this->db->get(Coupon::TABLE_NAME,
            "*",
            [
                Coupon::CODE_COL => $code
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
        }while (!$this->validateCodeCreation($res));

        return $res;
    }

    /**
     * - Check that a code isn't already used.
     * @param $code
     * @param bool $boolResponse
     * @return bool
     */
    public function validateCodeCreation($code,$boolResponse = true)
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

    /**
     * @param $code string
     * @return Coupon | null
     */
    public function checkCodeIsValid($code,$restoId)
    {
        if(empty($code))
            return null;

        $today = General::getCurrentDate();
        /** @var Coupon $coupon */
        $coupon = $this->getCouponByCode($code);
        if(isset($coupon[Coupon::ID_COL]))
            $coupon = Coupon::fromJson($coupon);
        $this->log->debug($coupon);

        if($coupon->restaurantid == $restoId || $coupon->restaurantid == null){
            $this->log->debug("restoId or Null: OK");
            if($today >= $coupon->startDate && $today <= $coupon->endDate){
                $this->log->debug("Data validation: OK");
                return $coupon;
            }
        }


        return null;
    }

    /**
     * @param $id int
     * @return bool|int
     */
    public function getCouponUsage($id)
    {
        $count = $this->db->count(Order::TABLE_NAME,
            [
                Order::COUPON_ID
            ],
            [
                Order::COUPON_ID => $id
            ]);
        $this->log->debug($count);
        return $count;
    }
}
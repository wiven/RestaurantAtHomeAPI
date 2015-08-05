<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 21:14
 */

namespace Rath\Controllers;


use Rath\Entities\General\Address;
use Rath\Slim\Middleware\Authorization;

class GeneralController extends ControllerBase
{
    /**
     * @param $id
     * @return array|bool
     */
    public function getAddress($id){
        return $this->db->select(Address::TABLE_NAME,
            [
                Address::ID_COL,
                Address::STREET_COL,
                Address::NUMBER_COL,
                Address::ADDITION_COL,
                Address::POSTCODE_COL,
                Address::CITY_COL
            ],
            [
                Address::ID_COL => $id
            ]);
    }

    /**
     * @param $address Address
     * @return array|bool
     */
    public function addAddress($address){
        $address->userId = Authorization::$userId;
        $lastId = $this->db->insert(Address::TABLE_NAME,
            Address::toDbArray($address)
        );
        if($lastId != 0)
            return $this->getAddress($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $address Address
     * @return array
     */
    public function updateAddress($address){
        $this->db->update(Address::TABLE_NAME,
            Address::toDbArray($address),
            [
                "AND" => [
                    Address::ID_COL => $address->id,
                    Address::USER_ID_COL => Authorization::$userId
                ]
            ]
        );
//        return $this->db->last_query();
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteAddress($id){
        $this->db->delete(Address::TABLE_NAME,
            [
                "AND" => [
                    Address::ID_COL => $id,
                    Address::USER_ID_COL => Authorization::$userId
                ]
            ]
        );
        return $this->db->error();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 21:14
 */

namespace Rath\Controllers\Data;


use Rath\Controllers\Data\ControllerBase;
use Rath\Entities\General\Address;
use Rath\Entities\General\Partner;
use Rath\Slim\Middleware\Authorization;

class GeneralController extends ControllerBase
{
    //region Addresses
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
    //endregion

    //region Partners

    /**
     * @param $part Partner
     * @return array|bool
     */
    public function addPartner($part)
    {
        $lastId = $this->db->insert(Partner::TABLE_NAME,
            Partner::toDbArray($part));
        if($lastId != 0)
            return $this->getPartner($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $partId
     * @return array|bool
     */
    public function getPartner($partId)
    {
        return $this->db->select(Partner::TABLE_NAME,
            "*",
            [
                Partner::ID_COL => $partId
            ]);
    }

    public function getAllPartners()
    {
        return $this->db->select(Partner::TABLE_NAME,
            "*");
    }

    public function getAllPartnersPaged($count,$skip)
    {
        return $this->db->select(Partner::TABLE_NAME,
            "*",
            [
                "LIMIT" => [$count,$skip]
            ]);
    }

    /**
     * @param $part Partner
     * @return array
     */
    public function updatePartner($part)
    {
        $this->db->update(Partner::TABLE_NAME,
            Partner::toDbArray($part),
            [
                Partner::ID_COL => $part->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $partId
     * @return array
     */
    public function deletePartner($partId)
    {
        $this->db->delete(Partner::TABLE_NAME,
            [
                Partner::ID_COL => $partId
            ]);
        return $this->db->error();
    }
    //endregion
}
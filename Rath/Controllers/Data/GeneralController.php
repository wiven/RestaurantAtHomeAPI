<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 21:14
 */

namespace Rath\Controllers\Data;


use Rath\Entities\AppMgt\City;
use Rath\Entities\General\Address;
use Rath\Entities\General\Partner;
use Rath\Entities\General\SocialMediaType;
use Rath\Slim\Middleware\Authorization;

class GeneralController extends ControllerBase
{
    //region Addresses
    /**
     * @param $id
     * @return array|bool
     */
    public function getAddress($id,$public = true){
        $fields = [
            Address::ID_COL,
            Address::STREET_COL,
            Address::NUMBER_COL,
            Address::ADDITION_COL,
            Address::POSTCODE_COL,
            Address::CITY_COL,
            Address::LATITUDE_COL,
            Address::LONGITUDE_COL
        ];

        if(!$public)
            array_push($fields,Address::USER_ID_COL);

        return $this->db->get(Address::TABLE_NAME,
            $fields,
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

    public function getAllPartnersPaged($skip,$top)
    {
        return $this->db->select(Partner::TABLE_NAME,
            "*",
            [
                "LIMIT" => [$skip,$top]
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

    //region Social Media Types (Management)

    /**
     * @param $socType SocialMediaType
     * @return array
     */
    public function addSocialMediaType($socType)
    {
        return $this->db->insert(SocialMediaType::TABLE_NAME,
            SocialMediaType::toDbArray($socType));
    }

    /**
     * @param $id
     * @return bool|array
     */
    public function getSocialMediaType($id)
    {
        return $this->db->get(SocialMediaType::TABLE_NAME,
            "*",
            [
                SocialMediaType::ID_COL => $id
            ]);
    }    
    
    public function getAllSocialMediaTypes()
    {
        return $this->db->select(SocialMediaType::TABLE_NAME,
            "*");
    }

    /**
     * @param $socType SocialMediaType
     * @return array
     */
    public function updateSocialMediaType($socType)
    {
        $this->db->update(SocialMediaType::TABLE_NAME,
            SocialMediaType::toDbArray($socType),
            [
                SocialMediaType::ID_COL => $socType->id
            ]);
        return $this->db->error();
    }

    public function deleteSocialMediaType($id)
    {
        $this->db->delete(SocialMediaType::TABLE_NAME,
            [
                SocialMediaType::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    public function getCity($cityId)
    {
        return $this->db->get(City::TABLE_NAME,
            "*",
            [
                City::ID_COL => $cityId
            ]);
    }
}
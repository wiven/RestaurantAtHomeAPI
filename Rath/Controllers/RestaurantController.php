<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:40
 */

namespace Rath\Controllers;

use Rath\Entities\Restaurant\Holiday;
use Rath\Entities\Restaurant\KitchenType;
use Rath\Entities\Restaurant\OpeningHours;
use Rath\Entities\Restaurant\Restaurant;
use Rath\helpers\MedooFactory;

class RestaurantController extends ControllerBase
{
    //region Restaurant

    /**
     * @param $id
     * @return array|bool
     */
    public function getRestaurant($id){
        return $this->db->select(Restaurant::TABLE_NAME,
            ["*"],
            [
                Restaurant::ID_COL => $id
            ]);
    }

    /**
     * @param $resto Restaurant
     * @return array|bool
     */
    public function addRestaurant($resto){
        $lastId = $this->db->insert(Restaurant::TABLE_NAME,
            Restaurant::restaurantToDbArray($resto)
        );
        if($lastId != 0)
            return $this->getRestaurant($lastId);
        else
            return $this->db->error();

    }

    /**
     * @param $resto Restaurant
     * @return array
     */
    public function updateRestaurant($resto){
        $this->db->update(Restaurant::TABLE_NAME,
            Restaurant::restaurantToDbArray($resto),
            [
                Restaurant::ID_COL => $resto->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteRestaurant($id){
        $this->db->delete(Restaurant::TABLE_NAME,
            [
                Restaurant::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region KitchenType

    /**
     * @param $id
     * @return array|bool
     */
    public function getKitchenType($id){
        $result = $this->db->select(KitchenType::TABLE_NAME,
            [
                KitchenType::ID_COL,
                KitchenType::NAME_COL
            ],
            [
                KitchenType::ID_COL => $id
            ]);
        return $result;
    }

    /**
     * @param $kitchenType KitchenType
     * @return array
     */
    public function addKitchenType($kitchenType){
        $this->db->insert(KitchenType::TABLE_NAME,
            [
                KitchenType::ID_COL => $kitchenType->id,
                KitchenType::NAME_COL => $kitchenType->name
            ]);
        return $this->db->error();
    }

    /**
     * @param $kitchenType KitchenType
     * @return array
     */
    public function updateKitchenType($kitchenType){
        $this->db->update(KitchenType::TABLE_NAME,
            [
                KitchenType::NAME_COL => $kitchenType->name
            ],
            [
                KitchenType::ID_COL => $kitchenType->id
            ]);
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteKitchenType($id){
        $this->db->delete(KitchenType::TABLE_NAME,
            [
                KitchenType::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region Holiday
    /**
     * @param $id
     * @return array|bool
     */
    public function getHoliday($id){
        return $this->db->select(Holiday::TABLE_NAME,
            ["*"],
            [
                Holiday::ID_COL => $id
            ]);
    }

    /**
     * @param $restoId
     * @return array|bool
     */
    public function getHolidays($restoId){
        return $this->db->select(Holiday::TABLE_NAME,
            ["*"],
            [
                Holiday::RESTAURANT_ID_COL => $restoId
            ]);
    }

    /**
     * @param $holiday Holiday
     * @return array|bool
     */
    public function addHoliday($holiday){
        $lastId = $this->db->insert(Holiday::TABLE_NAME,
            Holiday::holidayToDbArray($holiday)
        );
        if($lastId != 0)
            return $this->getHoliday($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $holiday Holiday
     * @return array
     */
    public function updateHoliday($holiday){
        $this->db->update(Holiday::TABLE_NAME,
            Holiday::holidayToDbArray($holiday),
            [
                Holiday::ID_COL => $holiday->id
            ]
        );
        return $this->db->error();
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteHoliday($id){
        $this->db->delete(Holiday::TABLE_NAME,
            [
                Holiday::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    public function getOpeningHour($id){
        return $this->db->select(OpeningHours::TABLE_NAME,
            ["*"],
            [
                OpeningHours::ID_COL => $id
            ]);
    }

    public function getOpeningHours($restoId){
        return $this->db->select(OpeningHours::TABLE_NAME,
            ["*"],
            [
                OpeningHours::RESTAURANT_ID_COL => $restoId
            ]);
    }

    public function addOpeningHour($oh){
        $lastId = $this->db->insert(OpeningHours::TABLE_NAME,
            OpeningHours::toDbArray($oh)
        );
        if($lastId != 0)
            return $this->getOpeningHour($lastId);
        else
            return $this->db->error();
    }

    public function updateOpeningHour($oH){
        $this->db->update(OpeningHours::TABLE_NAME,
            OpeningHours::toDbArray($oH),
            [
                OpeningHours::ID_COL => $oH->id
            ]
        );
        return $this->db->error();
    }

    public function deleteOpeningHour($id){
        $this->db->delete(OpeningHours::TABLE_NAME,
            [
                OpeningHours::ID_COL => $id
            ]);
        return $this->db->error();
    }
}
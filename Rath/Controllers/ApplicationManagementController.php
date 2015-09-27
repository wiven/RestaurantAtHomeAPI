<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 2/08/2015
 * Time: 11:02
 */

namespace Rath\Controllers;

use Rath\Controllers\Data\ControllerBase;
use Rath\Entities\ApiResponse;
use Rath\Entities\AppMgt\City;
use Rath\Entities\AppMgt\DistanceMatrix;

class ApplicationManagementController Extends ControllerBase
{
    public static function GetStatus(){
        return ['ack'=> time()];
    }

    public function calculateDistanceMatrix($provinceId)
    {
        ini_set('max_execution_time', 3000);
        $removed = $this->db->delete(DistanceMatrix::TABLE_NAME,[
            DistanceMatrix::ID_COL.'[!]' => 0
        ]);

        echo '<br> Cleared existing matrix: '.$removed.' rows';

        $cities = $this->db->select(City::TABLE_NAME,
            [
                City::ID_COL,
                City::LATITUDE_COL,
                City::LONGITUDE_COL
            ],
            [
                City::PROVINCE_COL => $provinceId
            ]);

        echo "<br> City count: ".count($cities);

        foreach($cities as $fromCity )
        {
            foreach($cities as $toCity)
            {
                if($fromCity[City::ID_COL] != $toCity[City::ID_COL]) {


//                    echo "<br> from: " . $fromCity[City::ID_COL] . " to " . $toCity[City::ID_COL] . " Distance: " . $this->distance(
//                            $fromCity[City::LATITUDE_COL],
//                            $toCity[City::LATITUDE_COL],
//                            $fromCity[City::LONGITUDE_COL],
//                            $toCity[City::LONGITUDE_COL],
//                            "K");
                    $distance = round($this->distance(
                        $fromCity[City::LATITUDE_COL],
                        $fromCity[City::LONGITUDE_COL],
                        $toCity[City::LATITUDE_COL],
                        $toCity[City::LONGITUDE_COL],
                        "K"
                    ),2);

                    if($distance <= 30){
                        $this->db->insert(DistanceMatrix::TABLE_NAME,
                            [
                                DistanceMatrix::FROM_CITY_ID_COL => $fromCity[City::ID_COL],
                                DistanceMatrix::TO_CITY_ID_COL => $toCity[City::ID_COL],
                                DistanceMatrix::DISTANCE_COL => $distance
                            ]);
                    }
//                    echo '<br>';
//                    var_dump($this->db->last_query());
//                    echo '<br>';
//                    var_dump($this->db->error());
                }
            }
        }

        echo "<br> Finished";
        ini_set('max_execution_time', 30);
    }

    /*
    *
    * Geeft de afstand van A tot B adhv breedte- & lengtegraad
    *
    * @param	float		$lat1		Breedtegraad van A
    * @param	float		$lat2		Breedtegraad van B
    * @param	float		$lon1		Lengtegraad van A
    * @param	float		$lon2		Lengtegraad van B
    * @param	string	$unit		Afstand in kilometer (K) of mijlen (M)
    *
    */
    function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K")
        {
            return ($miles * 1.609344);
        }
        else
        {
            return $miles;
        }

    }
}
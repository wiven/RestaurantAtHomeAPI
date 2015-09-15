<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 9/10/2015
 * Time: 7:42 PM
 */

namespace Rath\Controllers;


use Rath\Controllers\Data\ControllerBase;
use Rath\Controllers\Data\DataControllerFactory;
use Rath\Entities\AppMgt\FilterField;
use Rath\Entities\DynamicClass;
use Rath\helpers\MedooFactory;

class SearchController extends ControllerBase
{
    public function searchProducts($skip, $top, $query)
    {

    }

    /**
     * @param $query
     * @return array
     * @throws \Exception
     */
    public function getFilterFieldsToMedooWhereArray($query)
    {
        $fc = DataControllerFactory::getFilterFieldController();
        $result = [];

        $parameters = explode("&",$query);
        foreach ($parameters as $para)
        {
            $keyValuePair = explode("=",$para);
            $field = $fc->get($keyValuePair[0]);
            //var_dump($field);
            $value = $keyValuePair[1];

            if(strpos($value,",") !== false)
            {
                $result[$field->databaseFieldname] = explode(",",$value);
            }
            elseif(strpos($value,"-") !== false)
            {
                $range = explode("-",$value);
                if(count($range) != 2)
                    throw new \Exception("Invalid Filter range submitted");

                $result[$field->databaseFieldname."[<>]"] = $range;
            }
            else
            {
                //var_dump($value);
                if($field->like)
                    $key = $field->databaseFieldname."[~]";
                else
                    $key = $field->databaseFieldname;

                $result[$key] = $value;
            }
        }


        return
            [
                "AND" => $result
            ];
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
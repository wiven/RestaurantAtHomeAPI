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
}
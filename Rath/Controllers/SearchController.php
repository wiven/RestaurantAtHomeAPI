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

    /**
     * @param $filterFields array
     */
    public function filterFieldsToSQLString($filterFields)
    {
        $medoo = MedooFactory::getMedooInstance();
        $result = " ";
        $firstField = true;
        foreach ($filterFields as $field => $value)
        {
            if(!$firstField)
                $result .= " AND ";

            $result .= $field;

            if(strpos($value,",") !== false)
            {
                $opties = explode(",",$value);
                $result .= " IN (". $medoo->array_quote($opties) .")";
            }
            elseif(strpos($value,"-") !== false)
            {
                $range = explode("-",$value);
                if(count($range) != 2)
                    throw new \Exception("Invalid Filter range submitted");

                if (is_numeric($value[0]) && is_numeric($value[1]))
                {
                    $wheres[] = '(' . $field . ' BETWEEN ' . $value[0] . ' AND ' . $value[1] . ')';
                }
                else
                {
                    $wheres[] = '(' . $field . ' BETWEEN ' . $this->quote($value[0]) . ' AND ' . $this->quote($value[1]) . ')';
                }

            }
            else
            {

            }

            $firstField = false;
        }

    }
}
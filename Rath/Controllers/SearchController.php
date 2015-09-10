<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 9/10/2015
 * Time: 7:42 PM
 */

namespace Rath\Controllers;


use Rath\Controllers\Data\ControllerBase;
use Rath\Entities\AppMgt\FilterField;

class SearchController extends ControllerBase
{
    /**
     * @param $query
     * @return array
     */
    public function getFilterFields($query)
    {
        $filterFields = [];
        $parameters = explode("&",$query);
        foreach ($parameters as $para)
        {
            $keyValuePair = explode("=",$para);
            $result = $this->db->get(FilterField::TABLE_NAME,
                "*",
                [
                    FilterField::ID_COL => $keyValuePair[0]
                ]
            );

            $filterFields[$result[FilterField::DATABASE_FIELDNAME_COL]] = $keyValuePair[1];
        }
        return $filterFields;
    }

    /**
     * @param $filterFields array
     */
    public function filterFieldsToSQLString($filterFields)
    {

    }
}
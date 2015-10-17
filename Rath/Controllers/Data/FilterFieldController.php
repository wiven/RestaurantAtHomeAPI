<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 12-Sep-15
 * Time: 2:36 PM
 */

namespace Rath\Controllers\Data;


use Rath\Entities\AppMgt\FilterField;
use Rath\Helpers\General;

class FilterFieldController Extends ControllerBase
{
    /**
     * @param $id
     * @return FilterField | String
     */
    public function get($id)
    {
        $result = $this->db->get(FilterField::TABLE_NAME,
            "*",
            [
                FilterField::ID_COL => $id
            ]
        );

        if(gettype($result) != General::arrayType or empty($result))
            return $id;
        return FilterField::fromJson($result);
    }
}
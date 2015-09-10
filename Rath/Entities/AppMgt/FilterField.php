<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 9/10/2015
 * Time: 9:32 PM
 */

namespace Rath\Entities\AppMgt;


class FilterField
{
    const TABLE_NAME = "filterfield";

    const ID_COL  = "id";
    const DATABASE_FIELDNAME_COL = "databaseFieldname";

    public $id;
    public $databaseFieldname;
}
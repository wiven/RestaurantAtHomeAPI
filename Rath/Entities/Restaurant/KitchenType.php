<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:31
 */

namespace Rath\Entities\Restaurant;


class KitchenType
{
    const TABLE_NAME = "kitchentype";

    const ID_COL = "id";
    const NAME_COL = "name";

    public $id;
    public $name;
}
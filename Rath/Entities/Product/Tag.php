<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:18
 */

namespace Rath\Entities\Product;


use Rath\Entities\EntityBase;

class Tag extends EntityBase
{
    const TABLE_NAME = "tag";

    const ID_COL = "id";
    const NAME_COL = "name";

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     * used to keep track of usage in the search process.
     */
    public $usage;

    /**
     * Tag constructor.
     */
    public function __construct()
    {
        $this->usage = 0;
    }

    /**
     * @param $payment Tag
     * @return array
     */
    public static function toDbArray($payment){
        return [
            Tag::NAME_COL => $payment->name
        ];
    }

    public function __toString()
    {
        return (string)$this->id;
    }
}
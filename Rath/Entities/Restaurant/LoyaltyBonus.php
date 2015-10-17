<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 24-Sep-15
 * Time: 07:24 PM
 */

namespace Rath\Entities\Restaurant;


use Rath\Controllers\Data\DataControllerFactory;
use Rath\Entities\ApiResponse;
use Rath\Entities\EntityBase;
use Rath\Entities\Product\Product;

class LoyaltyBonus extends EntityBase
{
    const TABLE_NAME = "loyaltybonus";

    const ID_COL = "id";
    const PRODUCT_ID_COL ="productid";
    const RESTAURANT_ID_COL = "restaurantid";
    const TYPE_COL = "type";
        const TYPE_VALUE_PERC = 'Percentage';
        const TYPE_VALUE_AMOUNT = 'Amount';
        const TYPE_VALUE_PRODUCT = 'Product';
    const AMOUNT_COL ="amount";
    const POINTS_COL = "points";

    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $restaurantid;
    /**
     * @var int
     */
    public $points;
    /**
     * @var string
     */
    public $type;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var int|null
     */
    public $productid;

    //region Validation
    /**
     * @return bool
     */
    public function validatePoints()
    {
        return ($this->points >= 0);
    }

    /**
     * @return bool
     */
    public function validateAmount()
    {
        return ($this->amount >= 0.0);
    }

    /**
     * @return ApiResponse
     */
    public function validate()
    {
        $pc = DataControllerFactory::getProductController();

        $response = new ApiResponse();
        $response->code = 200;

        switch($this->type) {
            case LoyaltyBonus::TYPE_VALUE_PRODUCT:
                if (!isset($this->productid)) {
                    $response->code = 406;
                    $response->message = "Product Id must be set";
                    return $response;
                }
                $prod = $pc->getProduct($this->productid);
                if (!isset($prod[Product::ID_COL])) {
                    $response->code = 406;
                    $response->message = "Product doesn't exist";
                }
                break;
        }

        //$this->log->debug($this);
        $this->log->debug("validatePoint: ".json_encode($this->validatePoints()));
        $this->log->debug("validateAmount: ".json_encode($this->validateAmount()));

        if(!$this->validatePoints() or !$this->validateAmount())
        {
            $response->code = 406;
            $response->message = "amount & points must be set";
            return $response;
        }
        return $response;
    }
    //endregion

    //region Database
    /**
     * @param $lp LoyaltyBonus
     * @return array
     */
    public static function toDbInsertArray($lp)
    {
        $data =  [
            self::RESTAURANT_ID_COL => $lp->restaurantid,
            self::POINTS_COL => $lp->points,
            self::TYPE_COL => $lp->type,
            self::AMOUNT_COL => $lp->amount,
            self::PRODUCT_ID_COL => $lp->productid
        ];

        return $data;
    }

    /**
     * @param $lp LoyaltyBonus
     * @return array
     */
    public static function toDbUpdateArray($lp)
    {
        $data = [
            self::TYPE_COL => $lp->type,
            self::AMOUNT_COL => $lp->amount,
            self::POINTS_COL => $lp->points,
            self::PRODUCT_ID_COL => $lp->productid
        ];

        return $data;
    }
    //endregion

}
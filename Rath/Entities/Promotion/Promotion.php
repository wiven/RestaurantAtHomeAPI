<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 3/08/2015
 * Time: 18:06
 */

//namespace Rath\Entities\Promotion;


class Promotion
{
    const TABLE_NAME = "promotion";

    const ID_COL = "id";
    const PROMOTION_TYPE_ID_COL = "promotiontypeId";
    const RESTAURANT_ID_COL = "restaurantId";
    const PRODUCT_ID_COL = "productId";

    const FROM_DATE_COL = "fromDate";
    const TO_DATE_COL = "toDate";
    const DESCRIPTION_COL = "description";
    const DISCOUNT_TYPE_COL = "discountType";
        const DISCOUNT_TYPE_VAL_PERS = "Percentage";
        const DISCOUNT_TYPE_VAL_AMOUNT = "Amount";
    const DISCOUNT_AMOUNT_COL = "discountAmount";
    const NEW_PRODUCT_PRICE = "newProductPrice";

}

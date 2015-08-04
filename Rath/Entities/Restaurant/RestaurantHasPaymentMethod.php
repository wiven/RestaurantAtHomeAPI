<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 4/08/2015
 * Time: 18:39
 */

namespace Rath\Entities\Restaurant;


class RestaurantHasPaymentMethod
{
    const TABLE_NAME = "restaurant_has_paymentmethod";

    const RESTAURANT_ID_COL = "restaurantId";
    const PAYMENT_METHOD_ID_COL = "paymentmethodId";
}
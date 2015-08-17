<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 11/08/2015
 * Time: 19:29
 */

namespace Rath\Controllers\Data;


use Rath\Entities\General\SocialMediaType;
use Rath\Entities\Order\OrderStatus;

class DefaultDataController extends ControllerBase
{
    public function insertOrderStatus()
    {
        $this->db->insert(OrderStatus::TABLE_NAME,
            [
                [
                    OrderStatus::ID_COL => OrderStatus::val_New,
                    OrderStatus::NAME_COL => "New"
                ],
                [
                    OrderStatus::ID_COL => OrderStatus::val_Accepted,
                    OrderStatus::NAME_COL => "Accepted"
                ],
                [
                    OrderStatus::ID_COL => OrderStatus::val_InProgress,
                    OrderStatus::NAME_COL => "In Progress"
                ],
                [
                    OrderStatus::ID_COL => OrderStatus::val_Ready,
                    OrderStatus::NAME_COL => "Ready"
                ],
                [
                    OrderStatus::ID_COL => OrderStatus::val_OnRoute,
                    OrderStatus::NAME_COL => "On Route"
                ],
                [
                    OrderStatus::ID_COL => OrderStatus::val_Finished,
                    OrderStatus::NAME_COL => "Finished"
                ],
            ]);
        return $this->db->error();
    }

    public function insertSocialMediaTypes()
    {
        $this->db->insert(SocialMediaType::TABLE_NAME,
            [
                [
                    SocialMediaType::ID_COL => SocialMediaType::val_Facebook,
                    SocialMediaType::NAME_COL => "Facebook"
                ],
                [
                    SocialMediaType::ID_COL => SocialMediaType::val_Twitter,
                    SocialMediaType::NAME_COL => "Twitter"
                ],
                [
                    SocialMediaType::ID_COL => SocialMediaType::val_Instagram,
                    SocialMediaType::NAME_COL => "Instagram"
                ]
            ]);
        return $this->db->error();
    }
}
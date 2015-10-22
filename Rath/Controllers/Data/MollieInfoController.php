<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 08-Oct-15
 * Time: 07:34 PM
 */

namespace Rath\Controllers\Data;


use Rath\Entities\Order\MollieInfo;

class MollieInfoController extends ControllerBase
{
    /**
     * @param $info MollieInfo
     * @param bool|true $idOnly
     * @return array|bool|int
     */
    public function createMollieInfo($info,$idOnly = true)
    {
        $lastId = $this->db->insert(MollieInfo::TABLE_NAME,
            MollieInfo::toDbArray($info));

        if($lastId != 0)
            if(!$idOnly)
                return $this->getMollieInfo($lastId);
            elseif($idOnly)
                return $lastId;

        return $this->db->error();
    }

    /**
     * @param $id
     * @return bool | array
     */
    public function getMollieInfo($id)
    {
        return $this->db->get(MollieInfo::TABLE_NAME,
            "*",
            [
                MollieInfo::ID_COL => $id
            ]);
    }

    public function getMollieInfoPublic($id)
    {
        return $this->db->get(MollieInfo::TABLE_NAME,
            [
                MollieInfo::MODE_COL,
                MollieInfo::METHOD_COL
            ],
            [
                MollieInfo::ID_COL => $id
            ]);
    }

    /**
     * @param $info MollieInfo
     * @return array|bool
     */
    public function updateMollieInfo($info)
    {
        $change = $this->db->update(MollieInfo::TABLE_NAME,
            MollieInfo::toDbArray($info),
            [
                MollieInfo::ID_COL => $info->id
            ]);

        if($change != 0)
            return $this->getMollieInfo($info->id);

        return $this->db->error();
    }

    public function deleteMollieInfo($id)
    {
        $this->db->delete(MollieInfo::TABLE_NAME,
            [
                MollieInfo::ID_COL => $id
            ]);
        return $this->db->error();
    }
}
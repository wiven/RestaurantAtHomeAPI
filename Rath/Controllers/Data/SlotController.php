<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 20-Sep-15
 * Time: 02:13 PM
 */

namespace Rath\Controllers\Data;


use Rath\Entities\ApiResponse;
use Rath\Entities\Restaurant\OpeningHours;
use Rath\Entities\Slots\SlotTemplate;
use Rath\Entities\Slots\SlotTemplateChange;
use Rath\Helpers\General;

class SlotController extends ControllerBase
{
    /**
     * @var UserController
     */
    private $uc;

    /**
     * ProductController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->uc = DataControllerFactory::getUserController();
    }

    //region Slot Template
    public function getSlotTemplate($id)
    {
        return $this->db->get(SlotTemplate::TABLE_NAME,
            "*",
            [
                SlotTemplate::ID_COL => $id
            ]);
    }

    /**
     * @param $st SlotTemplate
     * @return array|bool
     */
    public function addSlotTemplate($st)
    {
        $this->uc->checkUserHasRestaurant($st->restaurantId,true);

        $lastId = $this->db->insert(SlotTemplate::TABLE_NAME,
            SlotTemplate::toDbArray($st));

        if($lastId != 0)
            return $this->getSlotTemplate($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $st SlotTemplate
     * @return bool|int
     */
    public function updateSlotTemplate($st)
    {
        $this->uc->checkUserHasSlotTemplate($st->id,true);
        $this->uc->checkUserHasRestaurant($st->restaurantId,true);

        $this->db->update(SlotTemplate::TABLE_NAME,
            SlotTemplate::toDbArray($st),
            [
                SlotTemplate::ID_COL => $st->id
            ]);
        return $this->db->error();
    }

    public function deleteSlotTemplate($id)
    {
        $this->db->delete(SlotTemplate::TABLE_NAME,
            [
                SlotTemplate::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region Slot Template Change
    public function getSlotTemplateChange($id)
    {
        return $this->db->get(SlotTemplateChange::TABLE_NAME,
            "*",
            [
                SlotTemplateChange::ID_COL => $id
            ]);
    }

    /**
     * @param $st SlotTemplateChange
     * @return array|bool
     */
    public function addSlotTemplateChange($st)
    {
        $this->uc->checkUserHasSlotTemplate($st->slottemplateId,true);

        $lastId = $this->db->insert(SlotTemplateChange::TABLE_NAME,
            SlotTemplateChange::toDbArray($st));

        if($lastId != 0)
            return $this->getSlotTemplateChange($lastId);
        else
            return $this->db->error();
    }

    /**
     * @param $st SlotTemplateChange
     * @return bool|int
     */
    public function updateSlotTemplateChange($st)
    {
        $this->uc->checkUserHasSlotTemplate($st->slottemplateId,true);
        $this->uc->checkUserHasSlotTemplateChange($st->id,true);

        $this->db->update(SlotTemplateChange::TABLE_NAME,
            SlotTemplateChange::toDbArray($st),
            [
                SlotTemplateChange::ID_COL => $st->id
            ]);
        return $this->db->error();
    }

    public function deleteSlotTemplateChange($id)
    {
        $this->db->delete(SlotTemplateChange::TABLE_NAME,
            [
                SlotTemplateChange::ID_COL => $id
            ]);
        return $this->db->error();
    }
    //endregion

    //region Auto Generation
    /**
     * @param $restoId
     * @param $slotSize
     * Size of a slot in min.
     * @return array
     * @throws \Exception
     */
    public function generateSlotsForRestaurantOpeningHours($restoId, $slotSize,$slotQuantity)
    {
        $response = new ApiResponse();
        $open = $this->db->select(OpeningHours::TABLE_NAME,
            "*",
            [
                "AND" => [
                    OpeningHours::OPEN_COL => true,
                    OpeningHours::RESTAURANT_ID_COL => $restoId
                ]
            ]);
        if(count($open) == 0){
            $response->code = 300;
            $response->message = "No openinghours defined for this restaurant";
            return $response;
        }

        $slots = $this->db->select(SlotTemplate::TABLE_NAME,
            [
                SlotTemplate::ID_COL
            ],
            [
                SlotTemplate::RESTAURANT_ID_COL => $restoId
            ]);

        if(count($slots) != 0){
            $response->code = 310;
            $response->message = "There are already slots defined for this restaurant. You can only use this function once.";
            return $response;
        }


        try {
            $this->db->pdo->beginTransaction();
//            echo "before Fore: #".count($open);
            foreach ($open as $dayOpen) {

                $dayOpen = (object)$dayOpen;
//                var_dump($dayOpen);
                //echo "After Vardump: ".strtotime($dayOpen->fromTime);
                $time = strtotime($dayOpen->fromTime);
//                echo " Time: ".$time;
                while ($time < strtotime($dayOpen->toTime)) {
//                    echo " time compare: ".($time < strtotime($dayOpen->toTime));
//                    echo " loop with time: ".date(General::timeFormat,$time);
                    $st = new SlotTemplate();
                    $st->restaurantId = $restoId;
                    $st->dayOfWeek = $dayOpen->dayOfWeek;
                    $st->fromTime = date(General::timeFormat, $time);
//                    echo " SlotSize: ".$slotSize;
//                    echo " Time upcount: ".date(General::timeFormat,strtotime('+'.$slotSize.' minutes', $time));
                    $st->toTime = date(General::timeFormat, strtotime('+'.$slotSize.' minutes', $time));
                    $st->quantity = $slotQuantity;

                    $time = strtotime($st->toTime);
//                    echo " Time: ".$time;
//                    echo " DO ToTime: ".strtotime($dayOpen->toTime);
//                    echo " Compare Time Overload: ".($st->toTime > strtotime($dayOpen->toTime));
                    if($time > strtotime($dayOpen->toTime)){
                        $st->toTime = date(General::timeFormat,strtotime($dayOpen->toTime));
//                        echo " Correct ToTime";
                    }
                    else if($st->toTime == strtotime($dayOpen->toTime))
                        $time = date(General::timeFormat, strtotime('+30 minutes', $time)); //prevent empty slot

                    $this->addSlotTemplate($st);
                }
            }
            $this->db->pdo->commit();
        } catch (\Exception $e) {
            $this->db->pdo->rollBack();
            throw $e;
//            var_dump($e);
        }
        return ["Status" => "Success"];
        //var_dump($open);
    }
    //endregion

}
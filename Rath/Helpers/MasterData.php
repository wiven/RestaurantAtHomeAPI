<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 25/07/2015
 * Time: 20:40
 */

namespace Rath\helpers;

use Rath\Entities\General\Address;
use Rath\Entities\Product\Product;
use Rath\Entities\Product\ProductStock;
use Rath\Entities\Promotion\Promotion;
use Rath\Entities\Restaurant\Holiday;
use Rath\Entities\Restaurant\OpeningHours;
use Rath\Entities\Restaurant\Restaurant;
use Rath\Entities\User\User;
use Rath\Entities\User\UserPermission;
use Rath\Controllers\UserController;
use Rath\Controllers\UserPermissionController;
use Exception;

//require_once APP_PATH.'/Rath/Libraries/medoo.php';
//
////include_once 'MedooFactory.php';
//require_once APP_PATH . '/Rath/Entities/User.php';
//require_once APP_PATH.'/Rath/Entities/Base.php';
//require_once APP_PATH . '/Rath/Entities/UserPermission.php';
//require_once 'MedooFactory.php';

class MasterData
{
    static  function CreateDemoData(){

        MasterData::InsertDemoUsers();
        MasterData::InsertDefaultRoutPermissions();

    }


    private static function InsertDemoUsers(){
        $user = new User();
        $user->name = "Thomas";
        $user->password = md5("10Centimeter");
        $user->surname = "De Pauw";
        $user->email = "thdepauw@hotmail.com";
        $user->phoneNo = "+154689456489";
        $user->type = "Client";
        $user->admin = 0;
        $user->socialLogin = false;
        UserController::CreateUser($user);

        $user = new User();
        $user->name = "Wim";
        $user->password = md5("10Centimeter");
        $user->surname = "Vandevenne";
        $user->email = "wim.vandevenne@gmail.com";
        $user->phoneNo = "+21354891384";
        $user->type = "Client";
        $user->socialLogin = 1;
        $user->admin = true;
        UserController::CreateUser($user);

        $user = new User();
        $user->name = "Frederik";
        $user->password = md5("10Centimeter");
        $user->surname = "Deroover";
        $user->email = "derooverfrederik@gmail.com";
        $user->phoneNo = "+158946548942";
        $user->type = "Client";
        $user->socialLogin = 0;
        $user->admin = true;
        UserController::CreateUser($user);
    }

    /**
     * @throws Exception
     */
    private static function InsertDefaultRoutPermissions(){
        $permissions = [];
        $row = 1;
        if (($handle = fopen(APP_PATH."/Resources/Database/DefaultUserPermissions.csv.txt", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
//                $num = count($data);
//                echo "<p> $num fields in line $row: <br /></p>\n";
//                for ($c=0; $c < $num; $c++) {
//                    echo $data[$c] . "<br />\n";
//                }
                $row++;
                //Add to array
                $perm = new UserPermission();
                $perm->userType = $data[0];
                $perm->route = $data[1];
                $permissions[$row] = $perm;
            }
            fclose($handle);
        } else{
            throw new Exception("Unable to read Permission CSV");
        }
        var_dump($permissions);
        UserPermissionController::InsertUserPermissionSets($permissions);
    }

    public static function echoObjectContent(){
        echo json_encode([
            MasterData::echoAddress(),
            MasterData::echoRestaurant(),
            MasterData::echoHoliday(),
            MasterData::echoOpeningHour(),
            MasterData::echoProduct(),
            MasterData::echoProductStock(),
            MasterData::echoPromotion()
        ]);

    }

    private static function echoAddress(){
        $address = new Address();
        $address->street = "Hoekskensstraat";
        $address->number = 3;
        $address->addition = "102";
        $address->postcode = "9080";
        $address->city = "Lochristi";
        $address->userId = 1;
        return $address;
    }

    private static function echoRestaurant(){
        $resto = new Restaurant();
        $resto->addressId = 4;
        $resto->kitchentypeId = 1;
        $resto->phone = "0494168007";
        $resto->email ="Restau@email.com";
        $resto->url = "http://test.be";
        $resto->photo = "url to photo";
        $resto->dominatingColor = "#5584";
        $resto->comment = "no comments will have a blank result";
        return $resto;
    }

    private static function echoHoliday(){
        $date = new \DateTime('now');
        $holiday = new Holiday();
        $holiday->restaurantId = 2;
        $holiday->fromDate = $date->format("Y-m-d H:i:s");
        $holiday->toDate = $date->format("Y-m-d H:i:s");
        return $holiday;
    }

    private static function echoOpeningHour(){
        $time = new \DateTime('now');
        $openingHour = new OpeningHours();
        $openingHour->restaurantId = 2;
        $openingHour->dayOfWeek = 0;
        $openingHour->fromTime = $time->format("H:i:s");
        $openingHour->toTime = $time->format("H:i:s");
        $openingHour->open = 1;
        return $openingHour;
    }

    private static function echoProduct(){
        $prod = new Product();
        $prod->restaurantId = 4;
        $prod->producttypeId = 1;
        $prod->name = 'Tomaten soep met brood';
        $prod->description = "Een heerlijk tomatensoepje afgekruid met pepermix voor de pittege smaak";
        $prod->price = 2.49;
        $prod->slots = 1;
        return $prod;
    }

    private static function echoProductStock(){
        $prods = new ProductStock();
        $prods->productId = 1;
        $prods->amount = 5;
        $prods->dayOfWeek = 0;
        return $prods;
    }

    private static function echoPromotion()
    {
        $date = new \DateTime('now');
        $promo = new Promotion();
        $promo->promotiontypeId = 1;
        $promo->restaurantId = 4;
        $promo->productId =1;
        $promo->fromDate = $date->format("Y-m-d");
        $promo->toDate = $date->format("Y-m-d");
        $promo->description = "Laatste van het sezoen!";
        $promo->discountType = Promotion::DISCOUNT_TYPE_VAL_PERS;
        $promo->discountAmount = 10;
        $promo->newProductPrice = 8.99;
        return $promo;
    }
}
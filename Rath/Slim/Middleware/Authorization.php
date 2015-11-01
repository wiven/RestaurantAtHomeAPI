<?php

/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 29/07/2015
 * Time: 19:36
 */

//require_once APPLICATION_PATH . '/Slim/Middleware.php';

namespace Rath\Slim\Middleware;

use Logger;
use Rath\Controllers\Data\DataControllerFactory;
use Rath\Controllers\Data\UserController;
use Rath\Controllers\UserPermissionController;
use Rath\Entities\User\User;
use Slim\Route;


class Authorization extends \Slim\Middleware
{
     // Variable that will be checked for authenticity.
    const restoId = "aRestoId";
    const orderId = "aOrderId";
    const productId = "aProductId";
    const promotionId = "aPromotionId";
    const slotTemplateId = "aSlotTemplateId";
    const slotTemplateChangeId = "aSlotTemplateChangeId";

    //Groupings
    //Restaurant mgt
    const dashboard = "/dashboard";
    const photo = "/photo";
    const slots = "/slots";
    const loyaltybonus = "/loyaltybonus";
    const coupon = "/coupon";
    const promotion = "/promotion";
    const product = "/product";
    const restaurant = "/restaurant";
    //Admin only
    const manage = "/manage";
    const history = "/history";


    //public route name
    const publicRoute = "public";

    //block routes that might not be used for security reasons
    const blockedRoute = "blocked";

    /**
     * @var int
     */
    public static $userId = 0;

    /**
     * @var bool
     */
    public static $globalAdmin = false;

    /**
     * @var User
     */
    public static $user;

    /**
     * @var string
     */
    private $hash = "";

    /**
     * @var Logger
     */
    private $log;
    /**
     * @var UserController
     */
    private $userController;

    public function __construct(){
        $this->log = Logger::getLogger(__CLASS__);
    }

    public function initUserController()
    {
        if(empty($this->userController))
            $this->userController = DataControllerFactory::getUserController();
    }

    public function call(){
        $this->initUserController();
        $this->app->hook('slim.before.dispatch', array($this, 'onBeforeDispatch'));
        $this->next->call();
    }

    public function onBeforeDispatch(){
        $this->loadUserIdFromHash();


        if(isset(self::$user->admin))
            if(self::$user->admin)
                return;

//        return; //Uncomment this to disable security

        $route = $this->app->router()->getCurrentRoute();
        if($route->getName() == self::publicRoute)
            return;

        if($route->getName() == self::blockedRoute)
            $this->SendSecurityMessage(self::blockedRoute);

        $this->checkGroups();
        $this->checkParameters($route);
    }


    private function loadUserIdFromHash(){
        $headers = $this->app->request->headers;
        $this->hash = $headers["hash"];
        if(!empty($this->hash)){
            $result = $this->userController->getUserIdByHash($this->hash);
            $this->log->debug($result);
            if(!empty($result))
            {
                /** @var User $user */
                $user = User::fromJson($result);
                self::$userId = $user->id;
                self::$globalAdmin = $user->admin;
                self::$user = $user;
            }
        }

        $this->log->debug(self::$user);
    }

    /**
     * @param $route Route
     */
    private function checkParameters($route)
    {
        $parameters = $route->getParams();

//        $this->log->debug($parameters);

        if(isset($parameters[self::restoId])){
            if(!$this->userController->checkUserHasRestaurant($parameters[self::restoId]))
                $this->SendSecurityMessage();
        }

        if(isset($parameters[self::orderId])){
            if(!$this->userController->checkUserHasOrder($parameters[self::orderId]))
                $this->SendSecurityMessage();
        }

        if(isset($parameters[self::productId])){
            if(!$this->userController->checkUserHasProduct($parameters[self::productId]))
                $this->SendSecurityMessage();
        }

        if(isset($parameters[self::promotionId])){
            if(!$this->userController->checkUserHasPromotion($parameters[self::promotionId]))
                $this->SendSecurityMessage();
        }

        if(isset($parameters[self::slotTemplateId])){
            if(!$this->userController->checkUserHasSlotTemplate($parameters[self::slotTemplateId]))
                $this->SendSecurityMessage();
        }

        if(isset($parameters[self::slotTemplateChangeId])){
            if(!$this->userController->checkUserHasSlotTemplateChange($parameters[self::slotTemplateChangeId]))
                $this->SendSecurityMessage();
        }
    }


    public function checkGroups()
    {
        $toCheck =[
            self::dashboard,
            self::manage,
            self::photo,
            self::slots,
            self::loyaltybonus,
            self::coupon,
            self::promotion,
            self::product
        ];

        $this->log->debug($_SERVER["REQUEST_URI"]);
        foreach($toCheck as $group){
            $this->log->debug($group);
            $this->log->debug(strpos($_SERVER["REQUEST_URI"],$group));

            if(strpos($_SERVER["REQUEST_URI"],$group) !== false)
            {
                if(empty(self::$user))
                    $this->SendSecurityMessage("authentication");

                $this->log->debug("Checking: ".$group);
                switch($group)
                {
                    case self::slots:
                    case self::loyaltybonus:
                    case self::coupon:
                    case self::promotion:
                    case self::photo:
                    case self::product:
                    case self::restaurant:
                    case self::dashboard:
                        if(self::$user->type != User::TYPE_VALUE_RESTO)
                            $this->SendSecurityMessage($group);
                        break;

                    default:
                        $this->SendSecurityMessage($group);
                }
                break;
            }
        }
    }


    private function SendSecurityMessage($routeName = "general")
    {
        $response = UserPermissionController::GetPermissionErrorMessage($routeName);
        $this->app->halt(401,json_encode($response));
    }
}

//if(empty($route->getName()))
//    return; //Skip all unamed routes. //TODO: build in Role model
//$publicRoutes = [
//    API_LOGIN_ROUTE,
//    API_UNAUTHORISED_ROUTE,
//    API_PING_ROUTE,
//    API_USER_CREATE_ROUTE,
//    API_MASTERDATA_ROUTE
//];
//return;
////die(var_dump($routeName));
//if(!in_array($route->getName(),$publicRoutes)){
//    if(!$this->uc->checkUserPermissions($this->hash,$route)){
//        $response = UserPermissionController::GetPermissionErrorMessage($route->getName());
//        $this->app->halt(401,json_encode($response));
////                $res = $this->app->response();
////                $res->status(401);
////                $res->body("Unauthorised");
////                $toUrl = $this->app->urlFor(API_UNAUTHORISED_ROUTE,['route',$route->getName()]);
////                die(var_dump($toUrl));
////                $this->app->redirect($toUrl);
////                $this->app->redirect('/unauthorised/'.$route->getName());
//        //throw new HttpUnauthorizedException();
//    }
//}
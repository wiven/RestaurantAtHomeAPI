<?php

/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 29/07/2015
 * Time: 19:36
 */

//require_once APPLICATION_PATH . '/Slim/Middleware.php';

namespace Rath\Slim\Middleware;

use Rath\Controllers\Data\DataControllerFactory;
use Rath\Controllers\Data\UserController;
use Rath\Controllers\UserPermissionController;
use Rath\Entities\User\User;


class Authorization extends \Slim\Middleware
{
    /**
     * @var int
     */
    public static $userId = 0;
    private $hash = "";
    /**
     * @var UserController
     */
    private $userController;

    public function __construct(){
        $this->userController = DataControllerFactory::getUserController();
    }

    public function call(){
        $this->app->hook('slim.before.dispatch', array($this, 'onBeforeDispatch'));
//        $this->app->hook('slim.before.dispatch', function() use ($this){
//            $this->app->
//        });


        $this->next->call();
    }

    public function onBeforeDispatch(){
        $this->loadUserIdFromHash();

        $route = $this->app->router()->getCurrentRoute();
        $routeName = $route->getName();
        if(empty($routeName))
            return; //Skip all unamed routes. //TODO: build in Role model
        $publicRoutes = [
            API_LOGIN_ROUTE,
            API_UNAUTHORISED_ROUTE,
            API_PING_ROUTE,
            API_USER_CREATE_ROUTE,
            API_MASTERDATA_ROUTE
        ];

        //die(var_dump($routeName));
        if(!in_array($routeName,$publicRoutes)){
            if(!$this->userController->checkUserPermissions($this->hash,$route->getName())){
                $response = UserPermissionController::GetPermissionErrorMessage($routeName);
                $this->app->halt(401,json_encode($response));
//                $res = $this->app->response();
//                $res->status(401);
//                $res->body("Unauthorised");
//                $toUrl = $this->app->urlFor(API_UNAUTHORISED_ROUTE,['route',$route->getName()]);
//                die(var_dump($toUrl));
//                $this->app->redirect($toUrl);
//                $this->app->redirect('/unauthorised/'.$route->getName());
                //throw new HttpUnauthorizedException();
            }
        }
    }

    private function loadUserIdFromHash(){
        $headers = $this->app->request->headers;
        $this->hash = $headers["hash"];
        if(!empty($this->hash)){
            $result = $this->userController->getUserIdByHash($this->hash);
            if(!empty($result))
                Authorization::$userId = intval($result[0][User::ID_COL]);
        }
        else
            Authorization::$userId = -1;
    }
}
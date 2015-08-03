<?php

/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 29/07/2015
 * Time: 19:36
 */

//require_once APPLICATION_PATH . '/Slim/Middleware.php';

namespace Rath\Slim\Middleware;

use Rath\Controllers\UserController;
use Rath\Controllers\UserPermissionController;


class Authorization extends \Slim\Middleware
{
    public function __construct(){

    }

    public function call(){
        $this->app->hook('slim.before.dispatch', array($this, 'onBeforeDispatch'));
//        $this->app->hook('slim.before.dispatch', function() use ($this){
//            $this->app->
//        });


        $this->next->call();
    }

    public function onBeforeDispatch(){
        $route = $this->app->router()->getCurrentRoute();
        $routeName = $route->getName();
        $publicRoutes = [
            API_LOGIN_ROUTE,
            API_UNAUTHORISED_ROUTE,
            API_PING_ROUTE,
            API_USER_CREATE_ROUTE,
            API_MASTERDATA_ROUTE
        ];
        //die(var_dump($routeName));
        if(!in_array($routeName,$publicRoutes)){
            $headers = $this->app->request->headers;
            if(!UserController::CheckUserPermissions($headers["hash"],$route->getName())){
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
}
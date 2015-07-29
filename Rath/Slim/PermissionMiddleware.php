<?php

/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 29/07/2015
 * Time: 19:36
 */
require_once APPLICATION_PATH.'/Slim/Middleware.php';

class PermissionMiddleware extends \Slim\Middleware
{
    public function __construct(){

    }

    public function call(){
        $this->app->hook('slim.before.dispatch', array($this, 'onBeforeDispatch'));
        $this->next->call();
    }

    public function onBeforeDispatch(){
        $route = $this->app->router()->getCurrentRoute();
        if($route->getName() != API_LOGIN_ROUTE || $route->getName() != API_UNAUTHORISED_ROUTE){
            $headers = $this->app->request->headers;
            if(!UserController::CheckUserPermissions($headers["hash"],$route->getName())){
//                $res = $this->app->response();
//                $res->status(401);
//                $res->body("Unauthorised");
                $this->app->redirect('/unauthorised/'.$route->getName());
            }
        }
    }
}
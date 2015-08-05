<?php
/**
 * @SWG\Info(title="RestaurantAtHome API", version="0.1")
 */


if (!defined('APP_PATH'))
    define('APP_PATH', realpath(__DIR__ ));

if(!defined('APP_MODE'))
    define('APP_MODE', 'LOCAL');

require_once __DIR__.'/vendor/autoload.php';

use Rath\helpers\CrossDomainAjax;


\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->setName("RestaurantAtHomeApi");
$app->add(new \Rath\Slim\Middleware\Authorization()); //TODO; Authentication check


// Inject as Slim application middleware
//$app->add(new \Slagger\Slagger('/v1/docs', 'Rath'));

//<editor-fold desc="Application status">

const API_PING_ROUTE = "ping";

$app->get('/ping', function() use ($app){
    $status = Rath\Controllers\ApplicationManagementController::GetStatus();
    CrossDomainAjax::PrintCrossDomainCall($app,$status);
})->name(API_PING_ROUTE);
//</editor-fold>

//<editor-fold desc="Application Managment">

const API_MASTERDATA_ROUTE = "masterdata";
const API_UNAUTHORISED_ROUTE = "unauthorised";


$app->group('/masterdata', function() use ($app){
    $app->POST('', function() use ($app){
        Rath\helpers\MasterData::CreateDemoData();
        Rath\helpers\CrossDomainAjax::PrintCrossDomainCall($app,['Datageneration success']);
    })->name(API_MASTERDATA_ROUTE);

    $app->get('/echodataobjects', function() use ($app){
        \Rath\helpers\MasterData::echoObjectContent();
    });
});
/**
 * @SWG\Post(
 *     path="/api/resource.json",
 *     @SWG\Response(response="200", description="An example resource")
 * )
 */



$app->get('/unauthorised/:route', function($route) use ($app){
    Rath\helpers\CrossDomainAjax::PrintCrossDomainCall($app,Rath\Controllers\UserPermissionController::GetPermissionErrorMessage($route));
})->name(API_UNAUTHORISED_ROUTE);

$app->group('/kitchenType' , function() use ($app){
    $resto = new \Rath\Controllers\RestaurantController();

    $app->get('/:id', function($id) use ($app,$resto){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $resto->getKitchenType($id)
        );
    });

    $app->post('', function() use ($app,$resto){
        $kt = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $resto->addKitchenType($kt)
        );
    });

    $app->put('',function() use ($app,$resto){
        $kt = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $resto->updateKitchenType($kt)
        );
    });

    $app->get('/delete/:id',function($id) use ($app,$resto){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $resto->deleteKitchenType($id)
        );
    });
});

//</editor-fold>

//<editor-fold desc="User managment">

const API_LOGIN_ROUTE = "login";

const API_USER_CREATE_ROUTE = "userCreate";
const API_USER_GET_ROUTE = "userGet";
const API_USER_UPDATE_ROUTE = "userUpdate";
const API_USER_DELETE_ROUTE = "userDelete";

$app->get('/login/:email/:password/:socialLogin',function($email,$password,$socialLogin) use ($app){
    $result = Rath\Controllers\UserController::AuthenticateUser($email,$password,$socialLogin);
    Rath\helpers\CrossDomainAjax::PrintCrossDomainCall($app,$result);
})->name(API_LOGIN_ROUTE);

$app->group('/user', function() use ($app){
    $app->get('/:hash', function($hash) use ($app){
        $result = Rath\Controllers\UserController::GetuserByHash($hash);
        Rath\helpers\CrossDomainAjax::PrintCrossDomainCall($app,$result);
    })->name(API_USER_GET_ROUTE);

    $app->post('',function() use ($app){
        $user = json_decode($app->request->getBody());
        $result = Rath\Controllers\UserController::CreateUser($user);
        Rath\helpers\CrossDomainAjax::PrintCrossDomainCall($app,$result);
    })->name(API_USER_CREATE_ROUTE);

    $app->put('', function() use ($app){
        $user = json_decode($app->request->getBody());
        $result = Rath\Controllers\UserController::UpdateUser($user);
        Rath\helpers\CrossDomainAjax::PrintCrossDomainCall($app,$result);
    })->name(API_USER_UPDATE_ROUTE);

    $app->get('/delete/:hash', function($hash) use ($app){
        $result = Rath\Controllers\UserController::DeleteUser($hash);
        Rath\helpers\CrossDomainAjax::PrintCrossDomainCall($app,$result);
    })->name(API_USER_DELETE_ROUTE);

    $app->group('/address', function() use ($app){
        $gen = new \Rath\Controllers\GeneralController();
        $app->get('/:id', function($id) use ($app,$gen){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->getAddress($id)
            );
        });

        $app->post('' ,function() use ($app,$gen){
            $pr = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->addAddress($pr)
            );
        });

        $app->put('', function() use ($app,$gen){
            $pr = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->updateAddress($pr)
            );
        });

        $app->get('/delete/:id', function($id) use ($app,$gen){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->deleteAddress($id)
            );
        });
    });
});


//</editor-fold>



$app->group('/restaurant', function() use ($app){
    $resto = new \Rath\Controllers\RestaurantController();

    $app->get('/:id', function($id) use ($app,$resto){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $resto->getRestaurant($id)
        );
    });

    $app->post('' ,function() use ($app,$resto){
        $pr = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $resto->addRestaurant($pr)
        );
    });

    $app->put('', function() use ($app,$resto){
        $pr = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $resto->updateRestaurant($pr)
        );
    });

    $app->get('/delete/:id', function($id) use ($app,$resto){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $resto->deleteRestaurant($id)
        );
    });

    $app->group('/holiday', function() use ($app,$resto){
        $app->get('/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getHoliday($id)
            );
        });

        $app->post('' ,function() use ($app,$resto){
            $ho = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->addHoliday($ho)
            );
        });

        $app->put('', function() use ($app,$resto){
            $ho = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->updateHoliday($ho)
            );
        });

        $app->get('/delete/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->deleteHoliday($id)
            );
        });
    });

    $app->group('/openinghour', function() use ($app,$resto){
        $app->get('/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getOpeningHour($id)
            );
        });

        $app->post('' ,function() use ($app,$resto){
            $ho = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->addOpeningHour($ho)
            );
        });

        $app->put('', function() use ($app,$resto){
            $ho = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->updateOpeningHour($ho)
            );
        });

        $app->get('/delete/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->deleteOpeningHour($id)
            );
        });
    });
});

$app->group('/product', function () use ($app) {
    $prod = new \Rath\Controllers\ProductController();

    $app->get('/:id', function($id) use ($app,$prod){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $prod->getProduct($id)
        );
    });

    $app->post('' ,function() use ($app,$prod){
        $pr = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $prod->addProduct($pr)
        );
    });

    $app->put('', function() use ($app,$prod){
        $pr = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $prod->updateProduct($pr)
        );
    });

    $app->get('/delete/:id', function($id) use ($app,$prod){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $prod->deleteProduct($id)
        );
    });
});





//function exception_handler($exception) {
//   if($exception);
//}
//
//set_exception_handler('exception_handler');

$app->run();

<?php
/**
 * @SWG\Info(title="RestaurantAtHome API", version="0.1")
 */

//region Globals
if (!defined('APP_PATH'))
    define('APP_PATH', realpath(__DIR__ ));

if(!defined('APP_MODE'))
    define('APP_MODE', 'LOCAL');
//endregion

require_once __DIR__.'/vendor/autoload.php';

use Rath\Controllers\ControllerFactory;
use Rath\Controllers\Data\DataControllerFactory;
use Rath\helpers\CrossDomainAjax;


//region Init
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->setName("RestaurantAtHomeApi");
$app->add(new \Rath\Slim\Middleware\Authorization()); //TODO; Authentication check

bcscale(2); //Calculation decimals
//endregion

// Inject as Slim application middleware
//$app->add(new \Slagger\Slagger('/v1/docs', 'Rath'));



//region App Mgt (old)
const API_PING_ROUTE = "ping";

$app->get('/ping', function() use ($app){
    $status = Rath\Controllers\ApplicationManagementController::GetStatus();
    CrossDomainAjax::PrintCrossDomainCall($app,$status);
})->name(API_PING_ROUTE);


const API_MASTERDATA_ROUTE = "masterdata";
const API_UNAUTHORISED_ROUTE = "unauthorised";


$app->group('/masterdata', function() use ($app){
    $app->POST('', function() use ($app){
        Rath\helpers\MasterData::CreateDemoData();
        Rath\helpers\CrossDomainAjax::PrintCrossDomainCall($app,['Datageneration success']);
    })->name(API_MASTERDATA_ROUTE);

    $app->get('/echodataobjects', function() use ($app){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            \Rath\helpers\MasterData::echoObjectContent()
        );

    });
});
//endregion

/**
 * @SWG\Post(
 *     path="/api/resource.json",
 *     @SWG\Response(response="200", description="An example resource")
 * )
 */


//region User
const API_LOGIN_ROUTE = "login";

const API_USER_CREATE_ROUTE = "userCreate";
const API_USER_GET_ROUTE = "userGet";
const API_USER_UPDATE_ROUTE = "userUpdate";
const API_USER_DELETE_ROUTE = "userDelete";

$app->get('/login/:email/:password/:socialLogin',function($email,$password,$socialLogin) use ($app){
    $user = DataControllerFactory::getUserController();
    CrossDomainAjax::PrintCrossDomainCall(
        $app,
        $user->authenticateUser($email,$password,$socialLogin));
})->name(API_LOGIN_ROUTE);

$app->group('/user', function() use ($app){
    $user = DataControllerFactory::getUserController();
    $app->get('/:hash', function($hash) use ($app,$user){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $user->getUserByHash($hash));
    })->name(API_USER_GET_ROUTE);

    $app->post('',function() use ($app,$user){
        $userData = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $user->createUser($userData));
    })->name(API_USER_CREATE_ROUTE);

    $app->put('', function() use ($app,$user){
        $userData = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $user->updateUser($userData));
    })->name(API_USER_UPDATE_ROUTE);

    $app->get('/delete/:hash', function($hash) use ($app,$user){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $user->deleteUser($hash));
    })->name(API_USER_DELETE_ROUTE);

    $app->group('/address', function() use ($app){
        $gen = new \Rath\Controllers\Data\GeneralController();
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
//endregion

//region management
$app->group('/manage', function() use ($app){
    $resto = DataControllerFactory::getRestaurantController();
    $prod = DataControllerFactory::getProductController();
    $promo = DataControllerFactory::getPromotionController();
    $gen = DataControllerFactory::getGeneralController();
    $default = DataControllerFactory::getDefaultDataController();

    $app->group('/kitchentype' , function() use ($app,$resto){
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

    $app->group('/paymentmethod' , function() use ($app,$resto){

        $app->get('/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getPaymentMethod($id)
            );
        });

        $app->post('', function() use ($app,$resto){
            $pm = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->addPaymentMethod($pm)
            );
        });

        $app->put('',function() use ($app,$resto){
            $pm = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->updatePaymentMethod($pm)
            );
        });

        $app->get('/delete/:id',function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->deletePaymentMethod($id)
            );
        });
    });

    $app->group('/tag' , function() use ($app, $prod){

        $app->get('/:id', function($id) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->getTag($id)
            );
        });

        $app->post('', function() use ($app,$prod){
            $tag = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->addTag($tag)
            );
        });

        $app->put('', function() use ($app,$prod){
            $tag = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->updateTag($tag)
            );
        });

        $app->get('/delete/:id',function($id) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->deleteTag($id)
            );
        });
    });

    $app->group('/producttype' , function() use ($app, $prod){

        $app->get('/:id', function($id) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->getProductType($id)
            );
        });

        $app->get('/all/', function() use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->getProductTypes()
            );
        });

        $app->post('', function() use ($app,$prod){
            $prodType = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->addProductType($prodType)
            );
        });

        $app->put('', function() use ($app,$prod){
            $prodType = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->updateProductType($prodType)
            );
        });

        $app->get('/delete/:id',function($id) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->deletePoductType($id)
            );
        });
    });

    $app->group('/promotiontype' , function() use ($app, $promo){

        $app->get('/:id', function($id) use ($app,$promo){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $promo->getPromotionType($id)
            );
        });

        $app->post('/', function() use ($app,$promo){
            $ps = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $promo->addPromotionType($ps)
            );
        });

        $app->put('/', function() use ($app,$promo){
            $ps = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $promo->updatePromotionType($ps)
            );
        });

        $app->get('/delete/:id',function($id) use ($app,$promo){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $promo->deletePromotionType($id)
            );
        });
    });

    $app->group('/partner' , function() use ($app, $gen){

        $app->get('/:id', function($id) use ($app,$gen){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->getPartner($id)
            );
        });

        $app->get('/all/', function() use ($app,$gen){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->getAllPartners()
            );
        });

        $app->post('', function() use ($app,$gen){
            $prodType = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->addPartner($prodType)
            );
        });

        $app->put('', function() use ($app,$gen){
            $prodType = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->updatePartner($prodType)
            );
        });

        $app->get('/delete/:id',function($id) use ($app,$gen){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->deletePartner($id)
            );
        });
    });

    $app->group('/socialmediatype' , function() use ($app, $gen){

        $app->get('/:id', function($id) use ($app,$gen){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->getSocialMediaType($id)
            );
        });

        $app->get('/all/', function() use ($app,$gen){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->getAllSocialMediaTypes()
            );
        });

        $app->post('', function() use ($app,$gen){
            $socType = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->addSocialMediaType($socType)
            );
        });

        $app->put('', function() use ($app,$gen){
            $socType = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->updateSocialMediaType($socType)
            );
        });

        $app->get('/delete/:id',function($id) use ($app,$gen){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $gen->deleteSocialMediaType($id)
            );
        });
    });

    $app->group('/defaults' , function() use ($app, $default) {

        $app->post('/orderstatus', function () use ($app, $default) {
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $default->insertOrderStatus()
            );
        });

        $app->post('/socialmediatypes', function () use ($app, $default) {
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $default->insertSocialMediaTypes()
            );
        });
    });
});
//endregion

//region Restaurant
$app->group('/restaurant', function() use ($app){
    $resto = \Rath\Controllers\Data\DataControllerFactory::getRestaurantController();

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

    $app->group('/paymentmethod', function() use ($app,$resto){
        $app->get('/:restoId', function($restoId) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getRestaurantPaymentMethods($restoId)
            );
        });

        $app->post('/:restoId/:payId' ,function($restoId,$payId) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->addRestaurantPaymentMethod($restoId,$payId)
            );
        });

        $app->get('/delete/:restoId/:payId', function($restoId,$payId) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->deleteRestaurantPaymentMethod($restoId,$payId)
            );
        });
    });

    $app->group('/speciality', function() use ($app,$resto){
        $app->get('/:restoId', function($restoId) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getRestaurantSpecialities($restoId)
            );
        });

        $app->get('/all/', function() use ($app,$resto){
//            die('test');
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getAllSpecialities()
            );
        });

        $app->post('/:restoId/:specId' ,function($restoId,$specId) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->addRestaurantSpeciality($restoId,$specId)
            );
        });

        $app->post('/new/:restoId/:name' ,function($restoId,$name) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->addNewRestaurantSpeciality($restoId,$name)
            );
        });

        $app->get('/delete/:restoId/:specId', function($restoId,$specId) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->deleteRestaurantSpeciality($restoId,$specId)
            );
        });
    });

    //TODO: test photo upload
    $app->group('/photo', function() use ($app,$resto){
        $app->get('/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getPhoto($id)
            );
        });

        $app->post('' ,function() use ($app,$resto){
            $ho = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->addPhoto($ho)
            );
        });

        $app->put('', function() use ($app,$resto){
            $ho = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->updatePhoto($ho)
            );
        });

        $app->get('/delete/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->deletePhoto($id)
            );
        });
    });

    $app->group('/socialmedia', function() use ($app,$resto){
        $app->get('/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getSocialMedia($id)
            );
        });

        $app->get('/all/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->getAllSocialMedia($id)
            );
        });

        $app->post('' ,function() use ($app,$resto){
            $ho = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->addSocialMedia($ho)
            );
        });

        $app->put('', function() use ($app,$resto){
            $ho = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->updateSocialMedia($ho)
            );
        });

        $app->get('/delete/:id', function($id) use ($app,$resto){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $resto->deleteSocialMedia($id)
            );
        });
    });
});
//endregion

//region Product
$app->group('/product', function () use ($app) {
    $prod = \Rath\Controllers\Data\DataControllerFactory::getProductController();

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

    $app->group('/tag' , function() use ($app, $prod){

        $app->get('/:productId', function($productId) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->getProductTags($productId)
            );
        });

        $app->post('/:prodId/:tagId', function($prodId,$tagId) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->addProductTag($prodId,$tagId)
            );
        });

        $app->get('/delete/:prodId/:tagId',function($prodId,$tagId) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->deleteProductTag($prodId,$tagId)
            );
        });
    });

    $app->group('/stock' , function() use ($app, $prod){

        $app->get('/:productId', function($productId) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->getProductStock($productId)
            );
        });

        $app->get('/single/:productStockId', function($productStockId) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->getSingleProductStock($productStockId)
            );
        });

        $app->post('/', function() use ($app,$prod){
            $ps = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->addProductStock($ps)
            );
        });

        $app->put('/', function() use ($app,$prod){
            $ps = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->updateProductStock($ps)
            );
        });

        $app->get('/delete/:id',function($id) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->deletePoductStock($id)
            );
        });
    });

    $app->group('/related' , function() use ($app, $prod){

        $app->get('/:productId', function($productId) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->getRelatedProducts($productId)
            );
        });

        $app->post('/:prodId/:relProdId', function($prodId,$relProdId) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->addRelatedProduct($prodId,$relProdId)
            );
        });

        $app->get('/delete/:prodId/:relProdId',function($prodId,$relProdId) use ($app,$prod){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $prod->deleteRelatedProduct($prodId,$relProdId)
            );
        });
    });
});
//endregion

//region Promotion
$app->group('/promotion', function () use ($app) {
    $promo = \Rath\Controllers\Data\DataControllerFactory::getPromotionController();

    $app->get('/:id', function($id) use ($app,$promo){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $promo->getPromotion($id)
        );
    });

    $app->post('/', function() use ($app,$promo){
        $ps = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $promo->addPromotion($ps)
        );
    });

    $app->put('/', function() use ($app,$promo){
        $ps = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $promo->updatePromotion($ps)
        );
    });

    $app->get('/delete/:id',function($id) use ($app,$promo){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $promo->deletePromotion($id)
        );
    });

    $app->group('/history' , function() use ($app, $promo){

        $app->get('/:id', function($id) use ($app,$promo){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $promo->getPromotionUsageCount($id)
            );
        });

        $app->post('/', function() use ($app,$promo){
            $ps = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $promo->addPromotionUsageHistory($ps)
            );
        });

        $app->get('/delete/:id',function($id) use ($app,$promo){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $promo->deletePromotionUsageHistory($id)
            );
        });
    });
});
//endregion

//region Order
$app->group('/order', function() use ($app){
    $oc = DataControllerFactory::getOrderController();

    $app->get('/:id', function($id) use ($app,$oc){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $oc->getOrderWithLines($id)
        );
    });

    $app->post('/', function() use ($app,$oc){
        $o = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $oc->createOrder($o)
        );
    });

    $app->put('/', function() use ($app,$oc){
        $o = json_decode($app->request->getBody());
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $oc->updateOrder($o)
        );
    });

    $app->get('/delete/:id', function($id) use ($app,$oc){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $oc->deleteOrder($id)
        );
    });

    $app->group('/line', function() use ($app,$oc){
        $app->get('/:id', function($id) use ($app,$oc){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $oc->getOrderDetailLine($id)
            );
        });

        $app->post('/', function() use ($app,$oc){
            $o = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $oc->addOrderDetailLine($o)
            );
        });

        $app->put('/', function() use ($app,$oc){
            $o = json_decode($app->request->getBody());
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $oc->updateOrderDetailLine($o)
            );
        });

        $app->get('/delete/:id', function($id) use ($app,$oc){
            CrossDomainAjax::PrintCrossDomainCall(
                $app,
                $oc->deleteOrderDetailLine($id)
            );
        });
    });
});
//endregion


//region Front-end data provider
//region Dashboard
$app->group('/dashboard', function() use ($app){
    $dash = ControllerFactory::getDashboardController();

    $app->get('/neworders/:restoId', function($restoId) use ($app,$dash){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $dash->getNewOrderCount($restoId)
        );
    });

    $app->get('/overview/:restoId', function($restoId) use ($app,$dash){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $dash->getOverviewContent($restoId)
        );
    });

    $app->get('/profile/:restoId',function($restoId) use ($app,$dash){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $dash->getProfileContent($restoId)
        );
    });

    $app->get('/products/:restoId/:skip/:top',function($restoId,$skip,$top) use ($app,$dash){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $dash->getProductContent($restoId,$skip,$top)
        );
    });

    $app->get('/orders/:restoId',function($restoId) use ($app,$dash){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $dash->getOrderContent($restoId)
        );
    });

    $app->get('/promotions/:restoId/:skip/:top',function($restoId,$skip,$top) use ($app,$dash){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $dash->getPromotionContent($restoId,$skip,$top)
        );
    });

    $app->get('/partners/:skip/:top',function($skip,$top) use ($app,$dash){
        CrossDomainAjax::PrintCrossDomainCall(
            $app,
            $dash->getPartners($skip,$top)
        );
    });
});
//endregion
//endregion







//function exception_handler($exception) {
//   if($exception);
//}
//
//set_exception_handler('exception_handler');

$app->run();

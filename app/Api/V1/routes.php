<?php

use Illuminate\Support\Facades\Route;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {

    return $router->app->version();
});

$router->group(['prefix' => 'api/v1'], function () use ($router) {
    $router->get('/subscribe/user/bot',[ 'as' => 'user_bot', 'uses' => '\App\Api\V1\Controllers\TwitterIntegrationController@subscribe_bot']);
    $router->get('/subscribe/user/channel','\App\Api\V1\Controllers\TwitterIntegrationController@subscribe_channel');
    $router->post('/subscribe/user/message','\App\Api\V1\Controllers\TwitterIntegrationController@subscribe_message');
    $router->post('/webhook','\App\Api\V1\Controllers\TwitterIntegrationController@webhook');
});



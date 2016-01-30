<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->get('/', function() {
    return view('home');
});

$app->get('/oauth2callback', 'ApiController@getRefreshToken');

$app->post('/queue/receive', function() {
	return Queue::marshal();
});

$api = $app['Dingo\Api\Routing\Router'];

$api->version('v1', ['protected' => true], function ($api) {

    $api->post('auth', ['protected' => false, 'uses' => 'App\Http\Controllers\ApiController@postAuth']);

    $api->get('credit', 'App\Http\Controllers\ApiController@getCredit');

    $api->post('check', 'App\Http\Controllers\ApiController@postCheck');

    $api->post('check-purchase', 'App\Http\Controllers\ApiController@validatePurchase');

    $api->post('register-push', 'App\Http\Controllers\ApiController@postRegisterPush');

    $api->post('send-push', 'App\Http\Controllers\ApiController@sendPushMessage');
});

<?php

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
    return 'rest-api, v1.0-R';
});


Route::post('login', 'AuthController@login');

Route::group([

    'middleware' => 'auth:api',
    'prefix' => 'data-api'

], function ($router) {
    Route::get('me', 'AuthController@me');



    Route::get('type/{type}/limit/{limit}/order/{order}', 'DataController@getProduct');
    Route::get('type/{type}/id/{id}', 'DataController@getSingleProduct');

    Route::get('categories', 'DataController@MyAllCategories');
    Route::get('categories/{single}/limit/{limit}', 'DataController@categoriesSingle');

    Route::get('search/{search}/limit/{limit}', 'DataController@search');

    
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');


    Route::get('/themes-color', 'DataController@color');
    Route::get('/generated-pages', 'DataController@pages');
    Route::get('/meta-data', 'DataController@meta');




});
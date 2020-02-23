<?php
use Illuminate\Http\Request;

$router->group(['prefix'=>'custom'], function () use ($router) {
    // $router->group(['middleware'=>'auth'], function () use ($router) {
        $router->get('/{modelname}/{function}', 'ApiCustomController@router');
        $router->post('/{modelname}/{function}', 'ApiCustomController@router');
        $router->put('/{modelname}/{function}', 'ApiCustomController@router');
        $router->patch('/{modelname}/{function}', 'ApiCustomController@router');
    // });
});
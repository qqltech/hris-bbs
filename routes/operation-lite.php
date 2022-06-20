<?php
use Illuminate\Http\Request;

$router->group(['prefix'=>'lite'], function () use ($router) {
    $router->group(['middleware'=>'auth'], function () use ($router) {
        $router->get('/{name}',['as'=>'read_list_native', 'uses'=> 'ApiNativeController@index']);
        $router->get('/{name}/{id}',['as'=>'read_row_native', 'uses'=> 'ApiNativeController@index']);
    });
});
<?php
use Illuminate\Http\Request;

$router->group(['prefix'=>'lite'], function () use ($router) {
    $router->group(['middleware'=>['project', 'auth']], function () use ($router) {
        $router->get('/{name}',['as'=>'read_list_native', 'uses'=> 'ApiNativeController@index']);
        $router->get('/{name}/{id}',['as'=>'read_row_native', 'uses'=> 'ApiNativeController@index']);
        
        $router->post('/{name}',['as'=>'create_native', 'uses'=> 'ApiNativeController@store']);
        $router->put('/{name}/{id}',['as'=>'update_native', 'uses'=> 'ApiNativeController@update']);
        $router->delete('/{name}/{id}',['as'=>'delete_native', 'uses'=> 'ApiNativeController@destroy']);

    });
});
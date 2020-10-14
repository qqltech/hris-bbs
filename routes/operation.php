<?php
use Illuminate\Http\Request;

$router->group(['prefix'=>'operation'], function () use ($router) {

    $router->group(['middleware'=>'auth'], function () use ($router) {

        $router->get('/{modelname}',['as'=>'read_list', 'uses'=> 'ApiFixedController@router']);         //LIST PARENTS
        $router->post('/{modelname}', 'ApiFixedController@router');        //CREATE PARENT-ALL-DETAILS
        $router->post('/{modelname}/{id}', 'ApiFixedController@router');        //CREATE PARENT-ALL-DETAILS

        $router->get('/{modelname}/{id}',['as'=>'read_id', 'uses'=>'ApiFixedController@router']);    //GET SINGLE PARENT-ALL-DETAILS
        $router->put('/{modelname}/{id}', 'ApiFixedController@router');    //UPDATE SINGLE PARENT-ALL-DETAILS
        $router->patch('/{modelname}/{id}', 'ApiFixedController@router');  //UPDATE SINGLE PARENT-ALL-DETAILS
        $router->delete('/{modelname}/{id}', 'ApiFixedController@router'); //DELETE SINGLE PARENT-ALL-DETAILS

        $router->get('/{modelname}/{id}/{subdetail}', 'ApiController@level2');    //LIST PARENT SUBDETAIL TERTENTU
        $router->post('/{modelname}/{id}/{subdetail}', 'ApiController@level2');   //CREATE SUBDETAIL TERTENTU DARI PARENT ID
        $router->put('/{modelname}/{id}/{subdetail}', 'ApiController@level2');    //UPDATE SUBDETAIL TERTENTU DARI PARENT ID
        $router->patch('/{modelname}/{id}/{subdetail}', 'ApiController@level2');  //UPDATE SUBDETAIL TERTENTU DARI PARENT ID
        $router->delete('/{modelname}/{id}/{subdetail}', 'ApiController@level2'); //DELETE SUBDETAIL TERTENTU DARI PARENT ID

        $router->get('/{modelname}/{id}/{subdetail}/{idsubdetail}', 'ApiController@level3');
        $router->put('/{modelname}/{id}/{subdetail}/{idsubdetail}', 'ApiController@level3');
        $router->patch('/{modelname}/{id}/{subdetail}/{idsubdetail}', 'ApiController@level3');
        $router->delete('/{modelname}/{id}/{subdetail}/{idsubdetail}', 'ApiController@level3');

    });

    $router->get('/', function(){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view("defaults.operation");
    });

});
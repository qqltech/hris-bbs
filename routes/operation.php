<?php
use Illuminate\Http\Request;

$router->group(['prefix'=>env('ROUTE_API_PREFIX','operation')], function () use ($router) {

    $router->group(['middleware'=>['project','auth']], function () use ($router) {

        $router->get('/{modelname}',['as'=>'read_list', 'uses'=> 'ApiFixedController@router']);         //LIST PARENTS
        $router->post('/{modelname}', 'ApiFixedController@router');        //CREATE PARENT-ALL-DETAILS
        $router->post('/{modelname}/{id}', 'ApiFixedController@router');        //CREATE PARENT-ALL-DETAILS

        $router->get('/{modelname}/{id}',['as'=>'read_id', 'uses'=>'ApiFixedController@router']);    //GET SINGLE PARENT-ALL-DETAILS
        $router->put('/{modelname}/{id}', 'ApiFixedController@router');    //UPDATE SINGLE PARENT-ALL-DETAILS
        $router->patch('/{modelname}/{id}', 'ApiFixedController@router');  //UPDATE SINGLE PARENT-ALL-DETAILS
        $router->delete('/{modelname}/{id}', 'ApiFixedController@router'); //DELETE SINGLE PARENT-ALL-DETAILS

        $router->get('/{modelname}/{id}/{detailmodelname}',['as'=>'read_list_detail', 'uses'=> 'ApiFixedController@router']);    //LIST PARENT DETAIL TERTENTU
        $router->get('/{modelname}/{id}/{detailmodelname}/{detailid}',['as'=>'read_id_detail', 'uses'=>'ApiFixedController@router']);   //CREATE DETAIL TERTENTU DARI PARENT ID
        $router->get('/{modelname}/{id}/{detailmodelname}/{detailid}/{subdetailmodelname}',['as'=>'read_list_sub_detail', 'uses'=>'ApiFixedController@router']);   //CREATE DETAIL TERTENTU DARI PARENT ID
        $router->get('/{modelname}/{id}/{detailmodelname}/{detailid}/{subdetailmodelname}/{subdetailid}',['as'=>'read_id_sub_detail', 'uses'=>'ApiFixedController@router']);   //CREATE DETAIL TERTENTU DARI PARENT ID
        //$router->put('/{modelname}/{id}/{detailmodelname}', 'ApiController@router');    //UPDATE DETAIL TERTENTU DARI PARENT ID
        //$router->patch('/{modelname}/{id}/{detailmodelname}', 'ApiController@router');  //UPDATE DETAIL TERTENTU DARI PARENT ID
        //$router->delete('/{modelname}/{id}/{detailmodelname}', 'ApiController@router'); //DELETE DETAIL TERTENTU DARI PARENT ID

        //$router->get('/{modelname}/{id}/{detailmodelname}/{iddetailmodelname}', 'ApiController@level3');
        //$router->put('/{modelname}/{id}/{detailmodelname}/{iddetailmodelname}', 'ApiController@level3');
        //$router->patch('/{modelname}/{id}/{detailmodelname}/{iddetailmodelname}', 'ApiController@level3');
        //$router->delete('/{modelname}/{id}/{detailmodelname}/{iddetailmodelname}', 'ApiController@level3');

    });

    $router->get('/', function(){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        return view("defaults.operation");
    });

});
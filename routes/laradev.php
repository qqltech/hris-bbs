<?php
use Illuminate\Http\Request;
$router->group(['prefix'=>'laradev'], function () use ($router) {
    $router->group(['middleware' => 'laradev'], function () use ($router) {
        $router->get('/environment', 'LaradevController@readEnv');
        $router->put('/environment', 'LaradevController@setEnv');

        $router->get('/databases', 'LaradevController@databaseCheck');
        $router->post('/databases', 'LaradevController@createDatabase');
        $router->delete('/databases/{databaseName}', 'LaradevController@deleteDatabase');

        $router->get('/tables', 'LaradevController@readTables');
        $router->get('/tables/{table}', 'LaradevController@readTables');
        $router->put('/tables/{tableName}', 'LaradevController@renameTables');
        $router->put('/tables/{tableName}/trigger', 'LaradevController@makeTrigger');
        $router->delete('/tables/{tableName}/trigger', 'LaradevController@makeTrigger');
        $router->post('/tables', 'LaradevController@createTables');
        $router->delete('/tables/{tableName}', 'LaradevController@deleteTables');
        $router->post('/migrate', 'LaradevController@migrateDefault');

        $router->get('/models', 'LaradevController@readModels');
        $router->get('/models/{tableName}', 'LaradevController@readModelsOne');
        $router->post('/models', 'LaradevController@createModels');
        $router->post('/models/{tableName}', 'LaradevController@createModels');
        $router->put('/models/{tableName}', 'LaradevController@updateModelsOne');
        $router->post('/mail', 'LaradevController@mail');

        
        $router->get('/migrations', 'LaradevController@readMigrations');
        $router->get('/migrations/{table}', 'LaradevController@readMigrations');
        $router->post('/migrations', 'LaradevController@editMigrations');
        $router->put('/migrations/{table}', 'LaradevController@editMigrations');

        
        $router->get('/realfk', 'LaradevController@getPhysicalForeignKeys');
        $router->get('/dorealfk', 'LaradevController@setPhysicalForeignKeys');

        $router->get('/migrate/{table}', 'LaradevController@doMigrate');
    });


    $router->get('/', function(Request $req){
        if(!isset($req->kode) || $req->kode!=env("BACKENDPASSWORD","pulangcepat")){
            return response()->json("Unauthorized",401);
        }
        return view("defaults.laradev");
    });
    $router->delete('/trio/{table}', 'LaradevController@deleteAll');
});
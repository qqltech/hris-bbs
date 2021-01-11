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

        $router->get('/models', 'LaradevController@readMigrations');
        $router->get('/models/{tableName}', 'LaradevController@readModelsOne');
        $router->post('/models', 'LaradevController@createModels');
        $router->post('/models/{tableName}', 'LaradevController@createModels');
        $router->put('/models/{tableName}', 'LaradevController@updateModelsOne');
        $router->post('/mail', 'LaradevController@mail');

        
        $router->get('/migrations', 'LaradevController@readMigrations');
        $router->get('/alter/{table}', 'LaradevController@readAlter');
        $router->put('/alter/{table}', 'LaradevController@editAlter');
        $router->get('/migrations/{table}', 'LaradevController@readMigrations');
        $router->post('/migrations', 'LaradevController@editMigrations');
        $router->put('/migrations/{table}', 'LaradevController@editMigrations');

        
        $router->get('/realfk', 'LaradevController@getPhysicalForeignKeys');
        $router->get('/dorealfk', 'LaradevController@setPhysicalForeignKeys');

        $router->get('/migrate/{table}', 'LaradevController@doMigrate');
        $router->get('/refreshalias/{table}', 'LaradevController@refreshAlias');

        $router->post("/uploadlengkapi","LaradevController@uploadLengkapi");
        $router->post("/uploadtest","LaradevController@uploadTest");
        $router->post("/uploadwithcreate","LaradevController@uploadWithCreate");
        $router->post("/uploadtemplate","LaradevController@uploadTemplate");
    });

    $router->post("/getnotice","LaradevController@getNotice");
    $router->get('/', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman config',
            'url'=>url("/laradev")
        ]);
    });

    $router->post('/', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        if(!isset($req->password) || $req->password!=env("BACKENDPASSWORD","pulangcepat")){
            return view('defaults.unauthorized')->with('data',[
                'page'=>'halaman config',
                'url'=>url("/laradev"),
                'salah'=>true
            ]);
        }else{            
            return view("defaults.laradev");
        }
    });

    $router->post('/trio/{table}', 'LaradevController@deleteAll');
});
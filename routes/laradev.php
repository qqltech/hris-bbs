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

        $router->get('/models', 'LaradevController@readMigrationsOrCache');
        $router->get('/models/{tableName}', 'LaradevController@readModelsOne');
        $router->post('/models', 'LaradevController@createModels');
        $router->post('/models/{tableName}', 'LaradevController@createModels');
        $router->put('/models/{tableName}', 'LaradevController@updateModelsOne');
        $router->post('/mail', 'LaradevController@mail');

        
        $router->get('/migrations', 'LaradevController@readMigrations');
        $router->get('/logs/{table}', 'LaradevController@readLog');
        $router->get('/tests/{table}', 'LaradevController@readTest');
        $router->put('/tests/{table}', 'LaradevController@editTest');
        $router->get('/alter/{table}', 'LaradevController@readAlter');
        $router->put('/alter/{table}', 'LaradevController@editAlter');
        $router->get('/migrations/{table}', 'LaradevController@readMigrations');
        $router->post('/migrations', 'LaradevController@editMigrations');
        $router->put('/migrations/{table}', 'LaradevController@editMigrations');

        
        $router->get('/realfk', 'LaradevController@getPhysicalForeignKeys');
        $router->get('/dorealfk', 'LaradevController@setPhysicalForeignKeys');

        $router->get('/migrate/{table}', 'LaradevController@doMigrate');
        $router->get('/do-test/{table}', 'LaradevController@doTest');
        $router->get('/queries10rows/{table}', 'LaradevController@queries10rows');
        $router->get('/truncate/{table}', 'LaradevController@truncate');
        $router->get('/refreshalias/{table}', 'LaradevController@refreshAlias');

        $router->post("/uploadlengkapi","LaradevController@uploadLengkapi");
        $router->post("/uploadtest","LaradevController@uploadTest");
        $router->post("/uploadwithcreate","LaradevController@uploadWithCreate");
        $router->post("/uploadtemplate","LaradevController@uploadTemplate");
        $router->post("/paramaker","LaradevController@paramaker");
        $router->post("/run-query","LaradevController@runQuery");
        $router->get("/run-backup","LaradevController@runBackup");

        $router->get("/javascript", "LaradevController@getJsFile");
        $router->get("/javascript/{filename}","LaradevController@getJsFile");
        $router->put("/javascript/{filename}","LaradevController@saveJsFile");
        $router->delete("/javascript/{filename}","LaradevController@deleteJsFile");

        $router->get("/blades", "LaradevController@getBladeFile");
        $router->get("/blades/{filename}","LaradevController@getBladeFile");
        $router->put("/blades/{filename}","LaradevController@saveBladeFile");
        $router->delete("/blades/{filename}","LaradevController@deleteBladeFile");
        $router->get("/cores", "LaradevController@getCoreFile");
        $router->get("/cores/{filename}","LaradevController@getCoreFile");
        $router->put("/cores/{filename}","LaradevController@saveCoreFile");
        $router->delete("/cores/{filename}","LaradevController@deleteCoreFile");
    });

    $router->post("/getnotice","LaradevController@getNotice");
    $router->get('/', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
        if( !$req->has('password') || $req->password!=env("CONFIGPASSWORD","pulangcepat")){
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
    $router->delete('/trio/{table}', 'LaradevController@deleteAll');

    $router->post('/connect', function(Request $req){
        if(!$req->has('password')){
            return response()->json([
                'message'=>'password is required'
            ], 401);
        }

        $backendPassword = env("LARADEVPASSWORD");
        if( $backendPassword !== $req->password ){
            return response()->json([
                'message'=>'unauthenticated'
            ], 401);
        }

        return response()->json([
            "socket_server"=> env('LOG_SERVER'),
            "socket_protocol"=>env('LOG_PROTOCOLS'),
            "socket_room"=>env('LOG_CHANNEL')
        ]);
    });
    
    $router->get("/get-backup", "LaradevController@getBackup");
});
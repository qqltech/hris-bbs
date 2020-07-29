<?php
use Illuminate\Http\Request;
$router->group(['prefix'=>'docs'], function () use ($router) {
    $router->get('/frontend', function(){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        try{
            $models = json_decode(file_get_contents("models.json"));
        }catch(Exception $e){
            return $e->getMessage();
        }
        return view("defaults.api",compact('models'));
    });
    $router->get('/backend', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        if(!isset($req->kode) || $req->kode!=env("BACKENDPASSWORD","pulangcepat")){
            return response()->json("Unauthorized",401);
        }
        try{
            $modelData = (new \App\Http\Controllers\LaradevController)->readMigrations(new Request(),null);
            $models = $modelData['models'];
            $realfk = $modelData['realfk'];
        }catch(Exception $e){
            return $e->getMessage();
        }
        return view("defaults.backend",compact('models','realfk'));
    });

    $router->get('/documentation', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view("docs.docs");
    });
    
    $router->get('/documentation/{dt}', function($dt){
        return view("docs.docs-".(str_replace([".md","_"],["",""],strtolower($dt))) );
    });
});
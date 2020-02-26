<?php
use Illuminate\Http\Request;
$router->group(['prefix'=>'docs'], function () use ($router) {
    $router->get('/frontend', function(){
        try{
            $models = json_decode(file_get_contents("models.json"));
        }catch(Exception $e){
            return $e->getMessage();
        }
        return view("defaults.api",compact('models'));
    });
    $router->get('/backend', function(Request $req){
        if(!isset($req->kode) || $req->kode!=env("BACKENDPASSWORD","pulangcepat")){
            return response()->json("Unauthorized",401);
        }
        try{
            $models = (new \App\Http\Controllers\LaradevController)->readMigrations(new Request(),null);
        }catch(Exception $e){
            return $e->getMessage();
        }
        return view("defaults.backend",compact('models'));
    });

});
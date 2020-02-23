<?php
use Illuminate\Http\Request;
$router->group(['prefix'=>'docs'], function () use ($router) {
    $router->get('/frontend', function(){
        $models = json_decode(file_get_contents("models.json"));
        return view("defaults.api",compact('models'));
    });
    $router->get('/backend', function(Request $req){
        if(!isset($req->kode) && $req->kode!="pulangcepat"){
            return response()->json("Unauthorized",401);
        }
        $models = (new \App\Http\Controllers\LaradevController)->readMigrations(new Request(),null);
        // return $models;
        return view("defaults.backend",compact('models'));
    });

});
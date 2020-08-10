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
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman backend',
            'url'=>url("docs/backend")
        ]);
    });
    $router->post('/backend', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        if(!isset($req->password) || $req->password!=env("BACKENDPASSWORD","pulangcepat")){
            return view('defaults.unauthorized')->with('data',[
                'page'=>'halaman backend',
                'url'=>url("docs/backend"),
                'salah'=>true
            ]);
        }else{            
            try{
                $modelData = (new \App\Http\Controllers\LaradevController)->readMigrations(new Request(),null);
                $models = $modelData['models'];
                $realfk = $modelData['realfk'];
                $data = [
                    'page'=>'halaman backend',
                    'url'=>url("docs/backend"),
                    'password'=>$req->password,
                    'salah'=>true
                ];
            }catch(Exception $e){
                return $e->getMessage();
            }
            return view("defaults.backend",compact('models','realfk','data'));
        }
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
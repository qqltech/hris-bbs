<?php
use Illuminate\Http\Request;
$router->group(['prefix'=>'docs'], function () use ($router) {
    $router->get('/frontend-params', function(){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        $list = DB::table("default_params")->selectRaw("modul,name,note,is_active,params,prepared_query")->orderBy('modul')->get();
        return view("defaults.paramaker-frontend",compact('list'));
    });
    
    $router->get('/schema/{api}', function(Request $req, $api){
        return (new \App\Http\Controllers\LaradevController)->getSchema( $api );
    });

    $router->get('/frontend', function(){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        try{
            function querySort ($x, $y) {
                return strcasecmp($x->model, $y->model);
            }
            $models = json_decode(file_get_contents("models.json"));
            usort($models, 'querySort');
        }catch(Exception $e){
            return $e->getMessage();
        }
        return view("defaults.api",compact('models'));
    });
    $router->get('/simulation', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view("defaults.simulation");
    });
    $router->get('/uploader', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman uploader',
            'url'=>url("docs/uploader")
        ]);
    });
    $router->post('/uploader', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        if(!isset($req->password) || $req->password!=env("BACKENDPASSWORD","pulangcepat")){
            return view('defaults.unauthorized')->with('data',[
                'page'=>'halaman uploader',
                'url'=>url("docs/uploader"),
                'salah'=>true
            ]);
        }else{
            return view("defaults.uploader");
        }
    });
    $router->get('/editor', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman backend',
            'url'=>url("docs/editor")
        ]);
    });
    $router->post('/editor', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        if(!isset($req->password) || $req->password!=env("BACKENDPASSWORD","pulangcepat")){
            return view('defaults.unauthorized')->with('data',[
                'page'=>'halaman backend',
                'url'=>url("docs/editor"),
                'salah'=>true
            ]);
        }else{
            return view("defaults.editor");
        }
    });
    $router->get('/reporting', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman report template',
            'url'=>url("docs/reporting")
        ]);
    });
    $router->post('/reporting', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        $tables = array_filter(DB::connection()->getDoctrineSchemaManager()->listTableNames(),function($tb){
            return strpos($tb,"report_template")!==false;
        });
        if(count($tables)==0){
            return "table _report_template does not exist";
        }
        
        if(!isset($req->password) || $req->password!=env("BACKENDPASSWORD","pulangcepat")){
            return view('defaults.unauthorized')->with('data',[
                'page'=>'halaman report template',
                'url'=>url("docs/reporting"),
                'salah'=>true
            ]);
        }else{
            $table = array_values($tables)[0]; 
            $list = DB::table($table)->select('name','template','id')->get();
            return view("defaults.reporting",compact('list','table'));
        }
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

    $router->get('/paramaker', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman template prepared parameter',
            'url'=>url("docs/paramaker")
        ]);
    });
    $router->post('/paramaker', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        if(!Schema::hasTable("default_params")){
            abort(404);
        }
        
        if(!isset($req->password) || $req->password!=env("BACKENDPASSWORD","pulangcepat")){
            return view('defaults.unauthorized')->with('data',[
                'page'=>'halaman template prepared parameter',
                'url'=>url("docs/paramaker"),
                'salah'=>true
            ]);
        }else{
            $list = DB::table("default_params")->orderBy('modul')->get();
            return view("defaults.paramaker",compact('list'));
        }
    });

    $router->get('/blades', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman backend',
            'url'=>url("docs/blades")
        ]);
    });
    $router->post('/blades', function(Request $req){
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }
        if(!isset($req->password) || $req->password!=env("BACKENDPASSWORD","pulangcepat")){
            return view('defaults.unauthorized')->with('data',[
                'page'=>'halaman blades',
                'url'=>url("docs/blades"),
                'salah'=>true
            ]);
        }else{            
            try{
                $data = [
                    'page'=>'halaman blades',
                    'url'=>url("docs/blades"),
                    'password'=>$req->password,
                    'salah'=>true
                ];
            }catch(Exception $e){
                return $e->getMessage();
            }
            $dir = resource_path("views/projects");
            
            if( ! File::exists($dir) ){
                File::makeDirectory( $dir, 493, true);
            }
            $files = array_filter(scandir($dir),function($dt){
                return !in_array($dt,['.','..']);
            });
            $files = array_values($files);
            return view("defaults.blades", compact("files") );
        }
    });
});
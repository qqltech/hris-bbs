<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

$router->group(['prefix'=>'docs'], function () use ($router) {
    $router->get('/frontend-params', function(){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        $list = DB::table("default_params")->selectRaw("modul,name,note,is_active,params,prepared_query")->orderBy('modul')->get();
        return view("defaults.paramaker-frontend",compact('list'));
    });
    
    $router->get('/schema/{api}', function(Request $req, $api){
        return (new \App\Http\Controllers\LaradevController)->getSchema( $api );
    });

    $router->get('/frontend', function(){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view("defaults.simulation");
    });
    $router->get('/uploader', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman uploader',
            'url'=>url("docs/uploader")
        ]);
    });
    $router->post('/uploader', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman backend',
            'url'=>url("docs/editor")
        ]);
    });
    $router->post('/editor', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman report template',
            'url'=>url("docs/reporting")
        ]);
    });
    $router->post('/reporting', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman backend',
            'url'=>url("docs/backend")
        ]);
    });
    $router->post('/backend', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view("docs.docs");
    });
    
    $router->get('/documentation/{dt}', function($dt){
        return view("docs.docs-".(str_replace([".md","_"],["",""],strtolower($dt))) );
    });

    $router->get('/paramaker', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman template prepared parameter',
            'url'=>url("docs/paramaker")
        ]);
    });
    $router->post('/paramaker', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman backend',
            'url'=>url("docs/blades")
        ]);
    });
    $router->post('/blades', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
    
    $router->get('/raw', function(Request $req){
        if(!$req->has('trait') && !$req->has('helper') && !$req->has('controller') && !$req->has('user')
            && !$req->has('composer') && !$req->has('app')
            ) {
            return response()->json([
                'helpers', 'traits', 'controllers', 'users', 'composers','apps'
            ],404);
        };
        
        if($req->has('helper')){
            $data = File::get( app_path("Helpers/GlobalHelper.php") );
        }elseif($req->has('trait')){
            $data = File::get( app_path("Traits/ModelTrait.php") );
        }elseif($req->has('controller')){
            $data = File::get( app_path("Http/Controllers/ApiFixedController.php") );
        }elseif($req->has('user')){
            $data = File::get( app_path("Http/Controllers/UserController.php") );
        }elseif($req->has('composer')){
            $data = File::get( base_path("composer.json") );
        }elseif($req->has('app')){
            $data = File::get( base_path("bootstrap/app.php") );
        }

        return view("defaults.raw", compact('data'));
    });

    $router->get('/activities', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        
        return view('defaults.unauthorized')->with('data',[
            'page'=>'halaman dev activities',
            'url'=>url("docs/activities")
        ]);
    });

    $router->post('/activities', function(Request $req){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        return getDeveloperActivities();
    });

    $router->get('/activities/{id}', function( Request $req, $id ){
        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
        }
        $activities = Cache::get( "developer_activities" );
        foreach($activities as $act){
            if( @$act['id']==$id && @$act['diff'] ){
                $diff = $act['diff'];
                $css = url("defaults/diff-table.css");
                $result = \Jfcherng\Diff\Factory\RendererFactory::make('SideBySide', [
                    'showHeader'=>false
                ])->renderArray(json_decode( $diff, true ));

                return "<link rel='stylesheet' href='$css'><p style='font-weight:semibold;'>$id ~ {$act['time']} ~ {$act['action']} ~ {$act['file']}</p>".$result;
            }
        }
        return "detail was not-found";
    });
});
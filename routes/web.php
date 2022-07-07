<?php
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
//====================================================================================BASIC
$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/logout','UserController@logout');
    $router->get('/me', "UserController@user");
    $router->post('/change-password', "UserController@changePassword");
});
$router->post('/login', "UserController@login");
$router->post('/register', "UserController@register");
$router->get('/verify/{token}', "UserController@verify");
//====================================================================================

$router->get('/telegram/{command}','TelegramController@index');
$router->get('/telegram-webhook','TelegramController@webhook');
$router->get('/get-updates', "sseController@getUpdate");
$router->post('/model', "ModelerController@modelFromDB");
$router->get('/api','NonApiController@resources');

$router->group(['middleware' => 'project'], function () use ($router) {
    $router->get('/', function (Request $request) use ($router) {
        if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            return response()->json("SERVER WAS CLOSED",404);
        }

        if( env("LANDING_RESPONSE") ){
            $funcArr = explode(".", env("LANDING_RESPONSE"));
            $class = getCustom($funcArr[0]);
            $func = $funcArr[1];
            return $class->$func($request);
        }

        if( !env("TUTORIAL",false) ){
            return app()->version();
        }
        return response()->json(["info"=>"welcome to LARAHAN fast Api Laravel Lumen-based!",
            "data"=>[
                "version"=>app()->version(),
                "documentation" => url("/docs/documentation"),
                "operation" => url("/operation"),
                "config" => url("/laradev"),
                "frontend" => url("/docs/frontend"),
                "backend" => url("/docs/backend"),
                "visualisasi DB" => url("/visual.html"),
                "simulation" => url("/docs/simulation"),
                "report templating" => url("/docs/reporting"),
                "uploader" => url("/docs/uploader")
            ]
        ]);
    });
});


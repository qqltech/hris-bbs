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

$router->get('/', function () use ($router) {
    if( strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
        return response()->json("SERVER WAS CLOSED",404);
    }
    return response()->json(["info"=>"welcome to LARAHAN fast Api LaravelLumen-based!",
        "data"=>[
            "config" => url("/laradev"),
            "frontend" => url("/docs/frontend"),
            "backend" => url("/docs/backend"),
            "visualisasi DB" => url("/visual.html"),
            "operation" => url("/operation"),
            "simulation" => url("/docs/simulation"),
            "documentation" => url("/docs/documentation"),
            "uploader" => url("/docs/uploader")
        ]
    ]);
});


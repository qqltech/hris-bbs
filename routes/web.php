<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
//====================================================================================BASIC
$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/logout','UserController@logout');
    $router->get('/me', "UserController@user");
    $router->post('/unlock-screen', "UserController@unlockScreen");
    $router->post('/change-password', "UserController@changePassword");
});
$router->post('/reset-password-link','UserController@ResetPasswordLink');
$router->get('/reset-password-verify/{token}','UserController@ResetPasswordTokenVerify');
$router->post('/reset-password','UserController@ResetPassword');

$router->post('/login', "UserController@login");
$router->post('/register', "UserController@register");
$router->get('/verify/{token}', "UserController@verify");
//====================================================================================

$router->get('/telegram/{command}','TelegramController@index');
$router->get('/telegram-webhook','TelegramController@webhook');
$router->get('/get-updates', "sseController@getUpdate");
$router->get('/web/{name}','NonApiController@resources');

$router->group(['middleware' => 'project'], function () use ($router) {

    $router->get('/', function (Request $request){
        if( $landing = env("LANDING_RESOLVER") ){
            $funcArr = explode(".", $landing);
            $class = getCore($funcArr[0]) ?? getCustom($funcArr[0]);
            $func = $funcArr[1];
            return $class->$func($request);
        }

        if( File::exists( public_path('app/index.html') ) ){
            return redirect('/app');
        }

        if( !env("TUTORIAL",false) || strtolower(env("SERVERSTATUS","OPEN"))=='closed'){
            abort(401);
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
                "uploader" => url("/docs/uploader"),
                "activities" => url("/docs/activities"),
            ]
        ]);
    });
});
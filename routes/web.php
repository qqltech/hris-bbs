<?php
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
//====================================================================================BASIC
$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/logout','UserController@logout');
    $router->get('/me', "UserController@user");
});
$router->post('/login', "UserController@login");
$router->post('/register', "UserController@register");
//====================================================================================

$router->get('/telegram/{command}','TelegramController@index');
$router->get('/telegram-webhook','TelegramController@webhook');
$router->get('/get-updates', "sseController@getUpdate");
$router->post('/model', "ModelerController@modelFromDB");
$router->get('/api','NonApiController@resources');

$router->get('/', function () use ($router) {
    return response()->json(["info"=>"welcome to the jungle!",
        "data"=>[
            "frontend" => url("/docs/frontend"),
            "backend" => url("/docs/backend"),
            "operation" => url("/operation"),
        ]
    ]);
});
// $router->get('/',function(){
    
//     $data1 = [
//         "data1" => 1,
//         "data2" => 3
//     ];
//     return array_except($data1,["data1"]);
//     // return \Illuminate\Http\Response::customMethod();
//     // return (new \App\Models\BasicModels\inv_tra_material_transfer_d_item)->customFind(1);
//     $p = (Object)[];
//     $p->where_raw = "this.id = 21";
//     $p->order_by  = "this.id";
//     $p->order_type= "ASC";
//     $p->order_by_raw=null;
//     $p->search="aku";
//     $p->paginate  = 22;
//     return (new \App\Models\BasicModels\inv_tra_material_transfer_d_item)->customGet($p);
//     return array_merge([
//         "data1"=>"inidata1"
//     ],[
//         "datatambahan" => "datakutambah"
//     ]);
//     $data = [
//         "dia"=>'aku',
//         "magi_d_item" =>[
//             "data1" => "data1"
//         ]
//     ];
//     unset($data['dia']);
//     foreach($data as $key => $isi){
//         echo $key.(is_array($isi)?"[array]":"[var]")."<br>";
//     }
// });


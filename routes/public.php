<?php
use Illuminate\Http\Request;
$router->group(['prefix'=>'public'], function () use ($router) {
    $router->get('/{modelname}/{function}', function(Request $req,$modelname,$function){
        $modelCandidate = "\App\Models\CustomModels\\$modelname";
        if( !class_exists( $modelCandidate ) ){
            return response()->json("Model [$modelname] does not exist",404);
        }
        $function = "public_".$function;
        $model = new $modelCandidate;
        if( !method_exists( $model, $function ) ){
            return response()->json("function [$function] in Model [$modelname] does not exist",400);
        }
        $result = $model->$function($req);
        return $result;
    });
    $router->post('/{modelname}/{function}', function(Request $req,$modelname,$function){
        $modelCandidate = "\App\Models\CustomModels\\$modelname";
        if( !class_exists( $modelCandidate ) ){
            return response()->json("Model [$modelname] does not exist",404);
        }
        $function = "public_".$function;
        $model = new $modelCandidate;
        if( !method_exists( $model, $function ) ){
            return response()->json("function [$function] in Model [$modelname] does not exist",400);
        }
        $result = $model->$function($req);
        return $result;
    });
});
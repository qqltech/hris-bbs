<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Image;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
// use PDF;
use Excel;

class ApiCustomController extends Controller
{
    private $user;
    private $messages       = [];
    private $modelName;
    private $functionName;
    private $isAuthorized    =true;

    public function __construct(Request $request)
    {
        $this->user        = \Auth::check()?\Auth::user():null;
        $this->modelName    = $request->route("modelname");
        $this->functionName = "custom_".$request->route("function");
        $this->is_model_and_function_exist( $this->modelName,$this->functionName );
    }
    private function getParentClass($model)
    {
        $string = "\\".get_parent_class($model);
        $newModel = new $string;
        return $newModel;
    }
    private function is_model_and_function_exist($modelName, $functionName)
    {
        $modelCandidate = "\App\Models\CustomModels\\$modelName";
        if( !class_exists( $modelCandidate ) ){
            $this->messages[] ="[UNKNOWN] model [$modelName] does not exist";
            $this->isAuthorized=false;
        }else{
            $model = new $modelCandidate;
            if( !method_exists( $model, $functionName ) ){
                $functionResponse = str_replace("custom_","",$functionName);
                $this->messages[] ="[UNKNOWN] function [$functionResponse] in Model [$modelName] does not exist";
                $this->isAuthorized=false;
            }
        }
    }
    public function router(Request $request)
    {
        if(!$this->isAuthorized){
            return response()->json(["messages"=>$this->messages],400);
        }else{
            $modelCandidate = "\App\Models\CustomModels\\$this->modelName";
            $function = $this->functionName;
            $model = new $modelCandidate;
            return $model->$function($request);
        }
    }
}
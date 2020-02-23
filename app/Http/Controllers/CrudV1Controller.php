<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
class CrudV1Controller extends Controller
{
    private $namespace;
    private $parentModel;
    private $forbiddenColumns = ["id","created_at","updated_at","creator_user_id","updater_user_id","approver_user_id"];
    public function __construct()
    {
        $this->namespace = "\App\Models\CustomModels";
    }
    
//============================================================================================CRUD 1 LEVEL
    private function isDetailExist($model, $detailName){
        return !method_exists($model,$detailName)?false:true;
    }
    private function isDetailOfDetailExist($detailModel, $detailOfDetailName){
        return !method_exists($detailModel,$detailOfDetailName)?false:true;
    }
    private function isColumnsExist($model, $key){
        return !in_array($key,$model->columns)?false:true;
    }
    private function createLevel1($request, $model ){
        DB::beginTransaction();
        try{
            $validator = Validator::make($request->all(),$model->createValidator);
            if ($validator->fails()) {
                return response()->json($validator->errors(),422);
            }
            $parentTotal = 0;
            $detailTotal = 0;
            $detailOfDetailsTotal = 0;
            $detailData   = [];
            $parentSchema = [];
            $detailSchema = [];
            $detailOfDetailsSchema = [];
            
            foreach(array_keys($request->all()) as $key){
                if (strpos($key, '_d_') !== false && is_array( ($request->$key) ) ) {
                    if(count($request->$key)==0){continue;}
                    if( !$this->isDetailExist($model, str_replace('dtl_','', $key) )){
                        return response()->json(['error'=>"model:[$key] is not exist as a detail of ".$model->getTable()],422);
                    }
                    $detailModel = "$this->namespace\\".str_replace('dtl_','', $key);
                    $detailModel = new $detailModel;
                    foreach( array_keys( ($request->$key) [0]) as $detailKey ){
                        if (strpos($detailKey, '_d_') !== false && is_array((($request->$key)[0])[$detailKey]) ) {                            
                            if(count((($request->$key)[0])[$detailKey])==0){continue;}
                            if( !$this->isDetailExist($detailModel, str_replace('dtl_','', $detailKey) )){
                                return response()->json(['error'=>"model:[$detailKey] is not exist as a detail of $key"],422);
                            }

                            $detailOfDetailModel = "$this->namespace\\".str_replace('dtl_','', $detailKey);
                            $detailOfDetailModel = new $detailOfDetailModel;
                            foreach( array_keys((($request->$key)[0])[$detailKey][0]) as $detailOfDetailKey ){
                                if(!$this->isColumnsExist($detailOfDetailModel, $detailOfDetailKey)){
                                    return response()->json([
                                        'error'=>"column:[$detailOfDetailKey] is not exist in $detailKey",
                                        'acceptedColumns'=> array_filter($detailOfDetailModel->columns,function($data){
                                            if(!in_array($data, $this->forbiddenColumns))return $data;
                                        })
                                    ],422);
                                }
                                $detailOfDetailsSchema[$detailKey][] =  $detailOfDetailKey ;
                            }
                        }else{
                            if(!$this->isColumnsExist($detailModel, $detailKey)){
                                return response()->json([
                                    'error'=>"column:[$detailKey] is not exist in $key",
                                    'acceptedColumns'=> array_filter($detailModel->columns,function($data){
                                        if(!in_array($data, $this->forbiddenColumns))return $data;
                                    })
                                ],422);
                            }
                            $detailSchema[$key][] = $detailKey;
                        }
                    }
                    $detailData[$key] = $request->$key;
                }else{
                    if(!$this->isColumnsExist($model, $key)){
                        return response()->json([
                            'error'=>"column:[$key] is not exist in ".$model->getTable(),
                            'acceptedColumns'=> array_filter($model->columns,function($data){
                                if(!in_array($data, $this->forbiddenColumns))return $data;
                            })
                        ],422);
                    }

                    $parentSchema[] = $key;
                }
            }
            
            $parentModel = "\\".get_parent_class($model);
            $createdParent  = (new $parentModel)->create( $request->only($parentSchema) );
            $parentTotal+=1;
            foreach($detailData as $detailKey => $data){
                foreach($data as $detail){
                    $detailModel = "$this->namespace\\".str_replace('dtl_','', $detailKey);
                    $detailModel = new $detailModel;
                    $detailParentModel = "\\".get_parent_class($detailModel);
                    $detailParentModel = new $detailParentModel;
                    $createdDetail = $detailParentModel->create( array_merge(array_only($detail,$detailParentModel->columns),[$model->getTable()."_id"=>$createdParent->id]) );
                    $detailTotal+=1;
                    $detailOfDetail = array_filter($detail,function($detailData, $key){
                        if (strpos($key, '_d_') !== false) {
                            return $detailData;
                        }
                    },ARRAY_FILTER_USE_BOTH);
                    foreach($detailOfDetail as $keyDet => $valDet){
                        foreach($valDet as $detOfDet){
                            $detailOfDetailModel = "$this->namespace\\".str_replace('dtl_','', $keyDet);
                            $detailOfDetailModel = new $detailOfDetailModel;
                            $detailOfDetailParentModel = "\\".get_parent_class($detailOfDetailModel);
                            $detailOfDetailParentModel = new $detailOfDetailParentModel;    
                            $createdDetail = $detailOfDetailParentModel->create( array_merge(array_only($detOfDet,$detailOfDetailParentModel->columns),[str_replace('dtl_','', $detailKey)."_id"=>$createdDetail->id]) );
                            $detailOfDetailsTotal+=1;
                        }
                    }
                }
            }

        }catch(Exception $e){
            DB::rollback();
            return response()->json(["error"=>$e->getMessage()]);
        }
        DB::commit();
        // logTg("laradev", (\Auth::user()->name)." created data $modelName successfully");
        $response = $model->afterCreate($model, $request);
        return response()->json([
            "data"=> [
                "id"    => $createdParent->id,
                "rows"=>[
                    "parents"=> "$parentTotal rows",
                    "details"=> "$detailTotal rows",
                    "details of details"=>"$detailOfDetailsTotal rows"
                ]
            ],
            "info" => $response?$response:"data has been created successfully"
        ]);
    }
    private function updateLevel1($request, $model ){
        $validator = Validator::make($request->all(),$model->updateValidator);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        return "started to update";
    }
    private function deleteLevel1($request, $model , $id){
        DB::beginTransaction();
        try{
            $data = $model->find($id);
            if(!$data){
                return response()->json(["error"=>"id:[$id] on ".$model->getTable()." is not exist" ], 422);
            }
            $data->delete();
        }catch(Exception $e){
            DB::rollback();
            return response()->json(["error"=>$e->getMessage()]);
        }
        DB::commit();
        $response = $model->afterDelete($model, $request, $id);
        return response()->json([
            "data"=> [
                "id"    => $id
            ],
            "info" => $response?$response:"data has been delete successfully"
        ]);
    }

    private function readLevel1($request, $model, $id ){
        $data = $model->onRead($model, $request, $id);     
        return $data;   
    //     return response()->json(array_merge(["data"=>$data->pluck('data')],
    //     [
    //         "total"=>$data->total(),
    //         "current_page"=>$data->currentPage(),
    //         "per_page"=>$data->perPage(),
    //         "from"=>$data->firstItem(),
    //         "to"=>$data->lastItem(),
    //         "last_page"=>$data->lastPage(),
    //         "has_next"=>$data->hasMorePages(),
    //         "prev"=>$data->previousPageUrl(),
    //         "next"=>$data->nextPageUrl()
    //     ]));
    }
    public function level1(Request $request, $modelname, $id=null){
        $modelCandidate = "$this->namespace\\$modelname";
        if(class_exists("$this->namespace\\$modelname")){
            $model = new $modelCandidate;
            $this->parentModel = $model;
        }else{
            return response()->json(["data"=>"model $modelname is not exist"],422);
        }
        switch( strtolower($request->method()) ){
            case 'post':
                $beforeCreate = $model->beforeCreate($model,$request);
                return $this->createLevel1( $beforeCreate['request'], $beforeCreate['model'] );
                break;
            case 'patch':
            case 'put':
                return $this->updateLevel1($request, $model, $id);
                break;
            case "delete":
                $beforeDelete = $model->beforeDelete($model,$request, $id);
                return $this->deleteLevel1($beforeDelete['request'], $beforeDelete['model'], $id);
                break;
            default:
                return $this->readLevel1($request, $model, $id);
        }

    }

//============================================================================================CRUD 2 LEVEL
    private function createLevel2($request, $model ){
        return "started to create";
    }
    private function updateLevel2($request, $model ){
        return "started to update";
    }
    private function deleteLevel2($request, $model ){
        return "started to delete";
    }
    private function readLevel2($request, $model, $id ){
        return "started to get";
    }
    public function level2(Request $request, $modelname, $id, $detailOfDetails){
        $modelCandidate = "$this->namespace\\$modelname";
        if( class_exists("$this->namespace\\$modelname") ){
            $model = new $modelCandidate;
            if( !method_exists($model,$detailOfDetails) ){
                return response()->json(["data"=>"subdetail $detailOfDetails is not exist"],422);
            }
        }else{
            return response()->json(["data"=>"model $modelname is not exist"],422);
        }
        switch( strtolower($request->method()) ){
            case 'post':
                return $this->createLevel2($request, $model, $id, $detailOfDetails);
                break;
            case 'patch':
            case 'put':
                return $this->updateLevel2($request, $model, $id, $detailOfDetails);
                break;
            case "delete":
                return $this->deleteLevel2($request, $model, $id, $detailOfDetails);
                break;
            default:
                return $this->readLevel2($request, $model, $id, $detailOfDetails);
        }

    }

//============================================================================================CRUD 3 LEVEL
    private function createLevel3($request, $model ){
        return "started to create";
    }
    private function updateLevel3($request, $model ){
        return "started to update";
    }
    private function deleteLevel3($request, $model ){
        return "started to delete";
    }
    private function readLevel3($request, $model, $id ){
        return "started to get";
    }
    public function level3(Request $request, $modelname, $id, $detailOfDetails){
        $modelCandidate = "$this->namespace\\$modelname";
        if( class_exists("$this->namespace\\$modelname") ){
            $model = new $modelCandidate;
            if( !method_exists($model,$detailOfDetails) ){
                return response()->json(["data"=>"subdetail $detailOfDetails is not exist"],422);
            }
        }else{
            return response()->json(["data"=>"model $modelname is not exist"],422);
        }
        switch( strtolower($request->method()) ){
            case 'post':
                return $this->createLevel3($request, $model, $id, $detailOfDetails);
                break;
            case 'patch':
            case 'put':
                return $this->updateLevel3($request, $model, $id, $detailOfDetails);
                break;
            case "delete":
                return $this->deleteLevel3($request, $model, $id, $detailOfDetails);
                break;
            default:
                return $this->readLevel3($request, $model, $id, $detailOfDetails);
        }

    }
}

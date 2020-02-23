<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
class ApiController extends Controller
{
    private $namespace;
    private $temporaryModel;
    private $parentModelPure;
    private $parentModel;
    private $childModelPure=[];
    private $childModel=[];
    private $grandchildModelPure=[];
    private $grandchildModel=[];
    private $REQUEST;
    private $REQUESTPARENT;
    private $REQUESTCHILDREN=[];
    private $REQUESTCHILDRENORIGINAL=[];
    private $REQUESTGRANDCHILDREN=[];
    private $EXCEPTION;
    private $operation;

    public function __construct()
    {
        $this->namespace = "\App\Models\CustomModels";
        $this->turn = 'PARENT';
    }
    private function getParentClass($model){
        $string = "\\".get_parent_class($model);
        $newModel = new $string;
        return $newModel;
    }
    private function operation_create(){
        $parentFinalRequest = $this->REQUESTPARENT->only( $this->parentModelPure->columns );
        if($this->operation == "CREATE"){
            $createdParentModel = ($this->getParentClass($this->parentModel))->create($parentFinalRequest);
            $eventClass = "afterCreate";
            $this->parentModelPure->$eventClass( $parentFinalRequest, $createdParentModel );
        }else{
            $createdParentModel = $this->parentModel->update($parentFinalRequest);
            $eventClass = "afterUpdate";
            $this->parentModelPure->$eventClass( $parentFinalRequest, $createdParentModel );
            $createdParentModel = $this->parentModel;
        }
        if( count($this->REQUESTCHILDREN)>0 ){
            foreach( $this->REQUESTCHILDREN as $key=>$childRequests ){
                $childModel = $this->childModel[$key];
                $parentId   = $createdParentModel->id;
                foreach($childRequests as $childReq){
                    $childFinalRequest = $childReq->merge([ $this->parentModel->getTable()."_id" => $parentId ]);
                    $createdChildModel = ($this->getParentClass($childModel))->create( $childFinalRequest->all() );
                    $this->childModel[$key]->$eventClass( $childFinalRequest->all(), $createdChildModel );
                    if( count($this->REQUESTGRANDCHILDREN)>0 ){
                        foreach( $this->REQUESTGRANDCHILDREN as $keygrandchild=>$grandchildRequests ){
                            $grandchildModel = $this->grandchildModel[$keygrandchild];
                            $childId   = $createdChildModel->id;
                            foreach($grandchildRequests as $grandchildReq){
                                $grandchildFinalRequest = $grandchildReq->merge([ $this->childModel[$key]->getTable()."_id" => $childId ]);
                                $createdGrandChildModel = ($this->getParentClass($grandchildModel))->create( $grandchildFinalRequest->all() );
                                $this->grandchildModel[$keygrandchild]->$eventClass( $grandchildFinalRequest->all(), $createdGrandChildModel );
                            }
                        }
                    }
                }
            }
        }
    }
    private function checkWriterRole($model)
    {
        if($model->writers){
            $writers = $model->writers;
            foreach($writers as $key => $data){
                if(strpos($key, 'auth:') !== false){ // operasi untuk mendapatkan auth
                    if(!\Auth::check()){
                        $this->EXCEPTION = "Your are not a logged in user";
                        return false;
                    }
                    $authKey = str_replace("auth:", "", $key);
                    $authValue = \Auth::user()->$authKey?\Auth::user()->$authKey:null;
                    $mappedValue = explode(",",$data);
                    if( ($authValue==null || !in_array($authValue, $mappedValue)) && $mappedValue[0]!="all" ){
                        $this->EXCEPTION = "Sorry, your role is not authorized to do this operation";
                        return false;
                    }
                }
            }
        }
        return true;
    }
    private function autoCreate($model) // inject CustomModels -> $autoCreate Array
    {
        $fixedArray  = [];
        if($model->autoCreate){
            $arrayCreate = $model->autoCreate;
            foreach($arrayCreate as $key => $data){
                if(strpos($data, 'auth:') !== false){ // operasi untuk mendapatkan auth
                    $authKey = str_replace("auth:", "", $data);
                    $newData = \Auth::user()->$authKey;
                }else{
                    $newData = $data;
                }
                $fixedArray [$key] = $newData;
            }
        }
        return $fixedArray;
    }
    private function eventBefore($model, $event)
    {
        $eventName = "before".ucfirst($event);  
        if($this->turn=='PARENT'){
            $beforeCreate = $this->parentModel->$eventName( $this->REQUESTPARENT, $model, ($event=='create')?null:$model->id ); // event beforeCreate di customModel
            $this->REQUESTPARENT = $beforeCreate['request']; // override request dengan array baru yg berasal dari event beforeCreate
            $this->parentModel = $beforeCreate['model']; // override model dengan model baru yg berasal dari event beforeCreate
        }elseif($this->turn=='CHILDREN'){
            foreach($this->REQUESTCHILDREN[$model->getTable()] as $key=>$childRequest){
                $beforeCreate = $model->$eventName( $childRequest, $model ); // event beforeCreate di customModel
                $this->REQUESTCHILDREN[$model->getTable()][$key] = $beforeCreate['request']; // override request dengan array baru yg berasal dari event beforeCreate
                $this->childModel[$model->getTable()] = $beforeCreate['model']; // override model dengan model baru yg berasal dari event beforeCreate
                $this->childModelPure [$model->getTable()] = $beforeCreate['model'];
            }
        }elseif($this->turn=='GRANDCHILDREN'){
            foreach($this->REQUESTGRANDCHILDREN[$model->getTable()] as $key=>$childRequest){
                $beforeCreate = $model->$eventName( $childRequest, $model ); // event beforeCreate di customModel
                $this->REQUESTGRANDCHILDREN[$model->getTable()][$key] = $beforeCreate['request']; // override request dengan array baru yg berasal dari event beforeCreate
                $this->grandchildModel[$model->getTable()] = $beforeCreate['model']; // override model dengan model baru yg berasal dari event beforeCreate
                $this->grandchildModelPure [$model->getTable()] = $beforeCreate['model'];
            }
        }
    }
    private function checkRequired($model) // inject CustomModels -> $autoCreate Array
    {
        if($this->turn=='PARENT'){
            $data = $this->REQUESTPARENT->all();
        }elseif($this->turn=='CHILDREN'){
            $data = $this->REQUESTCHILDREN[$model->getTable()];
        }elseif($this->turn=='GRANDCHILDREN'){
            $data = $this->REQUESTGRANDCHILDREN[$model->getTable()];
        }
        if($model->required){
            $arrayRequired = $model->required;
            if($this->turn=='PARENT'){
                $arrayFromRequest = array_keys($data);
                $notPresent = array_filter($arrayRequired,function($dt)use($arrayFromRequest){
                    if(!in_array($dt, $arrayFromRequest)){
                        return $dt;
                    }
                });
                if(count($notPresent)>0){
                    $this->EXCEPTION = "fields: [".implode(",",$notPresent)."] are required in ".$model->getTable();
                    return false;
                }
            }else{
                foreach($data as $key=>$childData){
                    $arrayFromRequest = array_keys($childData);
                    $notPresent = array_filter($arrayRequired,function($dt)use($arrayFromRequest){
                        if(!in_array($dt, $arrayFromRequest)){
                            return $dt;
                        }
                    });
                    if(count($notPresent)>0){
                        $this->EXCEPTION = "fields: [".implode(",",$notPresent)."] are required in $this->turn [".$model->getTable()."] index_ke [".($key+1)."]";
                        return false;
                    }                    
                }
            }
        }
        return true;
    }
    private function checkValidator($model, $type='create')
    {
        if($this->turn=='PARENT'){
            $data = $this->REQUESTPARENT->all();
        }elseif($this->turn=='CHILDREN'){
            $data = $this->REQUESTCHILDREN[$model->getTable()];
        }elseif($this->turn=='GRANDCHILDREN'){
            $data = $this->REQUESTGRANDCHILDREN[$model->getTable()];
        }
        $type = $type."Validator";
        if($this->turn=='PARENT'){
            $validator = Validator::make($data,$model->$type);
            if ($validator->fails()) {
                $this->EXCEPTION = array_merge( $validator->errors()->all(),["model"=>$model->getTable()]);
                return false;
            }
        }else{
            foreach($data as $key=> $childData){
                $validator = Validator::make($childData,$model->$type);
                if ($validator->fails()) {
                    $this->EXCEPTION = array_merge( $validator->errors()->all(),["model"=>$model->getTable(),"index_ke"=>$key+1]);
                    return false;
                }                  
            }
        }
        return true;
    }
    private function is_model_exist($modelName)
    {        
        $modelCandidate = "$this->namespace\\$modelName";
        if( !class_exists( $modelCandidate ) ){
            $this->EXCEPTION ="model [$modelName] is not exist";
            return false;
        }
        $this->temporaryModel = new $modelCandidate;
        return true;
    }
    private function set_child_model()
    {
        $detailsArray = $this->parentModelPure->details; //get array $details di basicModel
        $childNotExist = [];
        foreach( array_keys($this->REQUEST->all()) as $key ){
            if(is_array($this->REQUEST->$key) && count($this->REQUEST->$key)>0 && (strpos($key, '_d_') !==false || strpos($key, '_detail_') !==false || strpos($key, '_dtl_') !==false ) ){                
                if( !in_array($key, $detailsArray) || !$this->is_model_exist( $key ) ){
                    $childNotExist[] = $key;
                }else{
                    $modelCandidate = "$this->namespace\\$key";
                    $this->childModelPure [$key] = new $modelCandidate;
                    $this->childModel [$key]     = new $modelCandidate;
                    $this->REQUESTCHILDREN[$key] = $this->REQUEST->$key;
                    $this->REQUESTCHILDRENORIGINAL[$key] = $this->REQUEST->$key;
                }
            }
        }
        if( count($childNotExist)>0 ){
            $this->EXCEPTION ="detail_models: [".implode(",",$childNotExist)."] are not exist";
            return false;            
        }
        return true;
    }
    private function set_grandchild_model()
    {        
        $grandchildNotExist = [];
        foreach( $this->REQUESTCHILDRENORIGINAL as $childModelName=>$childRequests ){
            $childModel = $this->childModel[$childModelName];
            $detailsArray = $childModel->details; //get array $details di basicModel
            
            foreach($childRequests as $index => $childReq){
                foreach( array_keys($childReq) as $key ){
                    if(is_array($childReq[$key]) && count($childReq[$key])>0 && (strpos($key, '_d_') !==false || strpos($key, '_detail_') !==false || strpos($key, '_dtl_') !==false ) ){                
                        if( !in_array($key, $detailsArray) || !$this->is_model_exist( $key ) ){
                            $grandchildNotExist[] = $key;
                        }else{
                            $modelCandidate = "$this->namespace\\$key";
                            $this->grandchildModelPure [$index][$key] = new $modelCandidate;
                            $this->grandchildModel [$index][$key]     = new $modelCandidate;
                            $this->REQUESTGRANDCHILDREN[$index][$key] = $childReq[$key];
                        }
                    }
                }
            }
        }
        if( count($grandchildNotExist)>0 ){
            $this->EXCEPTION ="detail_models: [".implode(",",$grandchildNotExist)."] are not exist";
            return false;            
        }
        return true;
    }

    private function checkChildrenCreate($model)
    {
        if(!$this->checkWriterRole($model)){
            return false;
        }
        if(!$this->checkRequired($model)){
            return false;
        }
        if(!$this->checkValidator($model, 'create')){
            return false;
        }
        // // $this->REQUEST = $this->REQUEST->hanya( array_merge($modelPure->createable,$modelPure->details ) );
        if($this->turn=='CHILDREN'){
            $data = $this->REQUESTCHILDREN[$model->getTable()];        
            foreach($data as $key=> $childData){
                $request = $this->REQUEST->capture();
                $singleChildRequest = $request->reuse( $childData ); //->merge( $this->autoCreate($model) ); //Inject array autoCreate           
                $singleChildRequest->hanya( array_merge($model->createable) );
                $singleChildRequest->merge( $this->autoCreate($model) );
                $this->REQUESTCHILDREN[$model->getTable()][$key] = $singleChildRequest;
            }
        }elseif($this->turn=='GRANDCHILDREN'){
            $data = $this->REQUESTGRANDCHILDREN[$model->getTable()];        
            foreach($data as $key=> $grandchildData){
                $request = $this->REQUEST->capture();
                $singleGrandChildRequest = $request->reuse( $grandchildData ); //->merge( $this->autoCreate($model) ); //Inject array autoCreate           
                $singleGrandChildRequest->hanya( array_merge($model->createable) );
                $singleGrandChildRequest->merge( $this->autoCreate($model) );
                $this->REQUESTGRANDCHILDREN[$model->getTable()][$key] = $singleGrandChildRequest;
            }
        }
        $this->eventBefore($model,'create'); //FIRE EVENT di CustomModel beforeCreate
        return true;
    }

    private function checkResourceExist($model, $id){
        $modelExist = $model->find($id);
        if(!$modelExist){
            $this->EXCEPTION ="ID $id in [".$model->getTable()."] is not exist";
            return false;
        }
        $this->temporaryModel = $modelExist;
        return true;
    }
    private function CREATE() //operasi CREATE parent-child-grandchild 3 levels
    {
        DB::beginTransaction();
        try{
            $this->turn = 'CHILDREN';
            if(!$this->set_child_model() ){ // cek sekalligus set CHILDREN (detail) MODELS dalam ARRAY
                return response()->json(["error"=>$this->EXCEPTION],422);
            }
            foreach($this->childModel as $key => $child){
                if(!$this->checkChildrenCreate($child)){
                    return response()->json(["error"=>$this->EXCEPTION],422);
                }
            }
            
            $this->turn = 'GRANDCHILDREN';
            if(!$this->set_grandchild_model() ){ // cek sekalligus set GRANDCHILD (detail of details) MODELS dalam ARRAY
                return response()->json(["error"=>$this->EXCEPTION],422);
            }
            return $this->grandchildModel[1]['inv_tra_material_transfer_d_item_d_other']->getTable();
            foreach($this->grandchildModel as $arrayModel){
                foreach($arrayModel as $modelname => $grandchild){
                    if(!$this->checkChildrenCreate($grandchild)){
                        return response()->json(["error"=>$this->EXCEPTION],422);
                    }
                }
            }
            
           $this->operation_create(); //JALANKAN OPERASI CREATE SAMPAI 3 LEVEL
        }catch(Exception $e){
            DB::rollback();
            return response()->json(["error"=>$e->getMessage()]);
        }
        DB::commit();
        return response()->json(["status"=>"data created successfully"],201);
    }
    private function NEWCREATE($request, $parentModelName){
        $data = $request->all();
        $detailsArray  = $this->parentModelPure->details;//get array $details di basicModel
        $childNotExist = [];
        if( !$this->is_model_exist($parentModelName) ){ //jika TRUE $this->temporaryModel AUTO TERBUAT tiap kali dicek
            return response()->json(["error"=>$this->EXCEPTION],422);
        }
        $parentModel = $this->temporaryModel;
        if(!$this->checkWriterRole($parentModel)){
            return response()->json(["error"=>$this->EXCEPTION],401);
        }
        if(!$this->checkRequired($parentModel, $data)){
            return response()->json(["error"=>$this->EXCEPTION],422);
        }
        if(!$this->checkValidator($parentModel, 'create')){
            return response()->json(["error"=>$this->EXCEPTION],422) ;
        }
        foreach( $data as $fieldParent => $valueParent ){                            
                if( is_array($valueParent) && in_array($fieldParent, $detailsArray  )){
                    // unset()
                }else{
                    $modelCandidate = "$this->namespace\\$key";
                    $this->childModelPure [$key] = new $modelCandidate;
                    $this->childModel [$key]     = new $modelCandidate;
                    $this->REQUESTCHILDREN[$key] = $this->REQUEST->$key;
                    $this->REQUESTCHILDRENORIGINAL[$key] = $this->REQUEST->$key;
                }
        };
    }
    private function SIMPLEDELETE()
    {
        DB::beginTransaction();
        try{
            $oldParentModel = $this->parentModel;
            if( $this->parentModelPure->cascade ){
                foreach($this->parentModelPure->detailsChild as $granddetail){                    
                    $this->parentModel->$granddetail()->delete();
                }
                foreach($this->parentModelPure->details as $detail){                    
                    $this->parentModel->$detail()->delete();
                }
            }
            $this->parentModel->delete();
            $this->parentModelPure->afterDelete( $this->REQUESTPARENT, $oldParentModel, $oldParentModel->id );
        }catch(Exception $e){
            DB::rollback();
            return response()->json(["error"=>$e->getMessage()]);
        }
        DB::commit();
        return response()->json(["status"=>"data deleted successfully"],200);
    }
    private function DELETE() //operasi CREATE parent-child-grandchild 3 levels
    {
        DB::beginTransaction();
        try{
            $this->turn = 'CHILDREN';
            if(!$this->set_child_model() ){ // cek sekalligus set CHILDREN (detail) MODELS dalam ARRAY
                return response()->json(["error"=>$this->EXCEPTION],422);
            }
            foreach($this->childModel as $key => $child){
                if(!$this->checkChildrenCreate($child)){
                    return response()->json(["error"=>$this->EXCEPTION],422);
                }
            }
            $this->turn = 'GRANDCHILDREN';
            if(!$this->set_grandchild_model() ){ // cek sekalligus set GRANDCHILD (detail of details) MODELS dalam ARRAY
                return response()->json(["error"=>$this->EXCEPTION],422);
            }
            // return $this->REQUESTGRANDCHILDREN['inv_tra_material_transfer_d_item_d_other'][0];
            foreach($this->grandchildModel as $key => $grandchild){
                
                if(!$this->checkChildrenCreate($grandchild)){
                    return response()->json(["error"=>$this->EXCEPTION],422);
                }
            }                        
           $this->operation_create(); //JALANKAN OPERASI CREATE SAMPAI 3 LEVEL
        }catch(Exception $e){
            DB::rollback();
            return response()->json(["error"=>$e->getMessage()]);
        }
        DB::commit();
        return response()->json(["status"=>"data created successfully"],201);
    }

    private function UPDATEPUT() //operasi CREATE parent-child-grandchild 3 levels
    {
        DB::beginTransaction();
        try{
            $this->turn = 'CHILDREN';
            if(!$this->set_child_model() ){ // cek sekalligus set CHILDREN (detail) MODELS dalam ARRAY
                return response()->json(["error"=>$this->EXCEPTION],422);
            }
            foreach($this->childModel as $key => $child){
                if(!$this->checkChildrenCreate($child)){
                    return response()->json(["error"=>$this->EXCEPTION],422);
                }
            }
            $this->turn = 'GRANDCHILDREN';
            if(!$this->set_grandchild_model() ){ // cek sekalligus set GRANDCHILD (detail of details) MODELS dalam ARRAY
                return response()->json(["error"=>$this->EXCEPTION],422);
            }
            foreach($this->grandchildModel as $key => $grandchild){
                
                if(!$this->checkChildrenCreate($grandchild)){
                    return response()->json(["error"=>$this->EXCEPTION],422);
                }
            }
            $oldParentModel = $this->parentModel;
            if( $this->parentModelPure->cascade ){
                foreach($this->parentModelPure->detailsChild as $granddetail){                    
                    $this->parentModel->$granddetail()->delete();
                }
                foreach($this->parentModelPure->details as $detail){                    
                    $this->parentModel->$detail()->delete();
                }
            }
            
           $this->operation_create(); //JALANKAN OPERASI CREATE SAMPAI 3 LEVEL
        }catch(Exception $e){
            DB::rollback();
            return response()->json(["error"=>$e->getMessage()]);
        }
        DB::commit();
        return response()->json(["status"=>"data updated successfully"],200);
    }

    public function query(Request $request, $modelname, $id=null)
    {
        if( !$this->is_model_exist($modelname) ){ //jika TRUE $this->temporaryModel AUTO TERBUAT tiap kali dicek
            return response()->json(["error"=>$this->EXCEPTION],422);
        }
        $this->parentModelPure  = $this->temporaryModel;
        $this->parentModel      = $this->temporaryModel;
        $this->REQUEST          = $request;
        $this->REQUESTPARENT    = $request->capture();
        switch( strtolower($this->REQUEST->method()) ){
            case 'post':    //CREATE DATA 1-2-3 LEVELS DETAILS COMPLETE!
                if(!$this->checkWriterRole($this->parentModel)){
                    return response()->json(["error"=>$this->EXCEPTION],401);
                }
                if(!$this->checkRequired($this->parentModel)){
                    return response()->json(["error"=>$this->EXCEPTION],422);
                }
                if(!$this->checkValidator($this->parentModel, 'create')){
                    return response()->json(["error"=>$this->EXCEPTION],422) ;
                }
                $this->REQUESTPARENT->hanya( array_merge($this->parentModelPure->createable) );
                $this->REQUESTPARENT->merge( $this->autoCreate($this->parentModelPure) ); //Inject array autoCreate
                $this->eventBefore($this->parentModelPure,'create');
                $this->operation = "CREATE";
                return $this->CREATE();
                break;
            case 'patch':
            case 'put':
                if( !is_numeric($id) ){
                    return response()->json(["error"=>"ID must be a valid integer"],422);
                }
                if(!$this->checkWriterRole($this->parentModel)){
                    return response()->json(["error"=>$this->EXCEPTION],401);
                }
                if(!$this->checkValidator($this->parentModel, 'update')){
                    return response()->json(["error"=>$this->EXCEPTION],422) ;
                }
                if(!$this->checkResourceExist($this->parentModel, $id)){
                    return response()->json(["error"=>$this->EXCEPTION],404);
                }
                $this->parentModel = $this->temporaryModel;
                $this->eventBefore( $this->parentModel, 'update');
                $this->operation = "PUT";
                return $this->UPDATEPUT();
                break;
            case "delete":
                if( !is_numeric($id) ){
                    return response()->json(["error"=>"ID must be a valid integer"],422);
                }
                if(!$this->checkWriterRole($this->parentModel)){
                    return response()->json(["error"=>$this->EXCEPTION],401);
                }
                if(!$this->checkValidator($this->parentModel, 'delete')){
                    return response()->json(["error"=>$this->EXCEPTION],422) ;
                }
                if(!$this->checkResourceExist($this->parentModel, $id)){
                    return response()->json(["error"=>$this->EXCEPTION],404);
                }
                if(!$this->parentModelPure->deleteable){
                    return response()->json(["error"=>"[$modelname] is UNDELETEABLE"],422);
                }
                $this->parentModel = $this->temporaryModel;
                $this->eventBefore( $this->parentModel, 'delete');
                $this->operation = "DELETE";
                return $this->SIMPLEDELETE();
                break;
            default:
                return $this->READ($request, $model, $id);
        }

    }
}
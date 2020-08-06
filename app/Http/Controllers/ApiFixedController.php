<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Image;
use Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
// use PDF;
use Excel;

class ApiFixedController extends Controller
{
    private $requestData;
    private $requestMeta;
    private $operation='create';
    private $user;
    private $isAuthorized   = true;
    private $messages       = [];
    private $errors       = [];
    private $success        = [];
    private $parentModelName;
    private $lastParentId;
    private $lastParentName;
    private $operationId=null;
    private $operationOK=true;
    private $customOperation=false;
    private $originalRequest;
    private $isMultipart = false;
    private $formatDate='Y-m-d';
    private $isBackdoor = false;

    public function __construct(Request $request,$backdoor=false)
    {
        $this->isBackdoor = $backdoor;
        if($backdoor){
            return;
        }
        if( ! File::isDirectory(base_path('public/uploads') ) ) {
            File::makeDirectory(base_path('public/uploads') , 493, true);
        }
        if(config('tables')==null){
            config(['tables'=>[]]);
        }
        $this->formatDate=env("FORMAT_DATE_FRONTEND","d/m/Y");
        $this->isMultipart = (strpos($request->header("Content-Type"),"multipart") !==FALSE)?true:false;
        $this->originalRequest = $request->capture();
        $this->requestData = $request->all();
        $this->requestMeta = $request->getMetaData();
        if(config('request')==null){
            config(['request'=>$this->requestData]);
            config(['requestHeader'=>$this->requestMeta->header()]);
            config(['requestMethod'=>$this->requestMeta->method()]);
            config(['requestOrigin'=>$this->requestMeta->path()]);
        }
        if($this->isMultipart){
            $this->serializeMultipartData();
        }
        $this->user        = \Auth::check()?\Auth::user():null;        
        switch( strtolower($request->method()) ){
            case 'post' :
                $this->operation = "create";
                break;
            case 'delete' :
                $this->operation = "delete";
                break;
            case 'patch' :            
            case 'put' :
                $this->operation = "update";
                break;
            case 'patch' :
                $this->operation = "update";
                break;
            case 'get'  :
                $this->operation = "read";
                break;
        }
        $this->parentModelName=$request->route("modelname");
        $this->operationId=($request->route("id")!=null&&$request->route("id")!="")?$request->route("id"):null;
        if(!$this->is_model_exist( $this->parentModelName )){return;};
        if($this->operationId != null && !is_numeric($this->operationId) ){ $this->customOperation=true; return;}
        if(!$this->is_operation_authorized($this->parentModelName )){return;};
        if(!$this->is_data_required($this->parentModelName, $this->requestData)){return;};
        if(!$this->is_data_valid($this->parentModelName, $this->requestData)){return;};
        if(!$this->is_data_not_unique($this->parentModelName, $this->requestData)){return;};
        if(!$this->is_model_deletable($this->parentModelName, $this->requestData)){return;};
        $this->is_detail_valid($this->parentModelName, $this->requestData);
    }
    private function checkNumeric($data){
        try {
            $data+0; 
            return true;
        }catch(Exception $e){
            return false;
        };
    }
    private function serializeMultipartData(){
        foreach( $this->requestData as $key=>$value ){
            if( $this->checkNumeric($value) ){
                continue;
            }
            $triedJSON = json_decode( $value, true);
            $this->requestData [ $key ] = (json_last_error()==JSON_ERROR_NONE) ? $triedJSON:$value;
        }
    }
    private function getParentClass($model)
    {
        $string = "\\".get_parent_class($model);
        $newModel = new $string;
        return $newModel;
    }
    private function is_model_exist($modelName)
    {
        $modelCandidate = "\App\Models\BasicModels\\$modelName";
        if( !class_exists( $modelCandidate ) ){
            $this->errors[] ="[UNKNOWN]Model [$modelName] does not exist";
            $this->isAuthorized=false;
            return false;
        }
        return true;
    }
    private function is_operation_authorized($modelName)
    {
        $modelCandidate = "\App\Models\CustomModels\\$modelName";
        $model = new $modelCandidate;
        $function = $this->operation."RoleCheck";
        if(method_exists($model, $function)){
            if(!$model->$function()){
                $this->messages[] ="[UNAUTHORIZED]operasi $this->operation di [$modelName] dilarang!";
                $this->isAuthorized=false;
                return false;
            }
        }
        $model = null;
        return true;
    }
    private function is_data_required($modelName, $data)
    {
        if( !in_array($this->operation,["create"]) ){return true;}
        $modelCandidate = "\App\Models\CustomModels\\$modelName";
        $model = new $modelCandidate;
        $arrayRequired = $model->required;
        if(isset($data[0]) && is_array($data[0])){
            foreach ($data as $i => $isiData){
                $arrayFromRequest = array_keys($isiData);
                $notPresent = array_filter($arrayRequired, function($dt)use($arrayFromRequest){
                    if(!in_array($dt, $arrayFromRequest)){
                        return $dt;
                    }
                });
                if(count($notPresent)>0){
                    foreach($notPresent as $field){
                        $this->errors[] = "[REQUIRED]The $field field is required.[$modelName] index [$i]";
                    }
                    $this->isAuthorized=false;
                    return false;
                }
            }
        }else{
            $arrayFromRequest = array_keys($data);
            $notPresent = array_filter($arrayRequired,function($dt)use($arrayFromRequest){
                if(!in_array($dt, $arrayFromRequest)){
                    return $dt;
                }
            });
            if(count($notPresent)>0){
                foreach($notPresent as $field){
                    $this->errors[] = "[REQUIRED]The $field field is required.[$modelName]";
                }
                $this->isAuthorized=false;
                return false;
            }
        }
        $model = null;
        return true;
    }
    private function is_data_valid($modelName, $data)
    {
        if( !in_array($this->operation,["create","update"]) ){return true;}
        $modelCandidate     = "\App\Models\CustomModels\\$modelName";
        $model              = new $modelCandidate;
        $operationValidator = $this->operation."Validator";
        $arrayValidation    = $model->$operationValidator;        
        if(isset($data[0]) && is_array($data[0])){
            foreach ($data as $i => $isiData){
                $validator = Validator::make($isiData, $arrayValidation);
                if ( $validator->fails()) {
                    foreach($validator->errors()->all() as $error){
                        $this->errors[] = "[INVALID]".$error."[$modelName] index[$i]";
                    }
                    $this->isAuthorized=false;
                    return false;
                }
            }
        }else{
            $validator = Validator::make($data, $arrayValidation);
            if ( $validator->fails()) {
                foreach($validator->errors()->all() as $error){
                    $this->errors[] = "[INVALID]".$error."[$modelName]";
                }
                $this->isAuthorized=false;
                return false;
            }
        }
        return true;
    }
    private function is_data_not_unique($modelName, $data)
    {
        if( !in_array($this->operation,["create","update"]) ){return true;}
        $modelCandidate     = "\App\Models\BasicModels\\$modelName";
        $model              = new $modelCandidate;
        $arrayValidation    = $model->unique;
        if($this->operation == 'update'){
            $newArrayValidation = [];
            foreach($arrayValidation as $key => $validation){
                $newArrayValidation[$key] = $validation.",$this->operationId";
            }
            $arrayValidation = $newArrayValidation;
        }
        if(isset($data[0]) && is_array($data[0])){
            foreach ($data as $i => $isiData){
                $validator = Validator::make($isiData, $arrayValidation);
                if ( $validator->fails()) {
                    foreach($validator->errors()->all() as $error){
                        $this->errors[] = "[DUPLICATED]".$error."[$modelName] index[$i]";
                    }
                    $this->isAuthorized=false;
                    return false;
                }
            }
        }else{
            $validator = Validator::make($data, $arrayValidation);
            if ( $validator->fails()) {
                foreach($validator->errors()->all() as $error){
                    $this->errors[] = "[DUPLICATED]".$error."[$modelName]";
                }
                $this->isAuthorized=false;
                return false;
            }
        }
        return true;
    }
    private function is_detail_valid($modelName, $data)
    {
        if( !in_array($this->operation,["create","update"]) ){return true;}
        $modelCandidate = "\App\Models\BasicModels\\$modelName";
        $model          = new $modelCandidate;
        $detailsArray   = $model->details; //get array $details di basicModel
        if(isset($data[0]) && is_array($data[0])){
            foreach ($data as $i => $isiData){
                foreach( $isiData as $key => $value ){
                    if(is_array($value) && count($value)>0 && in_array($key, $detailsArray) ){                
                        $this->is_model_exist($key);
                        $this->is_operation_authorized($key );
                        $this->is_data_required($key, $value);
                        $this->is_data_valid($key,$value);
                        $this->is_detail_valid($key,$value);
                    }
                }
            }
        }else{
            foreach( $data as $key => $value ){
                if(is_array($value) && count($value)>0 && in_array($key, $detailsArray) ){                
                    $this->is_model_exist($key);
                    $this->is_operation_authorized($key );
                    $this->is_data_required($key, $value);
                    $this->is_data_valid($key,$value);
                    $this->is_detail_valid($key,$value);
                }
            }
        }
        $model = null;
    }
    private function is_model_deletable($modelName, $data)
    {
        if( !in_array($this->operation,["delete"]) ){return;}
        $modelCandidate = "\App\Models\CustomModels\\$modelName";
        $model          = new $modelCandidate;
        $detailsArray   = $model->details; 
        $heirs          = $model->heirs; 
        $cascade        = $model->cascade;
        $deleteable     = $model->deleteable;
        $deleteOnUse    = isset($model->deleteOnUse)?$model->deleteOnUse:false;
        if(!$deleteable){
            $this->messages[] = "UNDELETABLE: cannot delete [$modelName]";
            $this->isAuthorized=false;
            return false;
        }
        if(!$deleteOnUse){
            foreach( $heirs as $heir ){
                $modelCandidateHeir = "\App\Models\BasicModels\\$heir";
                $modelHeir          = new $modelCandidateHeir;
                $join               = $modelHeir->joins;
                foreach($join as $relation){
                    if(strpos($relation,$modelName)!==false){
                        $colArr = explode("=", $relation)[1];
                        $col    = explode(".", $colArr)[1];
                        $existing = $modelHeir->where($col, $this->operationId )->limit(1)->get();
                        if(count($existing)>0){
                            $this->messages[] = "USED: cannot delete id $this->operationId in [$modelName]. It is being used in child $heir";
                            $this->errors[] = "FAILED: cannot delete this resource, It is still being used in another resource.";
                            $this->isAuthorized=false;
                            return false;
                        };
                    }
                }
            }
        }
        if($cascade){
            foreach( $detailsArray as $detail ){             
                $this->is_model_deletable($detail, null);
            }
        }
        $model = null;
        return true;
    }
    private function createAdditionalData($model, $arrayData) // inject CustomModels -> $autoCreate Array
    {
        $fixedArray  = [];
        $dataKey     = $this->operation."AdditionalData";
        if($model->$dataKey){
            $arrayCreate = $model->$dataKey;
            foreach($arrayCreate as $key => $data){
                if(!in_array($key,$model->columns)){
                    $this->messages[] = "NOT ADDED: field $key in $dataKey cannot be add in [".$model->getTable()."]";
                    continue;
                }
                if(strpos($data, '[') !== false){
                    $function = $this->operation."_$key";
                    $newData = $model->$function((object)$arrayData);
                }elseif(strpos($data, 'auth:') !== false){ // operasi untuk mendapatkan auth
                    $authKey = str_replace("auth:", "", $data);
                    $newData = \Auth::user()->$authKey;
                }elseif(strpos($data, 'request:') !== false){ // operasi untuk mendapatkan auth
                    $arrayDataKey = str_replace("request:", "", $data);
                    $newData = $arrayData[$arrayDataKey];
                }else{
                    $newData = $data;
                }
                $fixedArray [$key] = $newData;
            }
        }
        return $fixedArray;
    }
    private function createEliminationData($model, $arrayData)
    {
        $columns = $model->columns;
        $fixedArray = [];
        $dropped = [];
        $dataKey     = $this->operation."able";
        foreach($arrayData as $key => $value){
            if(in_array($key, $columns)){
                $fixedArray [$key] = $value;
            }else{
                $dropped []=$key;
                $this->messages[] = "DROPPED: field $key was dropped in ".$model->getTable();
            }
        }
        $createableColumns = $model->$dataKey;
        foreach($fixedArray as $key => $value){
            if(!in_array($key, $createableColumns)){
                unset($fixedArray [$key]);
                $this->messages[] = "WARNING: field $key is not $dataKey in ".$model->getTable();
            }
        }
        return $fixedArray;
    }
    public function createOperation( $modelName, $data, $parentId=null, $parentName=null )
    {
        if(!$this->operationOK){return;}
        $modelCandidate = "\App\Models\CustomModels\\$modelName";
        $model          = new $modelCandidate;
        $detailsArray   = $model->details;
        
        if(isset($data[0]) && is_array($data[0])){
            foreach ($data as $i => $isiData){
                $additionalData = $this->createAdditionalData($model, $isiData);
                $eliminatedData = $this->createEliminationData($model, $isiData);
                $processedData  = array_merge($eliminatedData, $additionalData);
                if($parentId!=null){
                    $columns    = $model->columns;
                    $fkName     = $parentName;
                    if(!in_array($fkName."_id",$columns)){
                        $realJoins = $model->joins;
                        foreach($realJoins as $val){
                            $valArray = explode("=",$val);
                            if($valArray[0]==$fkName.".id"){
                                $fkName = explode(".",$valArray[1])[1];
                                break;
                            }
                        }
                    }else{
                        $fkName.="_id";
                    }
                    $processedData[$fkName] = $parentId;
                }
                $createBeforeEvent = $model->createBefore($model, $processedData, $this->requestMeta);
                if(isset($createBeforeEvent['errors'])){
                    $this->operationOK=false;
                    $this->errors = $createBeforeEvent['errors'];
                    return;
                }
                $finalData  = $createBeforeEvent["data"];
                
                $finalModel = ($this->getParentClass($model))->create(reformatData($finalData));
                $model->createAfter($finalModel, $processedData, $this->requestMeta, $finalModel->id);
                $this->success[] = "SUCCESS: data created in ".$model->getTable()." new id: $finalModel->id";
                foreach( $isiData as $key => $value ){
                    if(is_array($value) && count($value)>0 && in_array($key, $detailsArray) ){
                        $this->createOperation($key, $value,$finalModel->id, $modelName);
                    }
                }
            }
        }else{
            $additionalData = $this->createAdditionalData($model, $data);
            $eliminatedData = $this->createEliminationData($model, $data);
            $processedData  = array_merge($eliminatedData, $additionalData);
            if($parentId!=null){
                $columns    = $model->columns;
                $fkName     = $parentName;
                if(!in_array($fkName."_id",$columns)){
                    $realJoins = $model->joins;
                    foreach($realJoins as $val){
                        $valArray = explode("=",$val);
                        if($valArray[0]==$fkName.".id"){
                            $fkName = explode(".",$valArray[1])[1];
                            break;
                        }
                    }
                }else{
                    $fkName.="_id";
                }
                $processedData[$fkName] = $parentId;
            }
            $createBeforeEvent = $model->createBefore($model, $processedData, $this->requestMeta);
            if(isset($createBeforeEvent['errors'])){
                $this->operationOK=false;
                $this->errors = $createBeforeEvent['errors'];
                return;
            }
            $finalData  = $createBeforeEvent["data"];
            if($this->isMultipart){
                $req = $this->originalRequest;
                foreach( array_keys($req->all()) as $keyName){
                    if($req->hasFile($keyName) && in_array($keyName, array_keys($finalData)) ){
                        $validator = Validator::make($req->all(), [
                            $keyName => 'max:25000|mimes:pdf,doc,docx,xls,xlsx,odt,odf,zip,tar,tar.xz,tar.gz,rar,jpg,jpeg,png,bmp,mp4,mp3,mpg,mpeg,mkv,3gp'
                        ]);
                        if ( $validator->fails()) {
                            foreach($validator->errors()->all() as $error){
                                $this->errors[] = "[INVALID]".$error."[$modelName]";
                            }
                            $this->operationOK=false;
                            return false;
                        }                    
                        $code= Carbon::now()->format('his');
                        $fileName = sanitizeString($req->$keyName->getClientOriginalName());
                        Storage::disk('uploads')->putFileAs(
                            $modelName, $req->$keyName, $code."_".$fileName
                        );
                        $finalData[$keyName] = url("/uploads/$modelName/".$code."_".$fileName);
                    }
                }
            }
            $finalModel = ($this->getParentClass($model))->create(reformatData($finalData));
            $model->createAfter($finalModel, $processedData, $this->requestMeta, $finalModel->id);
            $this->operationId=$finalModel->id;
            $this->success[] = "SUCCESS: data created in ".$model->getTable()." new id: $finalModel->id";
            foreach( $data as $key => $value ){
                if(is_array($value) && count($value)>0 && in_array($key, $detailsArray) ){                
                    $this->createOperation($key, $value, $finalModel->id, $model->getTable());
                }
            }
        }
        $model = null;   
    }
    public function readOperation( $modelName, $params=null, $id=null )
    {
        $params=(array)$params;
        foreach($params as $key => $param){
            if(is_array($param)){
                continue;
            }
            if( str_replace(["null","NULL"," "],["","",""],$param)==""){
                $params[$key] = null;
            }
        }
        $params=(object)$params;
        $modelCandidate = "\App\Models\CustomModels\\$modelName";
        $model          = new $modelCandidate;
        $details       = $model->details;
        $data = (object)$params;
        $p = (Object)[];
        if($id!=null){
            $p->selectfield = isset($data->selectfield) ? $data->selectfield:null;
            $p->join        = isset($data->join) ? ($data->join=="false"?false:true):true;
            $p->single      = isset($data->single) ? ($data->single=="false"?false:true):false;
            $p->id          = $id;
            $p->joinMax        = isset($data->joinMax) ? $data->joinMax:0;
            $overrideParams = $model->overrideGetParams($p,$id);
            return [
                "data"=>$model->customFind($overrideParams),
                "meta"=>config('tables'),
                "metaScript"=>method_exists( $model, "metaScript" )?$model->metaScript():null
            ];
        }else{
            $p->where_raw   = isset($data->where) ? $data->where : null;
            $p->order_by    = isset($data->orderby) ? $data->orderby:$model->getTable().".updated_at";
            $p->order_type  = isset($data->ordertype) ? $data->ordertype:"DESC";
            $p->addselect  = isset($data->addselect) ? urldecode($data->addselect):null;
            $p->union  = isset($data->union) ? urldecode($data->union):null;
            $p->order_by_raw= isset($data->orderbyraw) ? $data->orderbyraw:null;
            $p->search      = isset($data->search) ? $data->search:null;
            $p->searchfield = isset($data->searchfield) ? $data->searchfield:null;
            $p->selectfield = isset($data->selectfield) ? urldecode($data->selectfield):null;
            $p->paginate    = isset($data->paginate) ? $data->paginate:25;
            $p->page        = isset($data->page) ? $data->page:1;
            $p->group_by    = isset($data->group_by) ? $data->group_by:null;
            $p->joinMax      = isset($data->joinMax) ? $data->joinMax:0;
            $p->join        = isset($data->join) ? ($data->join=="false"?false:true):true;
            $p->caller      = null;
            $overrideParams = $model->overrideGetParams($p);
            return $model->customGet($overrideParams);
        }
    }
    private function deleteOperation( $modelName, $params=null, $id=null, $fk=null )
    {
        $modelCandidate = "\App\Models\CustomModels\\$modelName";
        $model          = new $modelCandidate;
        $detailsArray   = $model->details; 
        $cascade        = $model->cascade;
        $preparedModel  = $model->find($id);
        if(!$preparedModel){
            $this->errors[]="[NOT FOUND]ID $id in model [$modelName] does not exist";
            $this->operationOK=false;
            return;
        }
        $deleteBeforeEvent = $model->deleteBefore($model, $preparedModel, $this->requestMeta, $id);        
        if(isset($deleteBeforeEvent['errors'])){
            $this->operationOK=false;
            $this->errors = $deleteBeforeEvent['errors'];
            return;
        }
        $preparedModel->delete(); 
        $model->deleteAfter($model, $preparedModel, $this->requestMeta, $id);
        $this->success[] = "SUCCESS: data deleted in ".$model->getTable()." id: $id";
        if($cascade){
            foreach( $detailsArray as $detail ){
                $modelCandidate = "\App\Models\CustomModels\\$detail";
                $model          = new $modelCandidate;
                $dataDetail = $model->where($modelName."_id","=",$id)->get();                
                foreach( $dataDetail as $dtl ){
                    $this->deleteOperation($detail, null, $dtl->id, $id);
                }
            }
        }
        $model = null;
    }
    private function updateOperation($modelName, $data=null, $id=null)
    {
        if(!$this->operationOK){return;}
        $modelCandidate = "\App\Models\CustomModels\\$modelName";
        $model          = new $modelCandidate;
        $detailsArray   = $model->details; 
        $cascade        = $model->cascade;
        $preparedModel  = (new $modelCandidate)->find($id);
        if(!$preparedModel){
            $this->errors[]="[NOT FOUND]ID $id in model [$modelName] does not exist";
            $this->operationOK=false;
            return;
        }
        $additionalData = $this->createAdditionalData($model, $data);
        $eliminatedData = $this->createEliminationData($model, $data);
        $processedData  = array_merge($eliminatedData, $additionalData);
        $updateBeforeEvent = $model->updateBefore($model, $processedData, $this->requestMeta);
        if(isset($updateBeforeEvent['errors'])){
            $this->operationOK=false;
            $this->errors = $updateBeforeEvent['errors'];
            return;
        }
        $finalData  = $updateBeforeEvent["data"];
        if($this->isMultipart){
            $req = $this->originalRequest;
            foreach( array_keys($req->all()) as $keyName){
                if($req->hasFile($keyName) && in_array($keyName, array_keys($finalData)) ){
                    $validator = Validator::make($req->all(), [
                        $keyName => 'max:25000|mimes:pdf,doc,docx,xls,xlsx,odt,odf,zip,tar,tar.xz,tar.gz,rar,jpg,jpeg,png,bmp,mp4,mp3,mpg,mpeg,mkv,3gp'
                    ]);
                    if ( $validator->fails()) {
                        foreach($validator->errors()->all() as $error){
                            $this->errors[] = "[INVALID]".$error."[$modelName]";
                        }
                        $this->operationOK=false;
                        return false;
                    }                    
                    $code= Carbon::now()->format('his');
                    $fileName = sanitizeString($req->$keyName->getClientOriginalName());
                    Storage::disk('uploads')->putFileAs(
                        $modelName, $req->$keyName, $code."_".$fileName
                    );
                    $finalData[$keyName] = url("/uploads/$modelName/".$code."_".$fileName);
                }
            }
        }
        $finalModel = $preparedModel->update(reformatData($finalData));
        $model->updateAfter($finalModel, $processedData, $this->requestMeta, $id);
        $this->success[] = "SUCCESS: data update in ".$model->getTable()." id: $id";
        
        foreach( $detailsArray as $detail ){
            if( !in_array($detail,array_keys($data) )){
                continue;
            }

            $modelCandidate = "\App\Models\CustomModels\\$detail";
            $modelChild = new $modelCandidate;
            
            $detailIds = [];
            $detailNew = [];
            $detailOld = [];
            foreach($data[$detail] as $index => $valDetail){
                if(isset($valDetail['id']) && is_numeric($valDetail['id']) && (new $modelCandidate)->where('id',$valDetail['id'])->count()>0){
                    $this->updateOperation($detail, $valDetail, $valDetail['id']);
                    $detailIds[]=$valDetail['id'];
                    $detailOld [] = $valDetail;
                }else{
                    $detailNew [] = $valDetail;
                }
            };
            
            $columns    = $modelChild->columns;
            $fkName     = $model->getTable();
            if(!in_array($fkName."_id",$columns)){
                $realJoins = $modelChild->joins;
                foreach($realJoins as $val){
                    $valArray = explode("=",$val);
                    if($valArray[0]==$fkName.".id"){
                        $fkName = $valArray[1];
                        break;
                    }
                }
            }else{
                $fkName.="_id";
            }
            $dataDetail = $modelChild->where($fkName,$id)->whereNotIn('id',$detailIds)->get();                
            foreach( $dataDetail as $dtl ){
                $this->deleteOperation($detail, null, $dtl->id, $id);
            }
            if( count($detailNew)>0){
                $this->createOperation($detail, $detailNew, $id, $model->getTable());
            }
            // foreach($detailOld as $oldDetail){
            //     $this->updateOperation($detail, $oldDetail, $oldDetail['id']);
            // }
        }
        // foreach( $data as $key => $value ){
        //     if(is_array($value) && count($value)>0 && in_array($key, $detailsArray) ){
        //         $detailIds = [];
        //         foreach($data[$key] as $valDetail){
        //             if(isset($valDetail['id']) && is_numeric($valDetail['id'])){
        //                 $detailIds[]=$valDetail['id'];
        //             }
        //         };
        //         $this->createOperation($key, $value, $id, $model->getTable());
        //     }
        // }
        $model = null;  
    }
    public function router(Request $request, $modelname, $id=null)
    {
        if($this->isAuthorized){
            if($this->customOperation){
                $modelCandidate = "\App\Models\CustomModels\\$this->parentModelName";
                $function = "custom_".$this->operationId;
                $functionName = $this->operationId;
                $model = new $modelCandidate;                
                if( !method_exists( $model, $function ) ){
                    $this->messages[] ="[UNKNOWN] function [$functionName] in Model [$this->parentModelName] does not exist";
                    return response()->json(["messages"=>$this->messages],400);
                }
                $result = $model->$function($this->originalRequest);
                return $result;
            }
            if($this->operation=='read'){
                return $this->readOperation($modelname,$this->requestData,$id);
            }
            DB::beginTransaction();
            try{
                $modelCandidate = "\App\Models\CustomModels\\$modelname";
                $model = new $modelCandidate;
                
                if( (isset($model->transaction_config) || method_exists($model, $this->operation."AfterTransaction")) && $this->operationId!==null){
                    
                    $oldData = $this->readOperation( $this->parentModelName, (object)[], $this->operationId )['data'];
                }
                $function = $this->operation."Operation";
                $this->$function($this->parentModelName,$this->requestData, $id);
                if(!$this->operationOK){
                    return response()->json([
                        "status"    => "$this->operation data failed",
                        "warning"  => $this->messages, 
                        "success"  => $this->success, 
                        "errors"  => $this->errors, 
                        "request" => $this->requestData,
                        "id"      => $this->operationId
                    ],400);
                }
            }catch(Exception $e){
                DB::rollback();
                return response()->json([
                    "status"    => "$this->operation data gagal", 
                    "warning"  => $this->messages, 
                    "success"  => $this->success, 
                    "errors"    => ["error"=>$e->getMessage(),"line"=>$e->getLine(),"file"=>$e->getFile()],
                    "request" => $this->requestData,
                    "id"        => $this->operationId
                ],400);
            }
            if($this->operationOK){
                if(method_exists($model, $this->operation."AfterTransaction")){
                    $newData = $this->readOperation( $this->parentModelName, (object)[], $this->operationId )['data'];
                    $newfunction = $this->operation."AfterTransaction";
                    $model->$newfunction( 
                        $newData,
                        isset($oldData)?$oldData:[], 
                        $this->requestData,
                        $this->requestMeta
                    );
                }
                if( isset($model->transaction_config) ){
                    try{
                        if(!isset($newData)){
                            if($this->operation=='delete'){
                                $newData = $oldData;
                            }else{
                                $newData = $this->readOperation( $this->parentModelName, (object)[], $this->operationId )['data'];
                            }
                        }
                        $config = (object)$model->transaction_config;
                        $isMultiple = $config->current_pivot_header_multiple;
                        // $details = $newData[$config->current_detail_table];
                        if(!$isMultiple){
                            $beforeTransactionId = $newData[$config->current_pivot_header_column];
                            $beforeTransactionData = $this->readOperation( $config->before_transaction_table, (object)[], $beforeTransactionId )['data'];
                            $beforeTransactionDetails = $beforeTransactionData[$config->before_transaction_detail_table];
                            $lolos = true;
                            foreach($beforeTransactionDetails as $i => $dtl){
                                $detail_id  = $dtl['id'];
                                $detail_qty = $dtl[$config->before_transaction_detail_column_qty];
                                if(\DB::table($config->current_detail_table)->where($config->current_pivot_detail_column,$detail_id)->count()>0){
                                    $sum = \DB::table($config->current_detail_table)
                                            ->selectRaw("SUM($config->current_detail_column_qty) as qtysum")
                                            ->where($config->current_pivot_detail_column,$detail_id)
                                            ->first();
                                    if($sum->qtysum<$detail_qty){
                                        $lolos = false;
                                        break;
                                    };
                                }else{
                                    $lolos = false;
                                    break;
                                }
                            }
                            \DB::table($config->before_transaction_table)
                                ->where("id", $beforeTransactionId)->update([
                                    $config->before_transaction_column_status => $lolos?$config->status_close:$config->status_open
                                ]);
                        }else{
                            $arrayBeforeTransactionIds = [];
                            $currentDetails = $newData[$config->current_detail_table];
                            foreach($currentDetails as $dtl){
                                if(!in_array($dtl[$config->current_pivot_header_column],$arrayBeforeTransactionIds)){
                                    $arrayBeforeTransactionIds[]=$dtl[$config->current_pivot_header_column];
                                }
                            }
                            foreach($arrayBeforeTransactionIds as $beforeTransactionId){
                                $beforeTransactionData = $this->readOperation( $config->before_transaction_table, (object)[], $beforeTransactionId )['data'];
                                $beforeTransactionDetails = $beforeTransactionData[$config->before_transaction_detail_table];
                                $lolos = true;
                                foreach($beforeTransactionDetails as $i => $dtl){
                                    $detail_id  = $dtl['id'];
                                    $detail_qty = $dtl[$config->before_transaction_detail_column_qty];
                                    if(\DB::table($config->current_detail_table)->where($config->current_pivot_detail_column,$detail_id)->count()>0){
                                        $sum = \DB::table($config->current_detail_table)
                                                ->selectRaw("SUM($config->current_detail_column_qty) as qtysum")
                                                ->where($config->current_pivot_detail_column,$detail_id)
                                                ->first();
                                        if($sum->qtysum<$detail_qty){
                                            $lolos = false;
                                            break;
                                        };
                                    }else{
                                        $lolos = false;
                                        break;
                                    }
                                }
                                \DB::table($config->before_transaction_table)
                                    ->where("id", $beforeTransactionId)->update([
                                        $config->before_transaction_column_status => $lolos?$config->status_close:$config->status_open
                                    ]);
                            }
                        }
                        
                    }catch(\Exception $e){}
                }

                DB::commit();
                return response()->json([
                    "status"    => "$this->operation data berhasil", 
                    "warning"  => $this->messages, 
                    "success"  => $this->success, 
                    "errors"  => $this->errors,
                    "request" => $this->requestData,
                    "id"        => $this->operationId
                ],200);
            }else{
                DB::rollback();
                return response()->json([
                    "status"    => "$this->operation data gagal",
                    "warning"  => $this->messages, 
                    "success"  => $this->success, 
                    "errors"  => $this->errors, 
                    "request" => $this->requestData,
                    "id"      => $this->operationId
                ],422);
            }
        }else{
            return response()->json([
                "status"    => "$this->operation data failed",
                "warning"  => $this->messages, 
                "success"  => $this->success, 
                "errors"  => $this->errors, 
                "request" => $this->requestData,
                "id"        => $this->operationId
            ],422);
        }
    }
}
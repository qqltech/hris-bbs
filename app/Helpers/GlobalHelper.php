<?php

use PhpOption\Option;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Optional;
use Illuminate\Support\Collection;
use Dotenv\Environment\DotenvFactory;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HigherOrderTapProxy;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

if (! function_exists('table_config')) {
    function table_config($table, $array)
    {
        $string = json_encode($array);
        if(getDriver()=='mysql'){
            Schema::getConnection()->statement("ALTER TABLE $table comment = '$string'");
        }elseif(getDriver()=='pgsql'){
            Schema::getConnection()->statement("COMMENT ON TABLE $table IS '$string'");
        }
    }
}

if (! function_exists('createMany')) {
    function createMany($table, $array)
    {
        
    }
}
function _joinRecursive($joinMax,&$kembar,&$fieldSelected,&$allColumns,&$joined,&$model,$tableName,$params){
    $tableStringClass = "\App\Models\BasicModels\\$tableName";
    $currentModel = new $tableStringClass;
    
    foreach( $currentModel->joins as $join ){
        $arrayJoins=explode("=",$join);
        $arrayParents=explode(".",$arrayJoins[0]);

        if(count($arrayParents)>2){
            $parent = $arrayParents[1];
            $fullParent = $arrayParents[0].".".$arrayParents[1];
        }else{
            $parent = $arrayParents[0];
            $fullParent=$parent;
        }
        // if(in_array($parent, $joined)){        
        //     continue;
        // }//PENTING
        $onParent = $arrayJoins[0];
        $onMe = $arrayJoins[1];
        $joined[]=$fullParent;
        $parentClassString = "\App\Models\BasicModels\\$parent";

        if( !class_exists($parentClassString) ){
            continue;
        }
        if(isset($params->caller) && $params->caller==$parent){
            continue;                
        }
        if( !isset($kembar[$parent]) ){
            $kembar[$parent] = 1;
        }else{
            $kembar[$parent] = $kembar[$parent]+1;
        }
        
        $parentName = $fullParent;
        if($kembar[$parent]>1){
            $parentName = "$fullParent AS ".$parent.(string)$kembar[$parent];
            $onParentArray=explode(".",$onParent);
            if( count( $onParentArray )>2 ){
                $onParent = $onParentArray[1].".".$onParentArray[2];
            }
            $onParent = str_replace($parent,$parent.(string)$kembar[$parent],$onParent);
        }
        $model = $model->leftJoin($parentName,$onParent,"=",$onMe);
        $parentClass = new $parentClassString;
        if($kembar[$parent]>1){
            $parentName = $parent.(string)$kembar[$parent];
        }
        foreach($parentClass->columns as $column){
            $colTemp        = "$parentName.$column AS ".'"'.$parentName.".".$column.'"';
            $fieldSelected[]= $colTemp;
            $allColumns[]   = "$parentName.$column";
        }
        if($joinMax>1){
            _joinRecursive($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
        }
    }
    
}
function _joinRecursiveAlias($joinMax,&$kembar,&$fieldSelected,&$allColumns,&$joined,&$model,$tableName,$params){
    $tableStringClass = "\App\Models\BasicModels\\$tableName";
    $currentModel = new $tableStringClass;
    
    foreach( $currentModel->joins as $join ){
        $arrayJoins=explode("=",$join);
        $arrayParents=explode(".",$arrayJoins[0]);

        if(count($arrayParents)>2){
            $parent = $arrayParents[1];
            $fullParent = $arrayParents[0].".".$arrayParents[1];
        }else{
            $parent = $arrayParents[0];
            $fullParent=$parent;
        }
        $onParent = $arrayJoins[0];
        $onMe = $arrayJoins[1];
        $joined[]=$fullParent;
        $parentClassString = "\App\Models\BasicModels\\$parent";

        $meArr = explode( ".", $onMe );
        $aliasParent = str_replace('_id', env('SUFFIX_PARENT_TABLE',''), end( $meArr ));

        if( !class_exists($parentClassString) ){
            continue;
        }
        if(isset($params->caller) && $params->caller==$parent){
            continue;                
        }
              
        $parentName = "$fullParent AS $aliasParent";
        $onParent = str_replace($fullParent,$aliasParent,$onParent);
            
        $model = $model->leftJoin($parentName, $onParent, "=", $onMe);
        $parentClass = new $parentClassString;

        foreach($parentClass->columns as $column){
            $colTemp        = "$aliasParent.$column AS ".'"'.$aliasParent."_".$column.'"';
            $fieldSelected[]= $colTemp;
            $allColumns[]   = "$aliasParent.$column";
        }
        
        if($joinMax>1){
            _joinRecursiveAlias($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
        }
    }
    
}
function _customGetData($model,$params)
{
    $table = $model->getTable();
    $className = class_basename( $model );
    
    $givenScopes = [];
    if($table == config( "parentTable") && req('scopes')){
        $scopes = explode(",", req('scopes'));
        foreach( $scopes as $scope ){
            if( !$model->hasNamedScope($scope) ){
                abort(422,json_encode([
                    'message'=>"Scope $scope tidak ditemukan",
                    "resource"=>$className
                ]));
            }
        }
        $givenScopes = $scopes;
    }

    $isParent = $className == (@app()->request->route()[2]['detailmodelname'] || @app()->request->route()[2]['modelname']);
    $joinMax = isset($params->joinMax)?$params->joinMax:0;
    $pureModel=$model;    
    $modelCandidate = "\\".get_class($model);
    // $modelCandidate = "\App\Models\CustomModels\\$table";
    $modelExtender  = new $modelCandidate;
    $fieldSelected=[];
    $metaColumns = [];
    foreach($model->columns as $column){
        $fieldSelected[] = "$table.$column";
        $metaColumns[$column] = "frontend";
    }
    $allColumns = $fieldSelected;
    $kembar = [];
    $joined = [];
    $enableJoin = $params->join;
    if( $isParent ){
        $enableJoin = req('join') ?? $params->join;
    }else{
        $enableJoin = req($className."_join") ?? true;
    }
    $enableJoin = is_bool($enableJoin) ? $enableJoin : (strtolower($enableJoin) === 'false' ? false : true);
    if( $enableJoin ){
        foreach( $model->joins as $join ){
            $arrayJoins=explode("=",$join);
            $arrayParents=explode(".",$arrayJoins[0]);

            if(count($arrayParents)>2){
                $parent = $arrayParents[1];
                $fullParent = $arrayParents[0].".".$arrayParents[1];
            }else{
                $parent = $arrayParents[0];
                $fullParent=$parent;
            }
            
            $joined[]=$parent;
            $onParent = $arrayJoins[0];
            $onMe = $arrayJoins[1];
            $parentClassString = "\App\Models\BasicModels\\$parent";
            
            $meArr = explode( ".", $onMe );
            $aliasParent = str_replace('_id', env('SUFFIX_PARENT_TABLE',''), end( $meArr ));

            if( !class_exists($parentClassString) ){
                continue;
            }

            if($params->caller && $params->caller==$parent){
                continue;                
            }

            if(req('api_version')!=2){
                if( !isset($kembar[$parent]) ){
                    $kembar[$parent] = 1;
                }else{
                    $kembar[$parent] = $kembar[$parent]+1;
                }
            }

            $parentName = $fullParent;
            if(req('api_version')!=2 && $kembar[$parent]>1){
                $parentName = "$fullParent AS ".$parent.(string)$kembar[$parent];
                // $onParent = str_replace($parent,"tes".$parent.(string)$kembar[$parent],$onParent); //OLD CODE
                $onParentArray=explode(".",$onParent);
                if( count( $onParentArray )>2 ){
                    $onParent = $onParentArray[1].".".$onParentArray[2];
                }
                $onParent = str_replace($parent,$parent.(string)$kembar[$parent],$onParent);
            }

            if(req('api_version')==2){
                $parentName = "$fullParent AS $aliasParent";
                $onParent = str_replace($fullParent,$aliasParent,$onParent);
            }

            $model = $model->leftJoin($parentName, $onParent, "=", $onMe);
            $parentClass = new $parentClassString;
            if( req('api_version') !=2 && $kembar[$parent]>1 ){
                $parentName = $parent.(string)$kembar[$parent];
            }
            foreach($parentClass->columns as $column){
                if( req('api_version')==2 ){
                    $colTemp = "$aliasParent.$column AS ".'"'.$aliasParent.".".$column.'"';
                }else{
                    $colTemp = "$parentName.$column AS ".'"'.$parentName.".".$column.'"';
                }

                $fieldSelected[]= $colTemp;
                $allColumns[]   = "$parentName.$column";
            }
            
            if($joinMax>0){
                if(req('api_version')==2){
                    _joinRecursiveAlias($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
                }else{
                    _joinRecursive($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
                }
            }
        }
    }
    if($params->selectfield || ($isParent && req('selectfield')) || req($className."_selectfield")){
        $rawSelectFields = req($className."_selectfield") ?? req('selectfield') ?? $params->selectfield;
        $selectFields = str_replace(["this.","\n","  ","\t"],["$table.","","",""], $rawSelectFields);
        $selectFields = explode(",", $selectFields);
        $fieldSelected= $selectFields;
    }
    
    if( isset($params->addSelect) && $params->addSelect!=null ){
        $addSelect = str_replace("this.","$table.",strtolower($params->addSelect));
        $fieldSelected = array_merge( $fieldSelected, explode(",",$addSelect));
    }
    
    if( $params->addJoin || ($isParent && req('addjoin')) || req($className."_addjoin") ){
        $addJoin = req($className."_addjoin") ?? req('addjoin') ?? $params->addJoin;            
        $joiningString = str_replace("this.","$table.",strtolower($addJoin));
        $joins = explode( ",", $joiningString );
        foreach($joins as $join){
            $join = strtolower($join);
            if(strpos( $join, " and ")!==FALSE){
                $join = explode(" and ",$join);
                $joinedTable=explode(".",$join[0])[0];
                $model = $model->leftJoin($joinedTable, function($q)use($join){
                    foreach($join as $statement){
                        $statement = str_replace(" ","",$statement);
                        $explodes = explode(".",$statement);
                        if( count($explodes)>2 ){
                            $parent = "{$explodes[0]}.{$explodes[1]}";
                        }else{
                            $parent = $explodes[0];
                        }
                        $onParent = explode("=",$statement)[0];
                        $onMe = explode("=",$statement)[1];
                        $q->on($onParent,"=",$onMe);
                    }
                });
            }else{
                $candParent = explode("=", $join)[0];
                $explodes = explode(".", $candParent);
                if( count($explodes)>2 ){
                    $parent = $explodes[0].".".$explodes[1];
                }else{
                    $parent = $explodes[0];
                }
                $onParent = explode("=",$join)[0];
                $onMe = explode("=",$join)[1];
                $model = $model->leftJoin($parent,$onParent,"=",$onMe);
            }
        }
    }
    
    if(method_exists($modelExtender, "extendJoin")){
        $model = $modelExtender->extendJoin($model);
    }
    /**
     * Filter direct params misal this.column:21
     */
    $requestDataArr = (array)req();
    $directFilter = [];
    foreach($requestDataArr as $key => $val){
        if(Str::startsWith($key, "this_") || Str::startsWith($key, "this.")){
            $directFilter[]=$key;
            $model = $model->where(str_replace(["this_","this."],["$table.", "$table."],$key ), $val);
        }
    }
    if($params->where_raw){
        $model = $model->whereRaw(str_replace("this.","$table.",urldecode( $params->where_raw) ) );
    }

    if( isRoute('read_list_detail') ){
        $parentModelName = @app()->request->route()[2]['modelname'];
        $parentModel = getCustom($parentModelName);
        $parentTable = getTableOnly($parentModel->getTable());
        $parentId = @app()->request->route()[2]['id'];
        if($parentModel->useEncryption){
            $parentId = $parentModel->decrypt($parentId);
        }

        $model = $model->where(function($q)use( $parentTable, $parentId ){
            $q->where( $parentTable."_id", $parentId );
        });
    }

    if( isRoute('read_list_sub_detail') ){
        $parentModelName = @app()->request->route()[2]['detailmodelname'];
        $parentModel = getCustom($parentModelName);
        $parentTable = getTableOnly($parentModel->getTable());
        $parentId = @app()->request->route()[2]['detailid'];
        if($parentModel->useEncryption){
            $parentId = $parentModel->decrypt($parentId);
        }

        $model = $model->where(function($q)use( $parentTable, $parentId ){
            $q->where( $parentTable."_id", $parentId );
        });
    }

    if(  req("notin") && strpos(req("notin"),":")!==false ){
        $givenScopes[] = 'notin';
    }
    
    if( $isParent ){
        $givenScopes[] = 'filters';
        $givenScopes[] = 'directFilters';
        
        if(req('whereNull'))  $givenScopes[] = 'null';
        if(req('orWhereNull'))  $givenScopes[] = 'orNull';
        if(req('whereNotNull'))  $givenScopes[] = 'notNull';
        if(req('orWhereNotNull'))  $givenScopes[] = 'orNotNull';
    }
    
    if( req("query_name") && req('query_name')!=='null' && !app()->request->route('id')){
        $givenScopes[] = 'queryParam';
    }
    
    if(  req("orin") && strpos(req("orin"),":")!==false ){
        $givenScopes[] = 'orin';
    }

    if( req('search') && req('search')!=='null' ){
        $model=$model->search( $allColumns );
    }

    if(isset($params->group_by) && $params->group_by!=null){
        $model = $model->groupBy( DB::raw(str_replace("this.", "$table.", urldecode($params->group_by) )) );
    }
    
    if($params->order_by_raw){
        $model = $model->orderByRaw( str_replace("this.","$table.",urldecode($params->order_by_raw) ) );
    }elseif($params->order_by){
        $order =  str_replace("this.","$table.", $params->order_by);
        if( method_exists( $modelExtender, 'aliases') ){
            $aliases = $modelExtender->aliases();
            if(is_array($aliases)){
                $key = array_search( $order,$aliases ) ;
                if( $key ){
                    $order = $key;
                }
            }
        }
       $model=$model->orderBy(DB::raw($order),$params->order_type==null?"asc":$params->order_type);
    }
    $final  = $model->select(DB::raw(implode(",",$fieldSelected) ));
    
    $finalObj = (object)[
        'type'=>'get', 'caller'=>$params->caller
    ];

    if(!$params->caller){
       $data = $final->scopes($givenScopes)->final($finalObj);
       if(req('simplest')){
           $data = $data->simplePaginate($params->paginate,["*"], 'page', $page = $params->page);
       }else{
            $data = $data->paginate($params->paginate,["*"], 'page', $page = $params->page);
       }
    }else{
       $data = $final->scopes($givenScopes)->final($finalObj)->get(); 
    }
    if( req("transform")==='false' ){
        if(!$params->caller){
            $addData = collect(['processed_time' => round(microtime(true)-config("start_time"),5)]);
            $data = $addData->merge($data);
        }
        return $data;
    }
    if($params->caller){
        $tempData=$data->toArray();
        $fixedData=[];
        $index=0;
        foreach($tempData as $i => $row){
            $keys=array_keys($row);
            foreach($keys as $key){
                if( count(explode(".", $key))>2 ){
                    $newKeyArray = explode(".", $key);
                    $newKey = $newKeyArray[1].".".$newKeyArray[2];
                    $tempData[$i][$newKey] = $tempData[$i][$key];
                    unset($tempData[$i][$key]);
                }
            }
        }
        foreach($tempData as $row){
            $transformedData = reformatDataResponse($row);
            if(method_exists($modelExtender, "transformRowData")){
                $transformedData = $modelExtender->transformRowData(reformatDataResponse($row));
                if( gettype($transformedData)=='boolean' ){
                    continue;
                }
            }

            $fixedData[$index] = $transformedData;
            foreach(["create","update","delete","read"] as $akses){
                $func = $akses."roleCheck";
                if( method_exists( $modelExtender, $func) ){
                    $fixedData[$index] = array_merge( ["meta_$akses"=>in_array( $akses, ['create','list'] ) ? $modelExtender->$func() : $modelExtender->$func( $row['id'] )], $fixedData[$index]);
                }
            }

            if($pureModel->useEncryption){
                $currentId = $pureModel->decrypt($fixedData[$index]['id']);
            }else{
                $currentId = $fixedData[$index]['id'];
            }

            foreach($pureModel->details as $detail){
                $detailArray = explode(".",$detail);
                $detailClass = $detail;
                if( count($detailArray)>1 ){
                    $detailClass = $detailArray[1];
                }          
                // $modelCandidate = "\App\Models\CustomModels\\$detailClass";
                // $model      = new $modelCandidate;
                $model      = getCustom($detailClass);
                $details    = $model->details;
                $columns    = $model->columns;
                $fkName     = $pureModel->getTable();
                if(!in_array($fkName."_id",$columns)){
                    $realJoins = $model->joins;
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
                $p = (Object)[];
                $p->where_raw   = $fkName."=".$currentId;
                $p->order_by    = null;
                $p->order_type  = null;
                $p->order_by_raw= null;
                $p->search      = null;
                $p->searchfield = null;
                $p->selectfield = null;
                $p->paginate    = null;
                $p->page        = null;
                $p->addSelect   = null;
                $p->addJoin     = null;
                $p->join        = true;
                $p->joinMax     = 0;
                $p->group_by    = null;
                $p = $model->overrideGetParams($p,null);
                $p->caller      = $pureModel->getTable();
                $detailArray = explode('.', $detail);
                $fixedData[$index][ count($detailArray)==1? $detail : $detailArray[1] ]  = $model->customGet($p);
            }
            $index++;
        }
        $func="transformArrayData";
        if( method_exists( $modelExtender, $func )  ){
            $newFixedData = $modelExtender->$func( $fixedData );
            $fixedData = gettype($newFixedData)=='array' ? $newFixedData : $fixedData;
        }
        $data   = $fixedData;
    }else{
        $tempData = $data->toArray()["data"];
        $fixedData=[];
        $index=0;        
        foreach($tempData as $i => $row){
            $keys=array_keys($row);
            foreach($keys as $key){
                if( count(explode(".", $key))>2 ){
                    $newKeyArray = explode(".", $key);
                    $newKey = $newKeyArray[1].".".$newKeyArray[2];
                    $tempData[$i][$newKey] = $tempData[$i][$key];
                    unset($tempData[$i][$key]);
                }
            }
        }
        foreach($tempData as $row){
            $transformedData = reformatDataResponse($row);
            if(method_exists($modelExtender, "transformRowData")){
                $transformedData = $modelExtender->transformRowData(reformatDataResponse($row));
                if( gettype($transformedData)=='boolean' ){
                    continue;
                }
            }

            $fixedData[$index] = $transformedData;
            foreach(["create","update","delete","read"] as $akses){
                $func = $akses."roleCheck";
                if( method_exists( $modelExtender, $func) ){
                    $fixedData[$index] = array_merge( ["meta_$akses"=>in_array( $akses, ['create','list'] ) ? $modelExtender->$func() : $modelExtender->$func( $row['id'] )], $fixedData[$index]);
                }
            }
            $index++;
        }
        $func="transformArrayData";
        if( method_exists( $modelExtender, $func )  ){
            $newFixedData = $modelExtender->$func( $fixedData );
            $fixedData = gettype($newFixedData)=='array' ? $newFixedData : $fixedData;
        }
        $data = array_merge([
            "data"=>$fixedData
        ],[
            // "metaScript"=>method_exists( $modelExtender, "metaScriptList" )?$modelExtender->metaScriptList():null,
            "total"=>req('simplest')?null: $data->total(),
            "current_page"=>$data->currentPage(),
            "per_page"=>$data->perPage(),
            "from"=>$data->firstItem(),
            "to"=>$data->lastItem(),
            "last_page"=>req('simplest')?null:$data->lastPage(),
            "has_next"=>$data->hasMorePages(),
            "prev"=>$data->previousPageUrl(),
            "next"=>$data->nextPageUrl(),
            "processed_time"=>round(microtime(true)-config("start_time"),5)
        ]);
    }
    return $data;
}

function _customFind($model, $params)
{
    $table = $model->getTable();
    $className = class_basename( $model );
    $givenScopes = [];
    if($table == config( "parentTable") && req('scopes')){
        $scopes = explode(",", req('scopes'));
        foreach( $scopes as $scope ){
            if( !$model->hasNamedScope($scope) ){
                abort(422,json_encode([
                    'message'=>"Scope $scope tidak ditemukan",
                    "resource"=>$className
                ]));
            }
        }
        $givenScopes = $scopes;
    }
    $joinMax = isset($params->joinMax)?$params->joinMax:0;
    $pureModel = $model;
    $modelCandidate = "\\".get_class($model);
    // $modelCandidate = "\App\Models\CustomModels\\$table";
    $idToFind = $pureModel->useEncryption ? $pureModel->decrypt($params->id) : $params->id;
    $modelExtender  = new $modelCandidate;
    $fieldSelected=[];
    $metaColumns=[];
    foreach($model->columns as $column){
        $fieldSelected[] = "$table.$column";
        $metaColumns[$column] = "frontend";
    }
    // if(!in_array(class_basename($model),array_keys(config('tables')))){
    //     $func = "metaFields";
    //     if( method_exists( $model, $func) ){
    //         $metaColumns = array_merge( $metaColumns, $model->$func($model->columns) );
    //     }
    //     config(['tables'=>array_merge(config('tables'), [class_basename($model)=>$metaColumns]) ]);
    // }
    $joined=[];
    $allColumns = $fieldSelected;
    if( $params->join ){
        $kembar = [];
        foreach( $model->joins as $join ){
            $arrayJoins=explode("=",$join);
            $arrayParents=explode(".",$arrayJoins[0]);

            if(count($arrayParents)>2){
                $parent = $arrayParents[1];
                $fullParent = $arrayParents[0].".".$arrayParents[1];
            }else{
                $parent = $arrayParents[0];
                $fullParent = $parent;
            }

            $joined[]=$parent;
            $onParent = $arrayJoins[0];
            $onMe = $arrayJoins[1];
            $parentClassString = "\App\Models\BasicModels\\$parent";

            $meArr = explode( ".", $onMe );
            $aliasParent = str_replace('_id', env('SUFFIX_PARENT_TABLE',''), end( $meArr ));

            if( !class_exists($parentClassString) ){
                continue;
            }

            if( !isset($kembar[$parent]) ){
                $kembar[$parent] = 1;
            }else{
                $kembar[$parent] = $kembar[$parent]+1;
            }
            $parentName = $fullParent;
            if(req('api_version')!=2 && $kembar[$parent]>1){
                $parentName = "$fullParent AS ".$parent.(string)$kembar[$parent];
                $onParentArray=explode(".",$onParent);
                if( count( $onParentArray )>2 ){
                    $onParent = $onParentArray[1].".".$onParentArray[2];
                }
                $onParent = str_replace($parent,$parent.(string)$kembar[$parent],$onParent);
            }

            if(req('api_version')==2){
                $parentName = "$fullParent AS $aliasParent";
                $onParent = str_replace($fullParent,$aliasParent,$onParent);
                // trigger_error(json_encode([$parentName,$onParent,$onMe]));
            }

            $model = $model->leftJoin($parentName,$onParent,"=",$onMe);
            $parentClass = new $parentClassString;
            if(req('api_version') !=2 && $kembar[$parent]>1){
                $parentName = $parent.(string)$kembar[$parent];
            }
            foreach($parentClass->columns as $column){
                if( req('api_version')==2 ){
                    $colTemp = "$aliasParent.$column AS ".'"'.$aliasParent.".".$column.'"';
                }else{
                    $colTemp = "$parentName.$column AS ".'"'.$parentName.".".$column.'"';
                }
                $fieldSelected[]= $colTemp;
                $allColumns[]   = "$parentName.$column";
            }
        }
        if($joinMax>0){
            if(req('api_version')==2){
                _joinRecursiveAlias($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
            }else{
                _joinRecursive($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
            }
        }
    }
    if($params->selectfield || req('selectfield')){
        $rawSelectFields = req('selectfield') ?? $params->selectfield;
        $selectFields = str_replace(["this.","\n","  ","\t"],["$table.","","",""], $rawSelectFields);
        $selectFields = explode(",", $selectFields);
        $fieldSelected= $selectFields;
    }
    
    if( isset($params->addSelect) && $params->addSelect!=null ){
        $addSelect = str_replace("this.","$table.",strtolower($params->addSelect));
        $fieldSelected = array_merge( $fieldSelected, explode(",",$addSelect));
    }
    
    if( $params->addJoin || req('addjoin') ){
        $addJoin = req('addjoin') ?? $params->addJoin;
        $joiningString = str_replace("this.","$table.",strtolower($addJoin));
        $joins = explode( ",", $joiningString );
        foreach($joins as $join){
            if(strpos( $join, " and ")!==FALSE){
                $join = explode(" and ",$join);
                $joinedTable=explode(".",$join[0])[0];
                $model = $model->leftJoin($joinedTable, function($q)use($join){
                    foreach($join as $statement){
                        $statement = str_replace(" ","",$statement);
                        $explodes = explode(".",$statement);
                        if( count($explodes)>2 ){
                            $parent = "{$explodes[0]}.{$explodes[1]}";
                        }else{
                            $parent = $explodes[0];
                        }
                        $onParent = explode("=",$statement)[0];
                        $onMe = explode("=",$statement)[1];
                        $q->on($onParent,"=",$onMe);
                    }
                });
            }else{
                $candParent = explode("=",$join)[0];
                $explodes = explode(".",$candParent);
                if( count($explodes)>2 ){
                    $parent = $explodes[0].".".$explodes[1];
                }else{
                    $parent = $explodes[0];
                }
                $onParent = explode("=",$join)[0];
                $onMe = explode("=",$join)[1];
                $model = $model->leftJoin($parent,$onParent,"=",$onMe);
            }
        }
    }
    
    if(method_exists($modelExtender, "extendJoin")){
        $model = $modelExtender->extendJoin($model);
    }
    
    if( isRoute('read_list_detail') ){
        $parentModelName = @app()->request->route()[2]['modelname'];
        $parentModel = getCustom($parentModelName);
        $parentTable = getTableOnly($parentModel->getTable());
        $parentId = @app()->request->route()[2]['id'];
        if($parentModel->useEncryption){
            $parentId = $parentModel->decrypt($parentId);
        }

        $model = $model->where(function($q)use( $parentTable, $parentId ){
            $q->where( $parentTable."_id", $parentId );
        });
    }

    if( isRoute('read_list_sub_detail') ){
        $parentModelName = @app()->request->route()[2]['detailmodelname'];
        $parentModel = getCustom($parentModelName);
        $parentTable = getTableOnly($parentModel->getTable());
        $parentId = @app()->request->route()[2]['detailid'];
        if($parentModel->useEncryption){
            $parentId = $parentModel->decrypt($parentId);
        }

        $model = $model->where(function($q)use( $parentTable, $parentId ){
            $q->where( $parentTable."_id", $parentId );
        });
    }
    
    $finalObj = (object)[
        'type'=>'find', 'caller'=>null
    ];

    $data = $model->scopes($givenScopes)->select(DB::raw(implode(",",$fieldSelected) ))->final($finalObj)->find($idToFind);
    if( !$data ){
        abort(404, json_encode([
            'message'=>"Maaf Data tidak ditemukan"
        ]));
    }
    $data=$data->toArray();
    $keys=array_keys($data);
    foreach($keys as $key){
        if( count(explode(".", $key))>2 ){
            $newKeyArray = explode(".", $key);
            $newKey = $newKeyArray[1].".".$newKeyArray[2];
            $data[$newKey] = $data[$key];
            unset($data[$key]);
        }
    }
    $data = reformatDataResponse($data);
    if(method_exists($modelExtender, "transformRowData") && (!req("transform") || (req("transform") && req("transform")=='true'))){
        $data = $modelExtender->transformRowData($data);
    }
    if($params->single){
        return $data;
    }
    
    $id = $idToFind;
    foreach($pureModel->details as $detail){
        $detailArray = explode(".",$detail);
        $detailClass = $detail;
        if( count($detailArray)>1 ){
            $detailClass = $detailArray[1];
        }
        // $modelCandidate = "\App\Models\CustomModels\\$detailClass";
        // $model          = new $modelCandidate;
        $model      = getCustom($detailClass);
        $fk_child = array_filter($model->joins,function($join)use($pureModel){
            $parentString       = explode("=",$join)[0];
            $parentArray        = explode(".",$parentString);
            $parentNameString   = $parentArray[ 0 ] ;
            if( count( $parentArray )>2 ){
                $parentNameString   = $parentArray[ 0 ].".".$parentArray[ 1 ] ;
            }
            if( $parentNameString == $pureModel->getTable() ){
                return $parentNameString;
            }
        });
        $fk_child = explode( "=",array_values($fk_child) [ 0 ] )[1];
        $p = (Object)[];
        $p->where_raw   = "$fk_child=$id";
        $p->order_by    = null;
        $p->order_type  = null;
        $p->order_by_raw= null;
        $p->search      = null;
        $p->searchfield = null;
        $p->selectfield = null;
        $p->paginate    = null;
        $p->page        = null;
        $p->addSelect   = null;
        $p->addJoin     = null;
        $p->join        = true;
        $p->joinMax     = 0;
        $p->group_by    = null;
        $p = $model->overrideGetParams($p);
        $p->caller      = $pureModel->getTable();
        $detailArray = explode('.', $detail);

        $data[count($detailArray)==1? $detail : $detailArray[1] ]  = $model->customGet($p);
    }
    
    $keys   =   array_keys($data);
    foreach($keys as $key){
        if( count(explode(".", $key))>2 ){
            $newKeyArray = explode(".", $key);
            $newKey = $newKeyArray[1].".".$newKeyArray[2];
            $data[$newKey] = $data[$key];
            unset($data[$key]);
        }
    }
    $func="transformArrayData";
    if( method_exists( $modelExtender, $func )  ){
        $newFixedData = $modelExtender->$func( $data );
        $fixedData = gettype($newFixedData)=='array' ? $newFixedData : $data;
        $data   = $fixedData;
    }
    return $data;
}

function sanitizeString( $string, $force_lowercase = true, $anal = false ) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
                "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                "â€”", "â€“", ",", "<",  ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return $clean;
}
function _uploadexcel($model, $request)
{
    $data = Excel::toArray( null,$request->file);
      	$rows = $data[0];
        $headings = $rows[0];
        $forbiddenHeadings = [];
      	$bulkData = [];
        $invalidRows = [];
        foreach($headings as $col => $heading){
            if( !in_array($heading,$model->columns) ){
                $forbiddenHeadings[] = $heading;
            }
        }
        if(count($forbiddenHeadings)>0){
            return response()->json(["invalid_columns"=>$forbiddenHeadings],400);
        }
        try{
            DB::beginTransaction();
            $hitung=0;
            foreach($rows as $baris => $array){
              if($baris==0){ continue; }
                $row = [];
                foreach($headings as $col => $heading){
                    $row[$heading] = $array[$col];
                }
                array_merge($row,["created_at"=>Carbon::now(),"updated_at"=>Carbon::now() ]);
                $validator = Validator::make($row, $model->importValidator);
                  if ( $validator->fails()) {
                      foreach($validator->errors()->all() as $error){
                          $invalidRows[] = "[INVALID]".$error." in row[$baris]";
                      }
                  }
                $bulkData[]=$row;
                $hitung++;
                if($hitung>999){
                    if( count($invalidRows)>0 ){
                      return response()->json($invalidRows,400);
                    }
                    $hitung=0;                 
                    DB::table($model->getTable())->insert($bulkData);
                    $bulkData=[];
                }
           }
            if( count($invalidRows)>0 ){
              return response()->json($invalidRows,400);
            }
            DB::table($model->getTable())->insert($bulkData);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json([$e->getMessage()],400);
        }
        DB::commit();
      	return response()->json(["status"=>"success","data"=>$bulkData],200);
}
function uploadfile($model, $req, $uniqueName=null, $extension=true ){
    $modelArray = explode("\\",get_class($model));
    $modelName = end($modelArray);
    $validator = Validator::make($req->all(), [
        'file' => 'max:25000|mimes:pdf,doc,docx,xls,xlsx,odt,odf,zip,tar,tar.xz,tar.gz,rar,jpg,jpeg,png,bmp,mp4,mp3,mpg,mpeg,mkv,3gp,ods'
    ]);
    if ( $validator->fails()) {
        return $validator->errors()->all();
    }
    $code= Carbon::now()->format('his').crc32(uniqid());
    if($uniqueName){
        $fileName = $uniqueName.($extension?$req->file->extension():'');
    }else{
        $fileName = $req->filename?$req->filename.($extension?$req->file->extension():''):sanitizeString($req->file->getClientOriginalName());
        $fileName = $code."_".$fileName;
    }
    Storage::disk('uploads')->putFileAs(
        $modelName, $req->file, $fileName
    );
    return url("/uploads/$modelName/".$fileName);
}
function ff($data,$id=""){
    $channel=env("LOG_CHANNEL",env('APP_NAME',uniqid()));
    $client = new \GuzzleHttp\Client();
    $socketServer=env("LOG_SENDER");
    try{
        if(!in_array(gettype($data),["object","array"])){
            $data = [$data];
        }
        $dtrace = (object)debug_backtrace(1,true)[0];
      	// ff($dtrace['class'],$dtrace['function']);
        $data = is_object($data)?array($data):$data;
        $filename = explode("/",$dtrace->file);
        $data = array_merge($data,[ "debug_id"=>$id." [".str_replace(".php","",end($filename))."-$dtrace->line]"]);        
        $client->post(
            "$socketServer/$channel",
            [
                'form_params' => $data
            ]
        );
    }catch(\Exception $e){
        $client->post(
            "$socketServer/$channel",
            [
                'form_params' => ["debug_error"=>$e->getMessage(),"debug_id"=>$id]
            ]
        );
    }
}
function reformatData($arrayData,$model=null){
    $dataKey=["date","tgl","tanggal","_at","etd","eta"];
    $dateFormat = env("FORMAT_DATE_FRONTEND","d/m/Y");
    foreach($arrayData as $key=>$data){
        $datatype=getDataType($model,$key);
        if(is_array($data)){
            continue;
        }
        $isDate=in_array($datatype,['date','datetime','timestamp']);
        if($isDate){
            try{
                $newData = Carbon::createFromFormat($dateFormat, $data)->format('Y-m-d');
                $arrayData[$key] = $newData;   
            }catch(Exception $e){
                
            }
        }elseif( str_replace(["null","NULL"," "],["","",""],$data)==''){
            $arrayData[$key] = null;
        }
    }
    return $arrayData;
}
function reformatDataResponse($arrayData){
    $dataKey=["date","tgl","tanggal","_at","etd","eta"];
    $dateFormat = env("FORMAT_DATE_FRONTEND","d/m/Y");
    foreach($arrayData as $key=>$data){
        $isDate=false;
        foreach($dataKey as $dateString){
            if(strpos(strtolower($key),$dateString)!==false && count(explode("-",$data))>2){
                $isDate=true;
                break;
            }
        }
        if($isDate){
            try{
                $newData = Carbon::createFromFormat("Y-m-d", $data)->format($dateFormat);
                $arrayData[$key] = $newData;
            }catch(Exception $e){}
        }
    }
    return $arrayData;
}

function getReportHeader($model,$params=[]){
    $p = (object)array_merge([
        "where_raw"=>null,
        "order_by"=>null,
        "order_type"=>"ASC",
        "page"=>"1",
        "order_by_raw"=>null,
        "search"=>null,
        "searchfield"=>null,
        "selectfield"=>null,
        "paginate"=>9999,
        "join"=>true,
        "caller"=>null,
        "joinMax"=>3
    ], $params);
    return $model->customGet($p);
}

function js($script){
    return base64_encode(base64_encode($script)); 
}

function str_replace_once($needle, $replace, $haystack) {
    $pos = strpos($haystack, $needle);
    if ($pos === false) {
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}

function getArrayFromString($formula){     
    $arr=[];   	
    $string="";
    foreach(str_split($formula) as $i => $char ){          	
        if( in_array($char,["-","+","/","*"]) ){
            $arr[]=$string;
            $string="";
            $arr[]=$char;
        }else{
            $string.=$char;
        }
    }
    $arr[]=$string;
    return $arr;
};
function testingformula(&$formula){
    $arr=getArrayFromString($formula);
    foreach($arr as $index => $calc){
        if(in_array($calc,["/","*"])){
        if($calc=='/'){
            $hasil=$arr[$index-1]/$arr[$index+1];
            $formula=str_replace_once($arr[$index-1].$arr[$index].$arr[$index+1],$hasil,$formula);
        }elseif($calc=='*'){
            $hasil=$arr[$index-1]*$arr[$index+1];
            $formula=str_replace_once($arr[$index-1].$arr[$index].$arr[$index+1],$hasil,$formula);
        }
    break;
    }
}
    if(strpos($formula,"/")!==false ||  strpos($formula,"*")!==false){
        testingformula($formula);
    }
}
function mathString($formula){
    $formula = str_replace(" ","",$formula);
    testingformula($formula);
    $arr = getArrayFromString($formula);
    $hasil = 0;
    foreach($arr as $index => $calc){
        if($index%2==0){
        if($index==0){
            $hasil=$calc;
        }else{
            if($arr[$index-1]=='-'){
                $hasil-=$calc;
            }elseif($arr[$index-1]=='+'){
                $hasil+=$calc;
            }elseif($arr[$index-1]=='/'){
                $hasil/=$calc;
            }elseif($arr[$index-1]=='*'){
                $hasil*=$calc;
            }
        }          
        }
    }
    return $hasil;
}
function getOutStanding($model, $row,$formula){
    $formula = str_replace(" ","",$formula);
    $arr = getArrayFromString($formula);
    $simpanan=[];
    foreach($arr as $index => $mathString){
        if($index%2==0 && !is_numeric($mathString)){
            $arrString = explode(".",$mathString);
            if(count($arrString)==1){
                $formula = str_replace_once($mathString, $row[$mathString], $formula);
            }else{
                $heirs = $model->heirs;
                $var = "\App\Models\BasicModels\\".$arrString[0];
                $simpanan[$arrString[0]]=[];
                $child = new $var;
                $childJoins = $child->joins;
                $simpanan[$arrString[0]]['heirs']=$child->heirs;
                if(in_array($arrString[0], $heirs)){
                    $whereKey = "";
                    foreach($childJoins as $join){
                        if( strpos($join,$model->getTable().".id") !==false ){
                            $whereKey = explode("=", $join)[1];
                            break;
                        }
                    };
                    $data = $child->selectRaw("sum($arrString[1]) as sumqty")
                            ->where($whereKey, $row['id'])
                            ->first();
                    $sum = $data->sumqty?$data->sumqty:0;
                    $formula = str_replace_once($mathString, $sum, $formula);
                    if($sum>0){
                        $simpanan[$arrString[0]]['data']=$child->select("id")
                            ->where($whereKey, $row['id'])->get()->toArray();
                    }else{
                        $simpanan[$arrString[0]]['data']=[];
                    }
                }else{
                    foreach($simpanan as $key => $keys ){                          
                        if(in_array($arrString[0], $keys['heirs'])){                            
                            $whereKey = "";
                            foreach($childJoins as $join){
                                if( strpos($join,"$key.id") !==false ){
                                    $whereKey = explode("=", $join)[1];
                                    break;
                                }
                            };
                            $ids=[];
                            foreach($keys['data'] as $row){
                                $ids[] = $row["id"];
                            }
                            $data = $child->selectRaw("sum($arrString[1]) as sumqty")
                                    ->whereIn($whereKey, $ids)
                                    ->first();
                            $sum = $data->sumqty?$data->sumqty:0;
                            $formula = str_replace_once($mathString, $sum, $formula);
                            if($sum>0){
                                $simpanan[$arrString[0]]['data']=$child->select("id")
                                    ->whereIn($whereKey, $ids)->get()->toArray();
                            }else{
                                $simpanan[$arrString[0]]['data']=[];
                            }
                            break;                            
                        }
                    }
                }
            }
        }
    }
    return mathString($formula);
};
function getDataType($model,$col){
    $columns = $model->columnsFull;
    foreach($columns as $column){
        $column = explode(":", $column);
        if($column[0]==$col){
            return $column[1];
            break;
        }
    }
    return null;
}
function Api(){
    return new \Api(new Illuminate\Http\Request(),true);
}
function SendEmail($to,$subject,$template){
    try{
        \Mail::to($to)->send(new \MailTemplate($subject, $template ));         
    }catch(\Exception $e){
        return $e->getMessage();
    }
    return true;
}
function SendEmailAsync($to,$subject,$template){
    try{
        Queue::push(new App\Jobs\SendEmail([
            "to"        => $to,
            "subject"   => $subject,
            "template"  => $template
        ]));
    }catch(\Exception $e){
        return $e->getMessage();
    }
    return true;
}
function Async($class,$func,$args){
    dispatch(new \App\Jobs\Background(get_class($class),$func,$args));
}
function getBasic($name){
    if( Str::contains($name, '.') ){
        $nameArr = explode(".", $name);
        $name = end($nameArr);
    }
    $string = "\App\Models\BasicModels\\$name";
    return class_exists( $string )?new $string:null;
}
function getCustom($name){
    if( Str::contains($name, '.') ){
        $nameArr = explode(".", $name);
        $name = end($nameArr);
    }
    if( config("custom_$name") ){
        return config("custom_$name");
    }
    
    $string = "\App\Models\CustomModels\\$name";
    $calledClass = class_exists( $string )?new $string:null;
    if($calledClass){
        config( ["custom_$name" => $calledClass] );
    }
    return $calledClass;
}

function getRoute(){
    return @app()->request->route()[1]['as'];
}
function isRoute($val){
    return @app()->request->route()[1]['as']==$val;
}

function getRawData($query){
    try{        	
        $res = (array)DB::select("$query limit 1")[0];
        return array_values($res)[0];
    }catch(\Exception $e){
        return null;
    }
}

function renderpdf( $config,$arrayData,$pageConfig=[],$type="pdf" ){
    $client = new \GuzzleHttp\Client();    
    $pageConfig = array_merge(["break"=>false,"title"=>"documentpdf","fontsize"=>12,"size"=>"A4","orientation"=>"P","preview"=>false],
    $pageConfig);
    $payLoad = [
        'config'=>$config,
        'data'=>$arrayData,
        'type'=>$type
    ];
    $payLoad = array_merge($payLoad,$pageConfig);
    try{    
        $response = $client->post(
            env('HTMLPDF_RENDERER'),
            [
                'json' => $payLoad,
                'headers' => [
                    'Authorization' => 'Bearer 57aa62501a7fe0d3b71de5712cdb1998',
                    'Accept' => 'application/json',
                ]
            ],
        );
    }catch(\Exception $e){
        return $e->getMessage()." ".$e->getLine();
    }
    return response($response->getBody())
    ->withHeaders([
        'Content-Type' => $type=='html'?'text/html':'application/pdf',//'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',//'text/html',//'application/pdf',
        'Pragma' => 'public',
        'Content-Disposition' => "inline; filename=".$pageConfig['title'].".pdf",//"attachment;filename=$judulsaja.xlsx",//'inline; filename="coba.pdf"'', // 
        'Cache-Control'=>'private, must-revalidate, post-check=0, pre-check=0, max-age=1',
        'Last-Modified'=>gmdate('D, d M Y H:i:s').' GMT',
        'Expires'=>'Mon, 26 Jul 1997 05:00:00 GMT'
    ]);
}

function renderHTML( $config,$arrayData,$pageConfig=["break"=>false,"title"=>"documenthtml","size"=>"A4","orientation"=>"P","preview"=>false] ){
   return renderPDF( $config,$arrayData,$pageConfig,"html" );
}
function renderXLS( $config,$arrayData,$pageConfig=[] ){
    $client = new \GuzzleHttp\Client();
    try{    
        $pageConfig = array_merge(["break"=>false,"fontsize"=>11,"sheetname"=>"header","title"=>"documentOffice2007","size"=>"A4","orientation"=>"P"],
                        $pageConfig);
        $payLoad = [
            'config'=>$config,
            'data'=>$arrayData,
        ];
        $payLoad = array_merge($payLoad,$pageConfig);
        $response = $client->post(
            env('XLS_RENDERER'),
            [
                'json' => $payLoad,
                'headers' => [
                    'Authorization' => 'Bearer 57aa62501a7fe0d3b71de5712cdb1998',
                    'Accept' => 'application/json',
                ]
            ],
        );
    }catch(\Exception $e){
        return $e->getMessage()." ".$e->getLine();
    }
    
    return response($response->getBody())
    ->withHeaders([
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Pragma' => 'public',
        'Content-Disposition' => "attachment;filename=".$pageConfig['title'].".xlsx",
        'Cache-Control'=>'private, must-revalidate, post-check=0, pre-check=0, max-age=1',
        'Last-Modified'=>gmdate('D, d M Y H:i:s').' GMT',
        'Expires'=>'Mon, 26 Jul 1997 05:00:00 GMT'
    ]);
}

function setLog($data){
    umask(0000);
    $dtrace = (object)debug_backtrace(1,true)[0];
    $filenameArr = explode(php_uname('s')=='Linux'?"/":"\\",$dtrace->file);
    $filename = str_replace(".php",".json",end($filenameArr));
    $path = base_path("logs");
    if( ! File::exists($path) ){
        File::makeDirectory( $path, 493, true);
    }
    $agent = new \Jenssegers\Agent\Agent;
    $data = is_array($data) ? $data:(array)$data;
    $data['___client_timestamp'] = Carbon::now();
    $data['___client_address'] = app()->request->ip();
    $data['___client_browser'] = $agent->browser();
    $data['___client_platform'] = $agent->platform();
    return File::put("$path/$filename",json_encode($data));
}
function getLog($filename=null,$string=false){
    if($filename===null){
        $dtrace = (object)debug_backtrace(1,true)[0];
        $filenameArr = explode(php_uname('s')=='Linux'?"/":"\\",$dtrace->file);
        $filename = str_replace(".php",".json",end($filenameArr));
    }
    $path = base_path("logs/$filename");
    if( ! File::exists($path) ){
        return null;
    }
    if($string){
        return File::get($path);
    }
    return json_decode(File::get($path),true);
}
function getTest($filename=null,$string=false){
    if($filename===null){
        $dtrace = (object)debug_backtrace(1,true)[0];
        $filenameArr = explode(php_uname('s')=='Linux'?"/":"\\",$dtrace->file);
        $filename = str_replace(".php",".json",end($filenameArr));
    }
    $table = getBasic( $filename )->getTable();
    $filename = Str::camel(ucfirst($filename));
    $path = base_path("tests/$filename"."Test.php");
    if( ! File::exists($path) ){
        return str_replace( [
            "___class___","__table__","__resource__"
        ],[
            $filename, $table, $filename
        ],File::get( base_path("templates/test.stub") ) );
    }
    if($string){
        return File::get($path);
    }
    return File::get($path);
}
function removeLog($filename=null){
    if($filename===null){
        $dtrace = (object)debug_backtrace(1,true)[0];
        $filenameArr = explode(php_uname('s')=='Linux'?"/":"\\",$dtrace->file);
        $filename = str_replace(".php",".json",end($filenameArr));
    }
    $path = base_path("logs/$filename");
    if( ! File::exists($path) ){        
        return false;
    }
    return File::delete("$path");
}

function req2( $key = null ) {
    $pairs = explode("&", !app()->request->isMethod('GET') ? file_get_contents("php://input") : (@$_SERVER['QUERY_STRING']??@$_SERVER['REQUEST_URI']));
    $data = (object)[];
    foreach ($pairs as $pair) {
        $nv = explode("=", $pair);
        if(count($nv)<2) continue;
        $name = urldecode($nv[0]);
        $value = urldecode($nv[1]);
        $data->$name = $value;
    }
    
    if($key!==null){
        if( Str::contains($key, '%') ){
            $key = str_replace( '%', '', $key );
            $newData = [];
            foreach((array)$data as $keyArr=>$dt){
                if( Str::startsWith( $keyArr, $key ) ){
                    $newData[$keyArr] = $dt;
                }
            }
            $data = (object) $newData;
        }else{
            return isset($data->$key)? $data->$key : null;
        }
    }
    return $data;
}

function req($key=null){
    // if(app()->request->method()=="GET" ){
    //     $data = (object)config('request');
    // }else{
    //     $data = json_decode(file_get_contents('php://input'));
    // }
    $data = json_decode(json_encode( config('request') ));
    if($key){
        return isset($data->$key)? $data->$key : null;
    }
    return $data;
}
function isJson($args) {
    json_decode($args);
    return (json_last_error()===JSON_ERROR_NONE);
}
function getDriver(){
    return Schema::getConnection()->getDriverName();
}
function isVersion( $var ){
    return (strpos(app()->version(), "^$var.")!==false);
}

/**
 * Casts from request param for all basic models
 */
function getCastsParam():array{
    $casters = [];
    if(req('casts')){
        try{
            $rawCasters = explode(",", req('casts'));
        
            foreach($rawCasters as $key => $caster){
                $casterArr = explode(":", $caster, 2);
                $casters[$casterArr[0]] = $casterArr[1];
            }
        }catch(\Exception $e){
            abort(500,json_encode(["error"=>["casts parameter has wrong format"]]));
        }
    }
    return $casters;
}

/**
 * Get error exception postgresql
 */
function pgsqlParseError( string $msg ):string {
    if(strpos($msg,'SQLSTATE')!==false){
        try{
            $errors = explode("ERROR: ",$msg,2);
            $exception = explode("\n",$errors[1],2);
            $msg = $exception[0];
        }catch(\Exception $e){
            ff($e->getMessage());
        }
    }
    return $msg;
}

function getTableOnly(string $tableName){
    if( Str::contains($tableName, ".") ){
        $exploded = explode(".", $tableName);
        return end($exploded);
    }
    return $tableName;
}

function getModelNameByLevel( int $level = 1 ){
    $name = 'modelname';
    if( $level === 2 ){
        $name = 'detailmodelname';
    }elseif( $level === 2 ){
        $name = 'subdetailmodelname';
    }else{
        return null;
    }

    return @app()->request->route()[2][ $name ];
}

function saveFileToCache( $modelName, $field, $file, $user_id='anonymous', $seconds = 1800 ){
    $key = $modelName."_".$field."_".$user_id."_".sanitizeString($file->getClientOriginalName());
    $path =  $file->getRealPath();
    $blob = base64_encode(File::get($path));
    Cache::put( $key, $blob, $seconds);
    
    return $key;
}

function pullFileFromCache($modelName, $field, $filename, $user_id='anonymous'){
    $key = $modelName."_".$field."_".$user_id."_".sanitizeString($filename);

    $cacheContent = Cache::get( $key );
    if(!$cacheContent){
        return null;
    }
    
    $contents = base64_decode( $cacheContent );
    return $contents;
}

function moveFileFromCache($modelName, $field, $filename, $user_id='anonymous', $oldFile = null ){
    $key = $modelName."_".$field."_".$user_id."_".sanitizeString($filename);
    $contents = pullFileFromCache($modelName, $field, $filename, $user_id);

    if( !$contents ){
        if(!$oldFile){
            abort(422, json_encode(['message'=>"File `$filename` tidak ada atau telah melebihi 30 menit, upload ulang dan segera submit"]));
        }else{
            return $oldFile;
        }
    }
    
    $code = Carbon::now()->format('his').crc32(uniqid());
    $fixedFileName = $code.":::".$filename;
    if(!File::exists(public_path("uploads/$modelName"))){
        umask(0000);
        File::makeDirectory( public_path("uploads/$modelName"), 493, true);
    }

    //  remove old file
    if( $oldFile && File::exists( public_path("uploads/$modelName/$oldFile") ) ){
        File::delete( public_path( "uploads/$modelName/$oldFile" ) );
    }

    $path = File::put(public_path("uploads/$modelName/$fixedFileName"), $contents);
    return $fixedFileName;
}

function getArrayFromExcel( $file, $dateColumns = [] ){
    $data = Excel::toArray( null, $file );
    $rows = $data[0];
    $columns = $rows[0];    // columns
    $bulkData = [];
    
    foreach($rows as $rowIndex => $array){
        if( $rowIndex == 0 ){
            continue; 
        }
        $row = [];
        foreach($columns as $col => $columnName){
            $columnName = str_replace(' ', '', $columnName); // remove space
            if(in_array(strtolower($columnName),[ /*remove unused cols*/ ])){
                continue;
            } //remove unused
            
            $val = $array[$col];
            if(in_array($columnName, $dateColumns) ){ //format tanggal INTEGER excel to date y-m-d
                try{
                    $val = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($array[$col])->format('d/m/Y');                  
                }catch(\Exception $e){
                    $val = date('d/m/Y');
                }
            }
            $row[$columnName] = $val;
        }
        $bulkData[] = $row;
    }

    return $bulkData;
}


function getSchema( $api, $header = null, $isRelation = false, $isDetailed = false ){
    $m = getBasic( $api );
    $body = [];
    foreach( $m->columnsFull as $col ){
        $explodedArr = explode( ":", $col );
        $key = $explodedArr[0];
        $body[ $key ] = str_replace( ":0", '', substr_replace( $col, "", 0, strlen("$key:") ) )
        .(in_array($key, $m->required)?":required":":optional")
        .(in_array($key, $m->unique)?":unique":"")
        .(in_array($key, $m->getGuarded())?":autocreate":"")
        .(in_array($key, ['created_at','updated_at','deleted_at'])?":autocreate":"")
        .($header && $key === $header."_id"?":autocreate":"");

        if( $isDetailed && !$isRelation && Str::endsWith($key, '_id') && method_exists( $m, str_replace('_id', '', $key) ) ){
            $parent = str_replace('_id', '', $key);
            if( $parent !== $header ){
                $relatedTable = $m->$parent()->getRelated()->getTable();

                if( Str::contains($relatedTable, '.') ){
                    $relatedTable = explode('.', $relatedTable)[1];
                }
                
                $relatedKeys = getSchema( $relatedTable, null, true, $isDetailed );
                foreach($relatedKeys as $relatedKey=>$relatedVal){
                    $body["$parent.$relatedKey"] = $relatedVal;
                }
            }
        }
    }

    if($isRelation) return $body;
    
    foreach( $m->details as $detail ){
        $detailName = getTableOnly($detail);
        $body[ $detailName ] = [ getSchema( $detailName, $api ) ];
    }

    return $body;
}

function createModelRow( $m ){
    $relatedTable = $m->getTable();
    if( Str::contains($relatedTable, '.') ){
        $relatedTable = explode('.', $relatedTable)[1];
    }
    
    $body = [];
    foreach( $m->columnsFull as $col ){
        $explodedArr = explode( ":", $col );
        $key = $explodedArr[0];
        if(in_array($key, $m->getGuarded())) continue;
        if(in_array($key, ['created_at','updated_at','deleted_at'])) continue;
        
        $dataType = str_replace( ":0", '', substr_replace( $col, "", 0, strlen("$key:") ) );

        $val = null;
        if(in_array($key, $m->unique)){
            $exist = $m->select('id',$key)->lastest('id')->first();
            $val = !$exist? uniqid(): $exist->$key.$exist->id;
        }else{
            if(Str::contains($dataType,'date')||Str::contains($dataType,'time')) {
                $val = Carbon::now();
            }elseif( Str::contains($dataType,'int') && Str::endsWith($key, '_id') && method_exists( $m, str_replace('_id', '', $key) ) ){
                $parent = str_replace('_id', '', $key);                
                $relatedData = $m->$parent()->getRelated()->first();
                if($relatedData){
                    $val = $relatedData->id;
                }else{
                    $val = createModelRow($m->$parent()->getRelated())->id;
                }
            }elseif( Str::contains($dataType,'int')){
                $val = rand(1, 100);
            }elseif( Str::contains($dataType,'str')||Str::contains($dataType,'text')){
                $val = 'X';
            }elseif( Str::contains($dataType,'bool') ){
                $val = true;
            }else{
                $val = '1000';
            }
        }

        $body[ $key ] = $val;
    }
    
    return $m->create($body);
}

function getTrackedUser( $userId ){
    $key = "track-user-$userId";
    return Cache::get($key);
}

function setTrackedUser( $cacheTime = 0 ){
    if( $cacheTime<=0 ) return;
    $userId = Auth::id();
    $key = "track-user-$userId";
    $request = app()->request;
    $headers = $request->header();
    unset( $headers['authorization'] );
    Cache::put( $key, [
        'ip'=>$request->ip(),
        'agent' => $request->userAgent(),
        'last_activity_at'=> Carbon::now()->format('d/m/Y H:i:s'),
        'last_request_method'=>$request->method(),
        'last_visited_url'=>$request->url(),
        'last_visited_payload'=> $_REQUEST,
        'headers'=>$headers
    ], $cacheTime );
}

function getTrackedRow( $model, $id ){
    $key = "track-$model-$id";
    return Cache::get($key);
}

function releaseTrackedRow( $model, $id ){
    $key = "track-$model-$id";
    return Cache::forget($key);
}

function setTrackedRow( $model, $id, $cacheTime = 600 ){
    if( $cacheTime<=0 ) return;
    $key = "track-$model-$id";
    $request = app()->request;
    $headers = $request->header();
    unset( $headers['authorization'] );
    Cache::put( $key, [
        'ip' => $request->ip(),
        'agent' => $request->userAgent(),
        'payload' => $_REQUEST,
        'at' => Carbon::now()->format('d/m/Y H:i:s'),
        'headers'=> $headers,
        'user' => Auth::user()
    ], $cacheTime );
}
function cloneDatabase( $dstDatabase, $isRecreate=false ){
    $host = env('DB_HOST');
    $username = env('DB_USERNAME');
    $password = env('DB_PASSWORD');
    $database = env('DB_DATABASE');
    
    if($dstDatabase===$database) trigger_error('destination database must be different with source');
    
    $path = storage_path('framework/cache');
    $file = date('Y-m-d') . '-temp-' . $database . '.sql';
    $command = sprintf('mysqldump  '.(isMariaDB()?'--column-statistics=0':'').' -h %s -u %s -p\'%s\' %s --routines> %s', 
                $host, 
                $username, 
                $password, 
                $database, 
                $sqlPath = "$path/$file");
    exec($command);
    // --column-statistics=0
    $command = sprintf('mysql -h %s -u %s -p\'%s\' %s < %s', 
                $host, 
                $username, 
                $password, 
                $dstDatabase, 
                $sqlPath);
    
    if($isRecreate){
        $conn=DB::getDoctrineSchemaManager();
        $conn->dropDatabase($dstDatabase);
        $conn->createDatabase($dstDatabase);
    }
    exec( $command );
    if( ! File::exists($sqlPath) ){
        File::delete( $sqlPath );
    }

    return 'ok';
}

function isMariaDB(){
    return Str::contains( Str::lower(DB::select('select version()')[0]->{'version()'}), 'maria');
}
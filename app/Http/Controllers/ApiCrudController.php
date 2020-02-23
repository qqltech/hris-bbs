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

class ApiCrudController extends Controller
{
    private $integerColumns = ["jumlah","qty","total","nominal","opname_qty","nominal_rugi","harga","disc","diskon","price","discount","grand_total","temp_total","paid_total","item_discount","other_discount"]; //OPTION CONVERTER NUMBER BERISI NAMA-NAMA KOLOM DENGAN JENIS NUMBER
    private $dateFormat     = "d/m/Y";  //OPTION CONVERTER CARBON DATE BERISI FORMAT STRING YANG MASUK DARI FRONTEND
    private $excepts        = ['manyRows','where','model','command','session','relations','_token','id','file','img64','paginate'];    //OPTION KOLOM-KOLOM EXCEPTION YANG TIDAK DIMASUKKAN PADA CREATE/UPDATE QUERY
    private function sendException($request,$message){
        return response()->json(['message'=> $message ], 400);
    }

    private function apiCheck($request) {
            if($request->command==null){
                switch( strtolower($request->method()) ){
                    case 'post':
                        $request->merge(["command"=>"create"]);
                        break;
                    case 'patch':
                    case 'put':
                        $request->merge(["command"=>"update"]);
                        break;
                    case "delete":
                        $request->merge(["command"=>"delete"]);
                        break;
                    default:
                        if($request->id == null){
                            $request->merge(["command"=>"select"]);
                        }else{
                            $request->merge(["command"=>"show"]);
                        }
                }
            }
            if($request->function!=null){
                $request->merge([
                    "command" => "custom"
                ]);
            }
            if($request->model == 'me'){
                $request->merge( ["id"=>MD5($request->user()->id) ] );
            }
        
        return $request;
    }

    private function getModel($model){
        if(in_array( $model, array_keys(config('models.models')) ) ){
            $model = str_replace("/","\\",str_replace("\\","",config("models.models.$model")['class']));
            return new $model;
        }else{
            return null;
        }
    }

//=================================================================================================UPDATE
    private function update($model,$request){
        // return $request->all();
        if(method_exists($model,'validation')){
             $request->validate($model->validation());
        }
        DB::beginTransaction();
        try{
            if($request->password != null){
                $request->merge(['password' => bcrypt($request->password)]);
            }
            if($request->where!=null && $request->id="inwhere()"){
                $parent = $model->whereRaw( $request->where );
                if(!$request->manyRows){
                    $parent=$parent->first();
                }
            }else{
                $parent = (strlen($request->id) < 30) ?
                    $model  ->find($request->id) :
                    $model  ->where(DB::raw("MD5(".$model->getKeyName()."".(\DB::connection()->getName() == 'pgsql'?"::text":"").")"),"=",$request->id)->first();
            }

            if(method_exists($model,'beforeUpdate')){
                $model->beforeUpdate($parent);
            }
            $childCount=0;
            if( count($request->relations)>0 ){
                if(count($request->relations)==1){
                    $details = \Detail::getDetail( $request->except($this->excepts), $this->integerColumns, $this->dateFormat , $request);
                    foreach( $request->relations as $key => $child ){
                        if(method_exists($model,$child)){
                            $parent->$child()->delete();
                            $parent->$child()->createMany($details);
                            $childCount++;
                        }
                    }
                }elseif(count($request->relations)>1){
                    foreach( $request->relations as $key => $child ){
                        $details = \Detail::getDetail( $request->except($this->excepts), $this->integerColumns, $this->dateFormat , $request, $child);                        
                        if(method_exists($model,$child)){
                            $parent->$child()->delete();
                            $parent->$child()->createMany($details);
                            $childCount++;
                        }
                    }
                }
            }

            $parentData    = \Detail::getParent( $request->except($this->excepts), $this->integerColumns, $this->dateFormat , $request);

            // $parent =(strlen($request->id) < 30) ?
            //     $model->find($request->id) :
            //     $model  ->where(DB::raw("MD5(".$model->getKeyName()."".(\DB::connection()->getName() == 'pgsql'?"::text":"").")"),"=",$request->id)
            //             ->first();

                $data=$parent? $parent->update($parentData) : $model->create($parentData);
            if( $request->hasFile('file') && !$request->manyRows){
                $validator = Validator::make($request->all(), [
                    'file.*' => 'max:20000|mimes:jpg,png,jpeg,bmp,doc,pdf,docx,xls,xlsx,zip,rar,tar,tar.gz,ppt,pptx,'
                ]);

                if ($validator->fails()) {
                    return $this->sendException($request, $validator->messages() );
                }
                if( !$this->upload($parent, $request)){
                    return $this->sendException($request, "file uploading error" );
                };
            }
            // return $parent;
            if( $request->img64 != null ){
                $this->saveimg64( $parent, rawurldecode($request->img64) );
            }

            DB::table('activities_report')->insert([
                'user_id'   =>  $request->user()->id,
                'table'     =>  $model->getTable(),
                'table_id'  =>  ($request->manyRows)? $request->id:$parent->id,
                'action'    =>  'update',
                'value'     =>  json_encode($model->first()),
                'created_at'=>  Carbon::now()
            ]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            DB::table('activities_report')->insert([
                'user_id'   =>  $request->user()->id,
                'table'     =>  $model->getTable(),
                'table_id'  =>  ($request->manyRows)? $request->id:$parent->id,
                'action'    =>  'update',
                'value'     =>  json_encode($parentData),
                'error'     =>  json_encode($e-> getMessage()),
                'status'    =>  'failed',
                'created_at'=>  Carbon::now()
            ]);
            return $this->sendException($request, $e-> getMessage());
        }

        if(method_exists($model,'afterUpdate')){
            $model->afterUpdate($model);
        }
        return response()->json(['status'=>'data updated successfully','id'=>$request->id ,'childTotal'=>$childCount], 200);
    }

//=================================================================================================CREATE/INSERT
    private function create($model,$request){
        if(method_exists($model,'validation')){
             $request->validate($model->validation());
        }
        DB::beginTransaction();
        try{
            if($request->password != null)
                $request->merge(['password' => bcrypt($request->password)]);
            if(method_exists($model,'beforeCreate')){
                $model->beforeCreate($request);
            }
            if( method_exists($model,'writeWhere') ){
                $request->merge( $model->writeWhere() );
            }
            $parentData    = \Detail::getParent( $request->except($this->excepts), $this->integerColumns, $this->dateFormat , $request);
            $parent        = $model->create($parentData);
            $childCount = 0;
            if( count($request->relations)>0 ){
                if(count($request->relations)==1){
                    $details = \Detail::getDetail( $request->except($this->excepts), $this->integerColumns, $this->dateFormat , $request);
                    foreach( $request->relations as $key => $child ){
                        if(method_exists($model,$child)){
                            $parent->$child()->createMany($details);
                            $childCount++;
                        }
                    }
                }elseif(count($request->relations)>1){
                    foreach( $request->relations as $key => $child ){

                        $details = \Detail::getDetail( $request->except($this->excepts), $this->integerColumns, $this->dateFormat , $request, $child);
                        
                        if(method_exists($model,$child)){
                            $parent->$child()->createMany($details);
                            $childCount++;
                        }
                    }
                }
            }
            if( $request->hasFile('file') ){
                $validator = Validator::make($request->all(), [
                    'file.*' => 'max:5000|mimes:jpg,png,jpeg,bmp,doc,pdf,docx,xls,xlsx,zip,rar,tar,tar.gz,ppt,pptx,'
                ]);

                if ($validator->fails()) {
                    return $this->sendException($request, $validator->messages() );
                }
                if( !$this->upload($parent, $request)){
                    return $this->sendException($request, "file uploading error" );
                };
            }

            if( $request->img64 != null ){
                $this->saveimg64( $parent, rawurldecode($request->img64) );
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            DB::table('activities_report')->insert([
                'user_id'   =>  $request->user()->id,
                'table'     =>  $model->getTable(),
                'action'    =>  'create',
                'value'     =>  json_encode($parentData),
                'error'     =>  json_encode($e-> getMessage()),
                'status'    =>  'failed',
                'created_at'=>  Carbon::now()
            ]);
            return $this->sendException($request, $e-> getMessage());
        }

        if(method_exists($model,'afterCreate')){
            $model->afterCreate($parent);
        }
        return response()->json(['status'=>'data created successfully','id'=>MD5($parent->id), 'childTotal' => $childCount ], 201);
    }

//=================================================================================================DELETE
    private function delete($model,$request){
        DB::beginTransaction();
        $childCount=0;
        try{

            if(method_exists($model,'beforeDelete')){
                $model->beforeDelete($request);
            }
            if( count($request->relations)>0 ){
                $parent = (strlen($request->id) < 30) ?
                            $model->find($request->id) :
                            $model->where(DB::raw("MD5(".$model->getKeyName()."".(\DB::connection()->getName() == 'pgsql'?"::text":"").")"),"=",$request->id)->first();
                foreach( $request->relations as $key => $child ){
                    if(method_exists($model,$child)){
                        $parent->$child()->delete();
                        $childCount++;
                    }
                }
            }

            if($request->where!=null && $request->id="inwhere()"){
                $modelCandidate = $model->whereRaw( $request->where );
                if(!$request->manyRows){
                    $modelCandidate=$modelCandidate->first();
                }
            }else{
                $modelCandidate =
                (strlen($request->id) < 30) ?
                    $model->find($request->id) :
                    $model  ->where(DB::raw("MD5(".$model->getKeyName()."".(\DB::connection()->getName() == 'pgsql'?"::text":"").")"),"=",$request->id);
            }

                DB::table('activities_report')->insert([
                    'user_id'   =>  $request->user()->id,
                    'table'     =>  $model->getTable(),
                    'table_id'  =>  ($request->manyRows)? $request->id:$modelCandidate->get()->first()->id,
                    'action'    =>  'delete',
                    'value'     =>  json_encode($modelCandidate->first()),
                    'created_at'=>  Carbon::now()
                ]);

                $modelCandidate->delete();
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            DB::table('activities_report')->insert([
                'user_id'   =>  $request->user()->id,
                'table'     =>  $model->getTable(),
                'table_id'  =>  $request->id,
                'action'    =>  'delete',
                'value'     =>  json_encode($modelCandidate),
                'error'     =>  json_encode($e-> getMessage()),
                'status'    =>  'failed',
                'created_at'=>  Carbon::now()
            ]);
            return $this->sendException($request, $e-> getMessage());
        }

        if(method_exists($model,'afterDelete')){
            $model->afterDelete($model);
        }
        return response()->json(['status'=>'data deleted successfully', 'id'=>$request->id, 'childTotal'=>$childCount], 200);
    }

//=================================================================================================SOFTDELETE
    private function softDelete($model,$request){
        DB::beginTransaction();
        try{
            (strlen($request->id) < 30) ?
                $model->find($request->id)->update(['deleted_at'=>Carbon::now() ]) :
                $model  ->where(DB::raw("MD5(".$model->getKeyName()."".(\DB::connection()->getName() == 'pgsql'?"::text":"").")"),"=",$request->id)
                        ->update(['deleted_at'=>Carbon::now() ]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return $this->sendException($request, $e-> getMessage());
        }
        return response()->json(['status'=>'data softDeleted successfully', 'id'=>$request->id], 200);
    }

//=================================================================================================SELECT
    private function show($model,$request){
        try{
            $data   = [];
            $keyName=$model->getKeyName();
            if( count($request->relations)>0 ){
                if( method_exists($model,'selectRaw') ){
                    $model = $model->select(DB::raw( ($model)->selectRaw() ) );
                }
                $parent = (strlen($request->id) < 30) ?
                        $model->find($request->id) :
                        $model  ->where(DB::raw("MD5(".$keyName."".(\DB::connection()->getName() == 'pgsql'?"::text":"").")"),"=",$request->id);
                $details = \Detail::getDetail( $request->except($this->excepts), $this->integerColumns, $this->dateFormat , $request);
                foreach( $request->relations as $key => $child ){
                    $data [] = $parent->with($child)->first();
                }
                if( count($data) ==1 ){
                    $data = $data[0];
                }
            }else{
                $primary = $keyName;

                if( method_exists($model,'selectRaw') ){
                    $model = $model->select(DB::raw( $model->selectRaw() ) );
                }
                $data = (strlen($request->id) < 30) ?
                        $model  ->find($request->id) :
                        $model  ->where(DB::raw("MD5(".$primary."".(\DB::connection()->getName() == 'pgsql'?"::text":"").")"),"=",$request->id)->first();
            }

        }catch(\Exception $e){
            return $this->sendException($request, $e-> getMessage());
        }
        return response()->json(['status'=>'data selected successfully', 'data'=>$data], 200);
    }
//=====================================================================================LIST
    private function list($model,$request){
        try{

            if($request->where != null){
                $model = $model->whereRaw( urldecode($request->where) );
            }

            if( method_exists($model,'readWhere') ){
                $model = $model->where($model->readWhere());
            }

            if( method_exists($model,'selectRaw') ){
                $model = $model->select(DB::raw( $model->selectRaw() ) );
            }

            if($request->paginate != null){
                $data = $model ->paginate( $request->paginate );
            }else{
                $data = $model ->get();
            }
        }catch(\Exception $e){
            return $this->sendException($request, $e-> getMessage());
        }
        
        return response()->json(['status'=>'data selected successfully', 'data'=>$data], 200);
    }


//=================================================================================================SAVE BASE64FILE
    private function saveimg64( $model, $data64 ){
            $type = 'image';
            $isImage=true;
            $sub_folder="/image/";
            $data64 = str_replace("data:image/jpeg;base64,","",$data64);
            $imageFile=Image::make( base64_decode($data64) );
            // try{
            //     $imageFile=Image::make( base64_decode($data64) );
            // }catch(\Exception $e){
            //     $imageFile=Image::make( $data64 );
            // }
            $imageFile->resize(300, 300);
            if( ! \File::isDirectory(public_path('uploads/'.$sub_folder))) {
                \File::makeDirectory(public_path('uploads/'.$sub_folder), 493, true);
            }
            $code=Carbon::now()->format('his');
            $imageFile->save( public_path('uploads/'.$sub_folder."/".$code."_".$model->getTable().".jpeg" ) );
            $fileModel = $this->getModel('file');
            $fileModel->create([
                'table'     => $model->getTable(),
                'parent_id' => $model->id,
                'type'      => $type,
                'filename'  => $code."_".$model->getTable().".jpeg"
            ]);
            if( in_array( "imgurl", Schema::getColumnListing($model->getTable())) ){
                $model->update([
                    "imgurl" => url('uploads/'.$type.'/'.$code."_".$model->getTable().".jpeg")
                ]);
            }
    }
//=================================================================================================UPLOAD FILE
    public function upload( $model, $request ){
        if($request->id!=null){

            $originalId = (strlen($request->id) < 30) ?
                $model->find($request->id)->pluck("id") :
                $model->select('id')->where(DB::raw("MD5(".$model->getKeyName()."".(\DB::connection()->getName() == 'pgsql'?"::text":"").")"),"=",$request->id)->first();
            if($originalId == null){
                return $this->sendException($request, ["Id $request->id is not exist!"] );
            }else{
                $request->id = $originalId;
            }
            if( $request->hasFile('file') ){
                $validator = Validator::make($request->all(), [
                    'file.*' => 'max:20000|mimes:jpg,png,jpeg,bmp,doc,pdf,docx,xls,xlsx,zip,rar,tar,tar.gz,ppt,pptx'
                ]);

                if ($validator->fails()) {
                    return $this->sendException($request, $validator->messages() );
                }
            }else{
                return $this->sendException($request, ["file cannot be null/empty!"] );
            }
        }
        try {

            foreach($request->file as $key=>$file){
                $isImage=false;
                if( !in_array( exif_imagetype($file), [IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_BMP] ) ){
                    $sub_folder="/file/";
                    $type = "file";
                }else{
                    $type = 'image';
                    $isImage=true;
                    $sub_folder="/image/";
                    $imageFile=Image::make($file);
                    $imageFile->resize(300, 300);
                    // echo "<br>".$file->getClientOriginalName()." is not an image with size: ".$file->getClientSize();
                }

                if( ! \File::isDirectory(public_path('uploads/'.$sub_folder))) {
                    \File::makeDirectory(public_path('uploads/'.$sub_folder), 493, true);
                }
                $code=Carbon::now()->format('his');
                $saved = ($isImage)?
                    $imageFile->save( public_path('uploads/'.$sub_folder."/".$code.$key."_".$file->getClientOriginalName()) ):
                    Storage::disk('local')->putFileAs(
                        $sub_folder, $file, $code.$key."_".$file->getClientOriginalName()
                    );
                    $fileModel = $this->getModel('file');
                    if($request->command='update'){
                        $fileModel->where('table',$model->getTable())
                                  ->where('parent_id',$request->id)
                                  ->update([
                                        'type'      => $type,
                                        'filename'  => $code.$key."_".$file->getClientOriginalName()
                                  ]);

                    }else{
                        $fileModel->create([
                            'table'     => $model->getTable(),
                            'parent_id' => $request->id != null ? $request->id : $model->id,
                            'type'      => $type,
                            'filename'  => $code.$key."_".$file->getClientOriginalName()
                        ]);
                    }
                    if( $isImage && in_array( "imgurl", Schema::getColumnListing($model->getTable()))  ){
                        $model->update([
                            "imgurl" => url('uploads/'.$type.'/'.$code.$key."_".$file->getClientOriginalName())
                        ]);
                    }
            }

        }catch(\Exception $e){
            if($request->id!=null){
                return $this->sendException($request, $e-> getMessage());
            }else{
                return false;
            }
        }

        if($request->id!=null){
            return response()->json( ['status'=>'file uploaded successfully', 200] );
        }else{
            return true;
        }
    }
//=================================================================================================SUMMERNOTES
    private function summernote($model,$request){
        if( $request->content == null || $request->content == '' ){
            return $this->sendException($request, ["content cannot be null/empty"] );
        }
        $feature=$request->title;
        $content=$request->content;
        $dom = new \DomDocument();
        libxml_use_internal_errors(true);
        $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $images = $dom->getElementsByTagName('img');
        $logo = "";

        $opt_path = "/uploads/articles";
        if( ! \File::isDirectory(public_path($opt_path))) {
            \File::makeDirectory(public_path($opt_path), 493, true);
        }

        foreach($images as $k => $img){
            $data = $img->getAttribute('src');
            list($type, $data) = explode(';', $data);
            list(, $data)      = explode(',', $data);
            // $data = base64_decode($data);
            $image_name= $opt_path ."/". time().$k.'.jpeg';
            $path = public_path() . $image_name;
            if($logo==""){ $logo = $image_name; }
            $imageFile=Image::make($data);
            $imageFile->resize(450, 450);
            $imageFile->save( $path );
            // file_put_contents($path, $data);
            $img->removeAttribute('src');
            $img->setAttribute('src', url($image_name));
        }

        $content = $dom->saveHTML();
        $articles = $model;
        $articles->content = $content;
        $articles->headlines = str_limit(strip_tags(strip_tags($content, '<p><span><strong><h1><h2><h3><h4>')),60);
        $articles->logo = $logo;
        $articles->title=$feature;
        $articles->save();

        if( $request->hasFile('file') ){
            $validator = Validator::make($request->all(), [
                'file.*' => 'max:15000|mimes:jpg,png,jpeg,bmp,doc,pdf,docx,xls,xlsx,zip,rar,tar,tar.gz,ppt,pptx'
            ]);

            if ($validator->fails()) {
                return response()->json( ['status'=>$validator, 404] );
            }
            if( !$this->upload($articles, $request)){
                return $this->sendException($request, "file uploading error" );
            };
        }

        echo "<h2>Judul</h2>" , $feature;
        echo "<h2>Details</h2>" , $content;
    }
//=================================================================================INDEX DATATABLE
    private function datatable($model, $request){
        // return $request->all();
        try{
            if($request->where != null){
                $data = $model->datatable($request->where);
            }else{
                $data = $model->datatable();
            }

            $data = $model->list($data,$request->model );
        }catch(\Exception $e){
            return $e-> getMessage();
        }

        return $data;
    }
//=================================================================================INDEX CHART
private function chart($model, $request){
    // dd($request->all());
    try{
        $data = $model->chart($request);
        $page=$request->page;
        // if($request->where != null){
        //     $data = $data->whereRaw( urldecode($request->where) );
        // }
    }catch(\Exception $e){
        return $e-> getMessage();
    }

    // return response()->json($data,200);
    return view($data['blade'],compact('page','data'));
}
//=================================================================================SELECT2
private function select2($model, $request){
    try{
        // if( $request->session['user']['id'] != null && in_array( "user_id", Schema::getColumnListing( $model->getTable() ) ) ){
        //     $data = $model->datatable()->where( "user_id", $request->session['user']['id']  );
        // }else{
        // }

        // if($request->where != null){
        //     $data = $data->whereRaw( urldecode($request->where) );
        // }
        $data = $model->select2($request->search)->get();
        // $data->get();

    }catch(\Exception $e){
        return $e-> getMessage();
    }

    return [
        "results"   => $data
    ];
}
//=================================================================================SELECT2
private function exportExcel($model, $request){
    try{

        $rawData = $model->exportExcel( $model->datatable()->get() );
        $data = Excel::download(new Export($rawData), $rawData['filename']);

    }catch(\Exception $e){
        return $e-> getMessage();
    }
    return $data;
}
//=================================================================================APPROVAL AUTOMATION
private function approval($model, $request){
    $now = \App\Models\ApprovalDetail::
                    join('approval','approval.id','=','approval_detail.approval_id')
                    ->where('user_id', $request->user_id)
                    ->where('table_name', $model->getTable() )
                    ->first();
    if(!$now){
        return 'nothing approved';
    }
    $next = \App\Models\ApprovalDetail::
                    join('approval','approval.id','=','approval_detail.approval_id')
                    ->where('level',">", $now->level)
                    ->where('table_name', $model->getTable() )
                    ->first();
    if(!$next){
        $model->update([
            "status"        =>  "approved",
            "approver_id"   =>  -1,
            "approver_auth" =>  "end"
        ]);
        return 'end of approval';
    }else{
        $model->update([
            "status"        =>  $next->status_name,
            "approver_id"   =>  $next->user_id,
            "approver_auth" =>  $next->authority
        ]);
        return 'pass to next approval';
    }

}
//=================================================================================INDEX ROUTER
private function custom($model, $request){
    $function = $request->function;
    if( !method_exists($model,$function) ){
        return $this->sendException($request, ["$function function does not exist"] );
    }
    return $model->$function($request);
}
//=================================================================================INDEX ROUTER

//=================================================================================INDEX ROUTER
    public function query(Request $request){
        // return "a";
        // return $request->methods();
        // return response()->json(['data'=>$request->all()]);
        $request = $this->apiCheck($request);
        // return response()->json($request->all());
        $validator = \Validator::make($request->all(), [
            'command'   => 'required|string|min:1',
            'model'     => 'required|string|min:1'
        ]);
    
        if ($validator->fails()) {
            return $validator->errors();
        }
    
        // $request->validate([
        //     'command'   => 'required|string|min:1',
        //     'model'     => 'required|string|min:1'
        // ]);
        // return $request->all();
        if(in_array($request->command,['update','delete','softDelete','show'])){
            if($request->where!=null){
                $request->merge(['id'=>'inwhere()']);
            }
            $request->validate([
                'id'     => 'required'
            ]);
        };
        // return $request->all();
        // if($request->command!='custom'){
        //     $model = $this->getModel($request->model);
        //     if($model == null){return response()->json(['status'=>'model tidak ada'],404);}
        // }
        
        $modelCandidate = explode(":",$request->model);
        $relations = [];
        if(count($modelCandidate)==1){
            $model = $this->getModel($request->model);
        }else{
            $model = $this->getModel($modelCandidate[0]);
            foreach( $modelCandidate as $key => $child ){
                if($key > 0){
                    $relations[] = $child;
                    if( !method_exists($model,$child) ){
                        return $this->sendException($request, ["maaf relationship $child tidak ada"] );
                        break;
                    }
                }
            }
        };
        $request->merge(["relations" => $relations]);
        switch($request->command){
            case 'update':
                return $this->update($model,$request);
                break;
            case 'delete':
                return $this->delete($model,$request);
                break;
            case 'softdelete':
                return $this->softDelete($model,$request);
                break;
            case 'create':
                return $this->create($model,$request);
                break;
            case 'show':
            case 'edit':
                return $this->show($model,$request);
                break;
            case 'select':
                return $this->list($model,$request);
                break;
            case 'chart':
                return $this->chart($model,$request);
                break;
            case 'upload':
                return $this->upload($model,$request);
                break;
            case 'check_exist':
                return $this->check_exist($model,$request);
                break;
            case 'articles':
                return $this->summernote($model,$request);
                break;
            case 'datatable':
                return $this->datatable($model,$request);
                break;
            case 'exportExcel':
                return $this->exportExcel($model,$request);
                break;
            case 'select2':
                return $this->select2($model,$request);
                break;
            case 'custom':
                return $this->custom($model,$request);
                break;
            case 'apitest':
                return response()->json(["status"=>"api is ready for use"]);
                break;
            default:
                return $this->sendException($request, ["wrong command"] );
        }

    }
}

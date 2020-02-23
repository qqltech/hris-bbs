<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use App\Helpers\PLSQL as PLSQL;
use App\Helpers\DBS as DBS;
use App\Models\Defaults\User;

class LaradevController extends Controller
{
    private $modelsPath = "";
    private $prefixNamespace = "";
    private $prefixNamespaceCustom = "";
    private $hasMany ="";
    private $belongsTo ="";
    private $hasManyThrough = "";

    public function __construct()
    {
        umask(0000);
        $this->modelsPath = app()->path()."/Models";
        if( ! File::exists($this->modelsPath."/BasicModels") ){
            File::makeDirectory( $this->modelsPath."/BasicModels", 493, true);
        }
        if( ! File::exists($this->modelsPath."/CustomModels") ){
            File::makeDirectory( $this->modelsPath."/CustomModels", 493, true);
        }
        if( ! File::exists(base_path("database/migrations/projects")) ){
            File::makeDirectory( base_path("database/migrations/projects"), 493, true);
        }
    }
    private function getBasicModel(){
        return File::get( base_path("templates/basicModel.stub") );
    }
    private function getCustomModel(){
        return File::get( base_path("templates/customModel.stub") );
    }
    private function getMigration(){
        return File::get( base_path("templates/migration.stub") );
    }
    private function getFullTables($toModel=false,$tableKhusus=null){
        try{
            $schemaManager = DB::getDoctrineSchemaManager();
            $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $data = $schemaManager->listTables();
            $tables = [];
            $fks = [];
            $cds = [];
            foreach ($data as $table) {
                $foreignKeys = [];
                $required = [];
                $defaults = [];
                $unique = [];
                $rawForeignKeys = $table->getForeignKeys();
                $indexes = $table->getIndexes();
                foreach ($rawForeignKeys as $fk) {
                    $fktemp= [
                        "child"=> $fk->getLocalTableName(), "child_column"=>implode($fk->getLocalColumns()),
                        "parent"=> $fk->getForeignTableName(), "parent_column"=>implode($fk->getForeignColumns())
                    ];
                    $foreignKeys[]=$fktemp;
                    $fks[$fk->getForeignTableName()][] = $fktemp;
                    $cds[$fk->getLocalTableName()][]   = $fktemp;
                }
                $columns = [];
                $columnNames = [];
                foreach ($table->getColumns() as $column) {
                    foreach($indexes as $key=>$index){
                        if(in_array($column->getName(), $index->getColumns()) && !$index->isPrimary() && $index->isUnique()){
                            $unique[$column->getName()] = "unique:".$table->getName().",".$column->getName();
                        }
                    }
                    $columnNames[] = $column->getName();
                    $columns[] = [
                        "name"=>$column->getName(),
                        "type"=> "".$column->getType(),
                        "length"=> "".$column->getLength(),
                        "default"=> "".$column->getDefault(),
                        "comment"=> "".$column->getComment(),
                        "nullable"=> $column->getNotnull()
                    ];
                    
                    if( !in_array($column->getName(), ["id","created_at","updated_at"]) &&  $column->getNotnull()){
                        $required[]=$column->getName();
                    }
                    $comment    = $column->getComment();
                    $columnName = $column->getName();

                    if($comment!=null && $comment!=""){
                        $comment = json_decode($comment);
                        if( isset($comment->fk) && $comment->fk!="false" ){
                            $fk = $comment->fk;
                            $arrayFK = explode(".", $fk);
                            if($arrayFK[1]=="id"){
                                $fktemp= [
                                    "child"=> $table->getName(), "child_column"=>$column->getName(),
                                    "parent"=> $arrayFK[0], "parent_column"=> $arrayFK[1], "cascade"=>true
                                ];
                                $foreignKeys[]=$fktemp;
                                $fks[ $arrayFK[0]][] = $fktemp;
                                $cds[ $table->getName() ][]   = $fktemp;
                            }
                        }
                        if( isset($comment->src) && $comment->src!="false" ){
                            $fk = $comment->src;
                            $arrayFK = explode(".", $fk);
                            if($arrayFK[1]=="id"){
                                $fktemp= [
                                    "child"=> $table->getName(), "child_column"=>$column->getName(),
                                    "parent"=> $arrayFK[0], "parent_column"=> $arrayFK[1], "cascade"=>false
                                ];
                                $foreignKeys[] = $fktemp;
                                $fks[ $arrayFK[0]][] = $fktemp;
                                $cds[ $table->getName() ][]   = $fktemp;
                            }
                        }
                        if( isset($comment->required) ){
                            $isRequired = $comment->required;
                            if($isRequired){
                                $required[]=$column->getName();
                            }
                        }
                        if( isset($comment->value) ){
                            $defaults[$column->getName()] = $comment->value;
                        }
                    }elseif(strpos($columnName, '_id') !== false){                         
                        $fktemp= [
                            "child"=> $table->getName(), "child_column"=>$columnName,
                            "parent"=> str_replace("_id","",$columnName), "parent_column"=> "id" , "cascade"=>true
                        ];
                        $foreignKeys[]=$fktemp;
                        $fks[ str_replace("_id","",$columnName)][] = $fktemp;
                        $cds[ $table->getName() ][]   = $fktemp;                        
                    }
                }
                $fullColumns = $columns;
                if($toModel){
                    $columns = $columnNames;
                    $required = count($required)>0?'["'.implode('","',$required).'"]':"[]";
                }
                $tables[]=[
                    "table" => $table->getName(),
                    "fullColumns" => $fullColumns,
                    "config" => $table->getComment()?json_decode($table->getComment()):null,
                    "columns"=>$columns,
                    "values"=>$defaults,
                    "foreign_keys" => $foreignKeys,
                    "required" => $required,
                    "uniques" => $unique,
                    'triggers'=>\App\Helpers\DBS::getTriggers($table->getName())
                ];
                // file_get_contents("https://api.telegram.org/bot716800967:AAFOl7tmtnoBHIHD4VV_WfdFfNhfRZz0HGc/sendMessage?chat_id=-345232929&text="
                // .json_encode($table->getComment() ));
            }
            $views = $schemaManager->listViews();
            foreach($views as $view){
                $columnNames = [];
                $columns     = [];
                $selectString = str_replace("SELECT ","",explode(" FROM",$view->getSql())[0]);
                foreach(explode(",", $selectString) as $key => $column){
                    $col = $column;
                    if(strpos($col," AS ")!==false){
                        $col = explode( "AS ",$col )[1];
                    }
                    if(strpos($col,".")!==false){
                        $col = explode( ".",$col )[1];
                    }
                    $col = str_replace(['"'," ","\n"],["","",""],$col);
                    $columnNames[]=$col;
                    $columns[] = [
                        "name"=>$col,
                        "type"=> "string",
                        "length"=> "",
                        "default"=> "",
                        "comment"=> "",
                        "nullable"=> true
                    ];
                };
                $tables[]=[
                    "table" => str_replace("public.","",$view->getName()),
                    "fullColumns" => $columns,
                    "config" => null,
                    "columns"=>$columnNames,
                    "values"=>[],
                    "foreign_keys" => [],
                    "required" => "[]",
                    "uniques" => [],
                    'triggers'=>[]
                ];
            }
        }catch(\Exception $e){
            return null;
        }
        $data = [
            "tables"=>$tables,
            "foreignkeys"=>$fks,
            "children" => $cds
        ];
        return $data;
    }

    private function getFullTable($table, $toModel=false){
        return $this->getFullTables($toModel, $table);
    }
    public function readEnv(Request $request){
            $data = [
                'APP_DEBUG' => 'true',
                'APP_ENV' => 'local',
                'APP_KEY' => 'base64:C4zyfJxLlJ8nxA6y6ENFK3qsq9fghPqscFaSr2wB7Uc=',
                'APP_NAME' => 'Project',
                'APP_TIMEZONE' => 'Asia/Jakarta',
                'APP_URL' => 'http://localhost',
                'DB_CONNECTION' => 'mysql',
                'DB_DATABASE' => 'trial2',
                'DB_HOST' => 'localhost',
                'DB_PORT' => '3306',
                'DB_PASSWORD' => '',
                'DB_STRICT_MODE' => 'false',
                'DB_USERNAME' => 'root',
                'MAIL_DRIVER' => 'smtp',
                'MAIL_ENCRYPTION' => 'ssl',
                'MAIL_FROM_ADDRESS' => 'starlight93@gmail.com',
                'MAIL_FROM_NAME' => 'fajar',
                'MAIL_HOST' => 'smtp.googlemail.com',
                'MAIL_PASSWORD' => '',
                'MAIL_PORT' => '465',
                'MAIL_USERNAME' => 'starlight93@gmail.com',
                'TG_TOKEN' => 'xxx',
                'TG_CHATID' => 'xxx',
                'FIREBASE_KEY' => 'xxx'
            ];
            $env = [];
            foreach ($data as $key => $value) {
                $env[$key] = urldecode(getenv($key));
            };
            return response()->json($env);
    }
    public function setEnv(Request $request){
        $envFile = File::get(base_path(".env"));
        $keys = array_keys($request->all());
        $terganti = [];
        $data = [];
        foreach($request->all() as $key => $val){
            $terganti[] = $key."=".getenv($key);
            $data[] = "$key=$val";
        }
        $envFile = str_replace($terganti, $data, $envFile);
        File::put(base_path(".env"),$envFile);
        return $data;
    }

    public function readDatabases(Request $request){
        $databases = DB::getDoctrineSchemaManager()->listDatabases();
        return $databases;
    }
    public function createDatabase(Request $request){
        DB::getDoctrineSchemaManager()->createDatabase($request->name);
        return "create database OK";
    }
    public function deleteDatabase(Request $request, $databaseName){
        DB::getDoctrineSchemaManager()->dropDatabase($databaseName);
        return "delete database OK";
    }
    public function readTables(Request $request,$table=null){
        if($table){
            return $this->getFullTable($table);
        }
        if($request->details){
            return $this->getFullTables();
        }
        $schemaManager = DB::getDoctrineSchemaManager();
        $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $tables = $schemaManager->listTables();
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[]=$table->getName();
        }
        return $tableNames;        
    }

    public function createTables(Request $request){
        $cols = $request->columns;
        $tableName = $request->table;
        Schema::dropIfExists($tableName);
        Schema::create($tableName, function (Blueprint $table)use($cols, $tableName) {
            $table->bigIncrements('id');
            foreach($cols as $column){
                $column=(object)$column;
                $datatype   = $column->datatype;
                $name       = $column->name;
                if( isset($column->meta) ){
                    $table->$datatype($name)->nullable()->comment(json_encode($column->meta));                    
                }else{
                    $table->$datatype($name)->nullable();
                }
            }
            $table->timestamps();
        });
        return "create table OK";
    }
    public function renameTables(Request $request, $tableName){
        $data = $this->getDirFullContents( base_path('database/migrations') );
        $request->name = str_replace(" ","_", $request->name);
        $data = array_filter($data,function($file)use ($tableName){
            if(strpos("$file.php", "$tableName.php")!==false){
                return $file;
            }
        });
        if(count($data)==0){
            return response()->json("migration file [$tableName] tidak ada",400);
        }
        
        if( !File::exists( "$this->modelsPath/BasicModels/$tableName.php") ){
            return response()->json("maaf model $tableName belum termigrate, silahkan dimigrate dahulu", 400);    
        }

        if(in_array("$request->name.php", $data)){
            return response()->json("maaf nama model $request->name telah terpakai", 400);
        }
        if(Schema::hasTable($tableName)){
            Schema::rename($tableName, $request->name);
        }
        if($request->models){
            File::put( "$this->modelsPath/CustomModels/$request->name.php", 
                str_replace( $tableName,$request->name,File::get( "$this->modelsPath/CustomModels/$tableName.php" ) )
            );
            File::put( "$this->modelsPath/BasicModels/$request->name.php",
            str_replace($tableName,$request->name, File::get( "$this->modelsPath/BasicModels/$tableName.php" ) )
            );
            File::put(base_path('database/migrations/projects')."/0_0_0_0_"."$request->name.php", str_replace([
                str_replace("_","",$tableName),$tableName,
            ],[
                str_replace("_","",$request->name),$request->name
            ],File::get( base_path('database/migrations/projects')."/0_0_0_0_"."$tableName.php" ) ));

            File::delete( "$this->modelsPath/CustomModels/$tableName.php" );
            File::delete( "$this->modelsPath/BasicModels/$tableName.php" );
            File::delete( base_path('database/migrations/projects')."/0_0_0_0_"."$tableName.php" );
        }
        $this->createModels( $request, 'abcdefghijklmnopq' );
        return "rename table OK";
    }
    public function deleteTables(Request $request, $tableName){
        Schema::dropIfExists($tableName);
        if($request->models){
            File::delete( "$this->modelsPath/CustomModels/$tableName.php" );
            File::delete( "$this->modelsPath/BasicModels/$tableName.php" );
        }
        return "delete table OK";
    }
    public function migrateDefault(Request $request){ 
        Artisan::call("migrate:".($request->fresh?"fresh":"refresh"),[
                "--path"=>"database/migrations/__defaults" , "--force"=>true
            ]
        );
        if($request->seed){
            Artisan::call("db:seed");
        }
        if($request->passport){
            Artisan::call("passport:install");
        }
        return "migration ok";
    }

    public function readModels(Request $request){
        $files = File::glob(app()->path()."/Models/BasicModels/*.*" );
        $files = str_replace([app()->path()."/Models/BasicModels/",".php"],["App\Models\BasicModels\\",""],implode(",", $files));

        return explode(",",$files);
    }

    public function readModelsOne(Request $request, $tableName=null){
        $basic = File::get(app()->path()."/Models/BasicModels/$tableName.php");
        $file = File::get(app()->path()."/Models/CustomModels/$tableName.php");
        if($request->script_only){
            return ['basic'=> $basic, 'custom'=>$file];
        }
        $className = "\\App\\Models\\CustomModels\\$tableName";   
        $class = new $className();
        return [
            'last_update' => $class->lastUpdate,
            'table' => $class->getTable(),
            'columns' => $class->columns,
            'text'=>$file
        ];
    }

    public function updateModelsOne(Request $request, $tableName=null){
        $file = File::put(app()->path()."/Models/CustomModels/$tableName.php", $request->text);        
        return "update Model OK";
    }

    public function createModels(Request $request, $tableName=null) {
        $this->prefixNamespace = "App\Models\BasicModels";
        $this->prefixNamespaceCustom = "App\Models\CustomModels";
        $this->hasMany = "
    public function __child()
    {
        return \$this->hasMany('App\Models\BasicModels\__child', '__cld_column', '__parent_column');
    }";
        $this->belongsTo ="
    public function __parent()
    {
        return \$this->belongsTo('App\Models\BasicModels\__parent', '__child_column', '__prt_column');
    }";
        $this->hasManyThrough ="
    public function __lastchildThrough()
    {
        return \$this->hasManyThrough('App\Models\BasicModels\__lastchild', 'App\Models\BasicModels\__child', '__prt_column', '__cld_column','id','id');
    }";
        $data = $this->getBasicModel();
        $dataCustom = $this->getCustomModel();
        $schema = $this->getFullTables(true);
        // return $schema;
        if($request->fresh){
            File::delete( File::glob("$this->modelsPath/CustomModels/*.*") );
            File::delete( File::glob("$this->modelsPath/BasicModels/*.*") );
        }
        $dataForJSON = [];
        $tableKhusus = $tableName;
        foreach($schema['tables'] as $table)
        {            
            $table = (object)$table;
            $tableName = $table->table;
            $cfg = (array)$table->config;
            $configKeys = array_keys($cfg);
            foreach($configKeys as $key){
                if( !is_array( $cfg[$key] ) ){
                    if( $cfg[$key] == 'all'){
                        $cfg[$key] = $table->columns;
                    }elseif( $cfg[$key] == 'none'){
                        $cfg[$key] = [];
                    }
                }
                if (strpos($key, '!') !== false) {
                    $cfg[ str_replace("!", "", $key) ] = array_values( array_diff($table->columns,$cfg[$key]) ) ;
                }
            }      
            $dataForJSON[] = [
                "model" => $tableName,
                "fullColumns" =>$table->fullColumns,
                "columns" => $table->columns,
                "config" =>[
                    'guarded'   => isset($cfg['guarded'])?$cfg['guarded']:['id'], 
                    'hidden'    => isset($cfg['hidden'])?$cfg['hidden']:[], 
                    'required'  => isset($cfg['required'])? $cfg['required']:[], 
                    'createable'=> isset($cfg['createable'])? $cfg['createable']:array_values(array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )),
                    'updateable'=> isset($cfg['updateable'])? $cfg['updateable']:array_values(array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )),
                    'searchable'=> isset($cfg['searchable'])? $cfg['searchable']:array_values(array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )),
                    'deleteable'=> isset($cfg['deleteable'])?($cfg['deleteable']=="false"?false:true):true,
                    'extendable'=> isset($cfg['extendable'])?($cfg['extendable']=="false"?false:true):false,
                    'casts'     => isset($cfg['casts'])?$cfg['casts']:['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y']
                ]
            ];
            // if( $tableKhusus!==null && $table->table!==$tableKhusus ){continue;}
            // File::put( $directory."/processlist.json", json_encode($oldValue, JSON_PRETTY_PRINT));
            // File::delete( \File::glob("$directory/$req->oldName*.*" ));
            // File::deleteDirectory( database_path("migrations/$req->oldName" ) );
            // File::delete( \File::glob( resource_path("views/generator/$req->oldName.php" )) );
            $paste = str_replace([
                "__namespace","__class","__table","__columns", "__required", "__lastupdate"
            ],[
                $this->prefixNamespace, $tableName, $tableName, '["'.implode('","',$table->columns).'"]', $table->required, date('d/m/Y H:i:s')
            ],$data);
            if($request->rewrite_custom || !File::exists( "$this->modelsPath/CustomModels/$tableName.php" ) ){
                $pasteCustom = str_replace([
                    "__namespace","__class","__basicClass", "__lastupdate"
                ],[
                    $this->prefixNamespaceCustom, $tableName, "\\$this->prefixNamespace\\$tableName", date('d/m/Y H:i:s')
                ],$dataCustom);
            }
            $hasMany = "";
            $belongsTo = "";
            $hasManyThrough = "";
            $joins = [];
            $details = [];
            $heirs = [];
            $detailsChild = [];
            $detailsHeirs = [];
            if(in_array($tableName, array_keys($schema['foreignkeys']) )){
                foreach($schema['foreignkeys'][$tableName] as $fk){
                    $fk=(object)$fk;
                    if($fk->cascade){
                        $details[] = $fk->child;
                    }else{
                        $heirs[] = $fk->child;
                    }
                    $hasMany.=str_replace([
                        "__child", "__cld_column","__parent_column"
                    ],[
                        $fk->child, $fk->child_column, $fk->parent_column
                    ],$this->hasMany);
                    if( in_array($fk->child, array_keys($schema['foreignkeys']) )){                        
                        foreach($schema['foreignkeys'][$fk->child] as $fKey){
                            $fKey=(object)$fKey;
                            if($fKey->cascade){
                                $detailsChild[] = $fKey->child;
                            }else{
                                $detailsHeirs[] = $fKey->child;
                            }
                            $hasManyThrough.=str_replace([
                                "__lastchild", "__child","__prt_column","__cld_column"
                            ],[
                                $fKey->child, $fKey->parent, $fk->child_column ,$fKey->child_column
                            ],$this->hasManyThrough);
                        }
                    }
                }
                
                // $paste = str_replace("__hasManyThrough",$hasManyThrough,$paste);
                // $paste = str_replace("__hasMany",$hasMany,$paste);
                
                $paste = str_replace("__hasManyThrough","",$paste);
                $paste = str_replace("__hasMany","",$paste);
            }
            $paste = str_replace("__detailsHeirs", count($detailsHeirs)==0?"[]":'["'.implode('","',$detailsHeirs).'"]' ,$paste);
            $paste = str_replace("__heirs", count($heirs)==0?"[]":'["'.implode('","',$heirs).'"]' ,$paste);
            $paste = str_replace("__detailsChild", count($detailsChild)==0?"[]":'["'.implode('","',$detailsChild).'"]' ,$paste);
            $paste = str_replace("__details", count($details)==0?"[]":'["'.implode('","',$details).'"]' ,$paste);
            $paste = str_replace("__hasManyThrough","",$paste);
            $paste = str_replace("__hasMany","",$paste);
            if(in_array($tableName, array_keys($schema['children']) )){
                foreach($schema['children'][$tableName] as $fk){
                    $fk=(object)$fk;
                    $joins[] = "$fk->parent.$fk->parent_column=$fk->child.$fk->child_column";
                    $belongsTo.=str_replace([
                        "__parent", "__child_column","__prt_column"
                    ],[
                        $fk->parent, $fk->child_column, $fk->parent_column
                    ],$this->belongsTo);
                }
                $paste = str_replace("__belongsTo","",$paste);
                $paste = str_replace("__joins", '["'.implode('","',$joins).'"]' ,$paste);
            }else{
                $paste = str_replace("__belongsTo","",$paste);
                $paste = str_replace("__joins", '[]' ,$paste);
            }
            
            $paste = str_replace("__belongsTo","",$paste); //mematikan belongsTo
            $paste = str_replace([
                "__config_guarded", "__config_hidden","__config_required","__config_createable",
                "__config_updateable","__config_searchable","__config_deleteable","__config_extendable",
                "__config_cascade","__config_casts", "__config_unique"
            ], [
                isset($cfg['guarded'])? (!is_array($cfg['guarded'])? "'".$cfg['guarded']."'":'["'.implode('","',$cfg['guarded']).'"]'):"['id']", 
                isset($cfg['hidden'])? (!is_array($cfg['hidden'])? "'".$cfg['hidden']."'":'["'.implode('","',$cfg['hidden']).'"]'):"[]", 
                isset($cfg['required'])? (!is_array($cfg['required'])? "'".$cfg['required']."'":'["'.implode('","',$cfg['required']).'"]'):$table->required, 
                isset($cfg['createable'])? (!is_array($cfg['createable'])? "'".$cfg['createable']."'":'["'.implode('","',$cfg['createable']).'"]'):'["'.implode('","',array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )).'"]',
                isset($cfg['updateable'])? (!is_array($cfg['updateable'])? "'".$cfg['updateable']."'":'["'.implode('","',$cfg['updateable']).'"]'):'["'.implode('","',array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )).'"]',
                isset($cfg['searchable'])? (!is_array($cfg['searchable'])? "'".$cfg['searchable']."'":'["'.implode('","',$cfg['searchable']).'"]'):'["'.implode('","',array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )).'"]',
                isset($cfg['deleteable'])?$cfg['deleteable']:"true",
                isset($cfg['extendable'])?$cfg['extendable']:"false",
                isset($cfg['cascade'])?$cfg['cascade']:"true",
                isset($cfg['casts'])?str_replace(["{","}",'":'],["[","\t]",'"=>'],json_encode($cfg['casts'], JSON_PRETTY_PRINT)):"['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y']",
                str_replace(["{","}",'":'],["[","\t]",'"=>'],json_encode($table->uniques, JSON_PRETTY_PRINT))
            ],$paste);
            $customfunction="";//__customfunction__
            if(isset($cfg['autocreate'])){
                foreach($cfg['autocreate'] as $kolom => $rumus){
                    if( strpos($rumus, '}') !== false ){
                        $customfunction.="public function create_$kolom(\$request){\n\t\treturn "
                            .str_replace(["auth:","request:","{","}"],["\Auth::user()->","\$request->","",""],$rumus)
                            .";\n\t}\n";
                    }
                }
            }
            if(isset($cfg['autoupdate'])){
                foreach($cfg['autoupdate'] as $kolom => $rumus){
                    if( strpos($rumus, '}') !== false ){
                        $customfunction.="public function update_$kolom(\$request){\n\t\treturn "
                            .str_replace(["auth:","request:","{","}"],["\Auth::user()->","\$request->","",""],$rumus)
                            .";\n\t}\n";
                    }
                }
            }
            $paste = str_replace("__customfunction__",$customfunction,$paste);
            if( ! File::exists( "$this->modelsPath/BasicModels/$tableName.php") ){
                File::delete("$this->modelsPath/BasicModels/$tableName.php" );
            }
            File::put( "$this->modelsPath/BasicModels/$tableName.php",$paste);
            if( $tableKhusus!==null && $table->table!==$tableKhusus ){continue;}
            if($request->rewrite_custom || !File::exists( "$this->modelsPath/CustomModels/$tableName.php" ) ){
                $pasteCustom = str_replace([
                    "__defaults","__autocreate","__autoupdate","__readers","__writers"
                ],[
                    isset($table->values)?str_replace(["{","}",'":',"\\","\n"," "],["[","]",'"=>',"","",""],json_encode($table->values, JSON_PRETTY_PRINT)):"[]",
                    isset($cfg['autocreate'])?str_replace(["{","}",'":',"\\","\n"," "],["[","]",'"=>',"","",""],json_encode($cfg['autocreate'], JSON_PRETTY_PRINT)):"[]",
                    isset($cfg['autoupdate'])?str_replace(["{","}",'":',"\\","\n"," "],["[","]",'"=>',"","",""],json_encode($cfg['autoupdate'], JSON_PRETTY_PRINT)):"[]",
                    isset($cfg['readers'])?str_replace(["{","}",'":',"\\","\n"," "],["[","]",'"=>',"","",""],json_encode($cfg['readers'], JSON_PRETTY_PRINT)):"[]",
                    isset($cfg['writers'])?str_replace(["{","}",'":',"\\","\n"," "],["[","]",'"=>',"","",""],json_encode($cfg['writers'], JSON_PRETTY_PRINT)):"[]"
                ],$pasteCustom);
                File::put( "$this->modelsPath/CustomModels/$tableName.php",$pasteCustom);
            }
        }
        File::put( base_path('public/models.json'), json_encode($dataForJSON, JSON_PRETTY_PRINT) );
        return "Database to Models OK";
    }

    public function mail(Request $request)
    {
        Mail::to($request->email)->send(new \App\Mails\SendMailable($request->name));        
        return 'Email was sent';
    }
    public function makeTrigger(Request $request, $tableName=null){
        $exist = PLSQL::table($tableName);
        $data = PLSQL::table($tableName);        
        if($request->time == 'after'){
            $exist = $exist->after($request->event);
            $data = $data->after($request->event);
        }else{
            $exist = $exist->before($request->event);
            $data  = $data->before($request->event);
        }
        $exist->drop();
        if($request->isMethod("delete")){
            return "delete trigger $tableName $request->time $request->event OK";
        }
        $command=$request->script;
        $data->script($command)->create();
        return "create/update trigger $tableName $request->time $request->event OK";
    }

    private function getDirContents($dir, &$results = array()){
        $files = scandir($dir);
        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                if (strpos($path, '.php') !== false && count(explode("_",$path))>3) {
                    $path = str_replace($dir,"",$path);
                    $stringku = implode("_",array_slice(explode("_",$path), 4));   
                    $results[] = $stringku;
                }
            } else if($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
                if (strpos($path, '.php') !== false && count(explode("_",$path))>3) {
                    $path = str_replace($dir,"",$path);
                    $stringku = implode("_",array_slice(explode("_",$path), 4));   
                    $results[] = $stringku;
                }
            }
        }
        return $results;
    }
    
    private function getDirFullContents($dir, &$results = array()){
        $files = scandir($dir);
        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                if (strpos($path, '.php') !== false && count(explode("_",$path))>3) { 
                    $results[] = $path;
                }
            } else if($value != "." && $value != "..") {
                $this->getDirFullContents($path, $results);
                if (strpos($path, '.php') !== false && count(explode("_",$path))>3) {
                    $results[] = $path;
                }
            }
        }
        return $results;
    }

    public function readMigrations(Request $req, $table=null){
        
        if($table!=null){
            $data = $this->getDirFullContents( base_path('database/migrations') );
            $data = array_filter($data,function($file)use ($table){
                if(strpos("$file.php", "$table.php")!==false){
                    return $file;
                }
            });
            if(count($data)==0){
                return response()->json("migration file [$table] tidak ada",400);
            }
            return File::get( array_values($data)[0] );
        }else{
            $data = $this->getDirContents( base_path('database/migrations') );            
            $schemaManager = DB::getDoctrineSchemaManager();
            $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $tables = $schemaManager->listTables();
            $arrayTables = [];
            foreach ($tables as $table) {
                $arrayTables[] =  $table->getName();
            }
            $views = $schemaManager->listViews();
            foreach ($views as $view) {
                $arrayTables[] =  str_replace("public.","",$view->getName() );
            }
            $models = [];
            foreach($data as $file){
                if($file==""){continue;};
                $stringClass = str_replace([".php","create_","_table"],["","",""], $file);
                $modelCandidate = "\App\Models\BasicModels\\$stringClass";
                $models[] =[
                    "file" => $file,
                    "model"=> class_exists( $modelCandidate )?true:false,
                    "table"=> in_array($stringClass, $arrayTables)?true:false,
                    "alias"=>class_exists( $modelCandidate )?( isset((new $modelCandidate )->alias)?true:false ):false
                ];
            }
            return $models;
        }
    }

    public function doMigrate(Request $req, $table=null){
        File::delete(glob(base_path('database/migrations')."/*.*"));
        $data = $this->getDirFullContents( base_path('database/migrations') );
        $data = array_filter($data,function($file)use ($table){
            if(strpos("$file.php", "$table.php")!==false){
                return $file;
            }
        });
        if(count($data)==0){
            return response()->json("migration file [$table] tidak ada",400);
        }
        if(Schema::hasTable($table)){
            Schema::dropIfExists($table);
        }else{
            DB::unprepared("DROP VIEW IF EXISTS $table;");
        }
        // return File::get( array_values($data)[0] );
        try{
            $file = base_path( 'database/migrations/projects')."/0_0_0_0_"."$table.php";
            if( strpos($table,"default_")!==FALSE || strpos($table,"oauth_")!==FALSE ){
                $file = base_path( 'database/migrations/__defaults')."/0000_00_00_000000_$table.php";
            }
            File::put(base_path('database/migrations')."/0_0_0_0_"."$table.php",File::get( $file ));
            $exitCode = Artisan::call('migrate:refresh', [
                '--force' => true,
            ]);
            // $req->rewrite_custom = $req->rewrite_custom;
            $this->createModels( $req, str_replace(["create_","_table"],["",""],$table) );
            File::delete(base_path('database/migrations')."/0_0_0_0_"."$table.php");
            if($table=="default_users"){
                $hasher = app()->make('hash');
                User::create([
                        'name' => "trial",'email' => "trial@trial.trial", 'username'=>"trial",'password' => $hasher->make("trial")
                ]);
            }
        }catch(Exception $e){
            File::delete(glob(base_path('database/migrations')."/*.*"));
            if( File::exists( base_path('database/migrations')."/0_0_0_0_"."$table.php" ) ){
                File::delete(base_path('database/migrations')."/0_0_0_0_"."$table.php");
            }
            return response()->json(["error"=>$e->getMessage()], 422);
        }
        return "database migration ok, $table model recreated successfully";
    }
    public function deleteAll(Request $req, $table){
        if(!$req->password){
            return response()->json("unauthorized",401);
        }
        if($req->password!=="jajanenak"){
            return response()->json("unauthorized password salah",401);
        }
        File::delete(glob(base_path('database/migrations')."/*.*"));
        $data = $this->getDirFullContents( base_path('database/migrations') );
        $data = array_filter($data,function($file)use ($table){
            if(strpos("$file.php", "$table.php")!==false){
                return $file;
            }
        });
        if(count($data)==0){
            return response()->json("migration file [$table] tidak ada",400);
        }
        try{
            if(strpos( array_values($data)[0] , "/projects")!==false){
                if(strpos($table,"_after_")!==false || strpos($table,"_before_")!==false){
                    $samaran = str_replace(['_after_','_before_'],["_timing_","_timing_"],$table);
                    $tableName = explode("_timing_",$samaran)[0];
                    DB::unprepared("                    
                        DROP TRIGGER IF EXISTS $table ON $tableName;
                        DROP FUNCTION IF EXISTS fungsi_"."$table();
                    ");
                }elseif(Schema::hasTable($table)){
                    Schema::dropIfExists($table);
                }else{
                    DB::unprepared("DROP VIEW IF EXISTS $table;");
                }
                File::delete( "$this->modelsPath/CustomModels/$table.php" );
                File::delete( "$this->modelsPath/BasicModels/$table.php" );
            }
            if( count(array_values($data))>1 ){
                foreach(array_values($data) as $file){
                    if( !(strpos( $file , "/projects")!==false) ){
                        File::delete( $file );
                    }
                }
            }else{
                File::delete( array_values($data)[0] );
            }
        }catch(Exception $e){
            return response()->json($e->getMessage(),400);
        }
        return response()->json("Model, Migrations, Table, Trigger terhapus semua");
    }
    public function editMigrations(Request $req, $table=null){
        if($table!=null){
            $data = $this->getDirFullContents( base_path('database/migrations/projects') );
            $data = array_filter($data,function($file)use ($table){
                if(strpos("$file.php", "$table.php")!==false){
                    return $file;
                }
            });
            if( count($data) <1){
                return response()->json("maaf nama model $table tidak ada", 400);
            }
            $file = File::put( base_path('database/migrations/projects')."/0_0_0_0_"."$table.php" , $req->text); 
            return "update Migrations OK [".count($data)."]";
        }
        $data = $this->getDirContents( base_path('database/migrations') );
        if(strpos("x".$req->modul, "alias ")!==false && count(explode(" ",$req->modul))==3 ){
            $modul = explode(" ",  $req->modul)[2];
            $tableSrc   = explode(" ",  $req->modul)[1];
            if(!in_array("$tableSrc.php", $data)){
                return response()->json("maaf nama model $tableSrc tidak ada", 400);
            }
            if(in_array("$modul.php", $data)){
                return response()->json("maaf nama model $modul telah terpakai", 400);
            }
            if( !File::exists( "$this->modelsPath/BasicModels/$tableSrc.php") ){
                return response()->json("maaf model $tableSrc belum termigrate, silahkan dimigrate dahulu", 400);    
            }
            $stringModelSrc = File::get("$this->modelsPath/BasicModels/$tableSrc.php");
            $stringModelSrc = str_replace( [
                "class $tableSrc",
                "public \$lastUpdate"
            ],[
                "class $modul",
                "public \$alias=true;\npublic \$lastUpdate"
            ],$stringModelSrc);
            File::put( "$this->modelsPath/BasicModels/$modul.php",$stringModelSrc);
            File::put( "$this->modelsPath/CustomModels/$modul.php", str_replace($tableSrc,$modul,File::get("$this->modelsPath/CustomModels/$tableSrc.php")) );
            File::put(base_path('database/migrations/projects')."/0_0_0_0_"."$modul.php", str_replace([
                "__class__","__table__",
            ],[
                str_replace("_","",$modul),$tableSrc
            ],File::get( base_path("templates/migrationalias.stub") ) ));
            return response()->json("pembuatan file migration Alias OK");
        }
        if(strpos("x".$req->modul, "view ")!==false && count(explode(" ",$req->modul))==2 ){
            $modul   = explode(" ",  $req->modul)[1];
            if(in_array("$modul.php", $data)){
                return response()->json("maaf nama model $modul telah terpakai", 400);
            }
            
            File::put(base_path('database/migrations/projects')."/0_0_0_0_"."$modul.php", str_replace([
                "__class__","__table__",
            ],[
                str_replace("_","",$modul),$modul
            ],File::get( base_path("templates/migrationview.stub") ) ));
            return response()->json("pembuatan file migration View OK");
        }

        if(strpos("x".$req->modul, "trigger ")!==false && count(explode(" ",$req->modul))==4 ){
            $modul   = explode(" ",  $req->modul)[1];
            $time    = explode(" ",  $req->modul)[2];
            $action    = explode(" ",  $req->modul)[3];
            
            if(!in_array("$modul.php", $data)){
                return response()->json("maaf model $modul sebagai induk table tidak ada", 400);
            }
            if( !File::exists( "$this->modelsPath/BasicModels/$modul.php") ){
                return response()->json("maaf table $modul belum termigrate, silahkan dimigrate dahulu", 400);    
            }
            if(in_array("$modul"."_$time"."_$action.php", $data)){
                return response()->json("maaf trigger $modul telah terpakai", 400);
            }
            
            File::put(base_path('database/migrations/projects')."/0_0_0_0_$modul"."_$time"."_$action.php", str_replace([
                "__class__","__table__","__time__","__action__"
            ],[
                str_replace("_","",$modul)."$time"."$action",$modul,$time,$action
            ],File::get( base_path("templates/migrationtrigger.stub") ) ));
            return response()->json("pembuatan file migration Trigger OK");
        }

        $modul = strtolower(str_replace(" ","_",$req->modul));
        if(in_array("$modul.php", $data)){
            return response()->json("maaf nama model $modul telah terpakai", 400);
        }
        
        File::put(base_path('database/migrations/projects')."/0_0_0_0_"."$modul.php", str_replace([
            "__class__","__table__",
        ],[
            str_replace("_","",$modul),$modul
        ],File::get( base_path("templates/migration.stub") ) ));
        
        return response()->json("pembuatan file migration OK");
    }
}

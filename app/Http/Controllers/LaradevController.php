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
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use DateTime;
use Validator;
use Exception;
use Carbon\Carbon;

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
        if( ! File::exists(base_path("database/migrations/alters")) ){
            File::makeDirectory( base_path("database/migrations/alters"), 493, true);
        }
    }

    public function cleanProject()
    {
        if( ! File::exists($this->modelsPath."/BasicModels") ){
            File::delete( $this->modelsPath."/BasicModels", 493, true);
        }
        if( ! File::exists($this->modelsPath."/CustomModels") ){
            File::delete( $this->modelsPath."/CustomModels", 493, true);
        }
        if( ! File::exists(base_path("database/migrations/projects")) ){
            File::delete( base_path("database/migrations/projects"), 493, true);
        }
        if( ! File::exists(base_path("database/migrations/alters")) ){
            File::delete( base_path("database/migrations/alters"), 493, true);
        }
    }

    private function getConnection($conn)
    {
        $conn = (object)$conn;
        $defaultConn = config('database.connections.flying'.$conn->driver);
        $newConn     = array_merge($defaultConn, (array)$conn);
        config(['database.connections.flying'.$conn->driver=>$newConn]);
        return DB::connection('flying'.$conn->driver);
    }

    public function databaseCheck(Request $request)
    {
        if($request->has('db_autocreate') && $request->db_autocreate=="true"){
            return $this->databaseCreateLocal($request);
        }
        try {
            if($request->has('host') ){
                // driver,host,port,username,password,database
                if($request->driver=="pgsql" && !isset($request->database) ){                
                    $connection->database = "postgres";
                }else{
                    $connection = $request->all();
                }
                $conn = $this->getConnection($connection);
                $dbname = $conn->getDatabaseName();
                $conn=$conn->getDoctrineSchemaManager();
            }else{
                $conn=DB::getDoctrineSchemaManager();
                $dbname = DB::getDatabaseName();
            }
        } catch (Exception $e) {
            return response()->json(['status'=>$e->getMessage()], 422);
        }
        return response()->json("Database ".$dbname." Exists");
    }
    public function databaseCreateLocal($request,$connection=null)
    {
        $dbname = "";
        try {
            if($connection!=null){
                $dbname = $connection['database'];
                $conn = $this->getConnection($connection);
                $conn=$conn->getDoctrineSchemaManager();
            }else{
                $dbname = env("DB_DATABASE","");
                $conn=DB::getDoctrineSchemaManager();
            }
        } catch (Exception $e) {
            try {
                if($connection!=null){
                    $dbname = $connection['database'];
                    if($connection['database']=='pgsql'){
                        $connection['database']='postgres';
                    }
                    $conn = $this->getConnection($connection);
                }else{
                    $dbname = env("DB_DATABASE","");
                    $default = [
                        'driver' => env('DB_CONNECTION', '127.0.0.1'),
                        'host' => env('DB_HOST', '127.0.0.1'),
                        'port' => env('DB_PORT', '5432'),
                        'username' => env('DB_USERNAME', 'forge'),
                        'password' => env('DB_PASSWORD', '')
                    ];
                    if(env('DB_CONNECTION', 'mysql')=='pgsql'){
                        $default['database']='postgres';
                    }
                    $conn = $this->getConnection($default);
                }
                $conn=$conn->getDoctrineSchemaManager();
                $conn->createDatabase($dbname);
                return response()->json("database $dbname created successfully");
            }catch(Exception $e2){
                return response()->json($e2->getMessage(), 422);
            }           
        }
        $migrate="no";
        try{
            if($request->has('db_migrate') && $request->db_migrate=="true"){
                Artisan::call("migrate:".($request->db_fresh && $request->db_fresh=="true"?"fresh":"refresh"),[
                    "--path"=>"database/migrations/__defaults" , "--force"=>true
                ]
                );
                if($request->has('db_seed') && $request->db_seed=="true"){
                    Artisan::call("db:seed");
                }
                if($request->has('db_passport') && $request->db_passport=="true"){
                    Artisan::call("passport:install");
                }
                $migrate="yes";
            }
        }catch(Exception $e){
            return response()->json($e->getMessage(), 422);
        }
        return response()->json("Database ".$dbname." is already created before,migrate = $migrate");
    }
    private function getBasicModel(){
        return File::get( base_path("templates/basicModel.stub") );
    }
    private function getCustomModel(){
        return File::get( base_path("templates/customModel.stub") );
    }
    private function getMigrationFile()
    {
        $template = "migration";
        return File::get( base_path("templates/$template.stub") );
    }
    private function getAlterFile()
    {
        $template = "migrationAlter";
        return File::get( base_path("templates/$template.stub") );
    }
    private function getFullTables($toModel=false,$tableKhusus=null)
    {
        try{
            $schemaManager = DB::getDoctrineSchemaManager();
            $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $data = $schemaManager->listTableNames();
            $tables = [];
            $fks = [];
            $cds = [];
            foreach ($data as $table) {
                $table = $schemaManager->listTableDetails($table);
                if( strpos($table->getName(),"pg_catalog.")!==false || strpos($table->getName(),"information_schema.")!==false || $table->getName()==='spatial_ref_sys' ){
                    continue;
                }
                $foreignKeys = [];
                $required = [];
                $defaults = [];
                $unique = [];
                $rawForeignKeys = $table->getForeignKeys();
                $indexes = $table->getIndexes();
                foreach ($rawForeignKeys as $fk) {
                    $fktemp= [
                        "child"=> $fk->getLocalTableName(), "child_column"=>implode($fk->getLocalColumns()),
                        "parent"=> $fk->getForeignTableName(), "parent_column"=>implode($fk->getForeignColumns()), "cascade"=>true,
                        "real"=>false, "physical"=>true
                    ];
                    $foreignKeys[]=$fktemp;
                    $fks[$fk->getForeignTableName()][] = $fktemp;
                    $cds[$fk->getLocalTableName()][]   = $fktemp;
                }
                $columns = [];
                $columnNames = [];
                $rulesArr = [];
                foreach ($table->getColumns() as $column) {
                    foreach($indexes as $key=>$index){
                        if(in_array($column->getName(), $index->getColumns()) && !$index->isPrimary() && $index->isUnique()){
                            $unique[$column->getName()] = "unique:".(  count(explode('.', $table->getName() ) )>1? env("DB_CONNECTION",'').".":"" ).$table->getName().",".$column->getName();
                        }
                    }
                    
                    if( !in_array($column->getName(), ["id","created_at","updated_at"]) &&  $column->getNotnull()){
                        $comment    = $column->getComment();
                        if($comment!=null && $comment!=""){
                            $comment = json_decode($comment);
                            if( isset($comment->required) && $comment->required!="false" ){
                                $required[]=$column->getName();
                            }else if(!isset($comment->required) ){
                                $required[]=$column->getName();
                            }
                        }else{                            
                            $required[]=$column->getName();
                        }
                    }
                    $comment    = $column->getComment();
                    $columnName = $column->getName();
                    $rule = null;

                    if($comment!=null && $comment!=""){
                        $comment = json_decode($comment);
                        if( isset($comment->fk) && $comment->fk!="false" ){
                            $fk = $comment->fk;
                            $arrayFK = explode(".", $fk);                            
                            if(end($arrayFK)=="id"){
                                $fktemp= [
                                    "child"=> $table->getName(), "child_column"=>$column->getName(),
                                    "parent"=> str_replace(".".end($arrayFK),"",$fk), "parent_column"=>end($arrayFK), "cascade"=>true,
                                    "real" => true, "physical"=>false
                                ];
                                $foreignKeys[]=$fktemp;
                                $fks[ str_replace(".".end($arrayFK),"",$fk)][] = $fktemp;
                                $cds[ $table->getName() ][]   = $fktemp;
                                $column->setUnsigned(true);
                            }
                        }
                        if( @$comment->src ){
                            $fk = $comment->src;
                            $arrayFK = explode(".", $fk);
                            if(end($arrayFK)=="id"){
                                $fktemp= [
                                    "child"=> $table->getName(), "child_column"=>$column->getName(),
                                    "parent"=> str_replace(".".end($arrayFK),"",$fk), "parent_column"=> end($arrayFK), "cascade"=>false,
                                    "real" =>false, "physical"=>false
                                ];
                                $foreignKeys[] = $fktemp;
                                $fks[ str_replace(".".end($arrayFK),"",$fk)][] = $fktemp;
                                $cds[ $table->getName() ][]   = $fktemp;
                                $column->setUnsigned(true);
                            }
                        }
                        $rule =  @$comment->rules;
                        if($rule){
                            $rulesArr[$columnName] = $rule;
                        }

                        if( isset($comment->required) && $comment->required!="false" ){
                            $isRequired = $comment->required;
                            if($isRequired){
                                $required[]=$column->getName();
                            }
                        }
                        if( isset($comment->value)){
                            $defaults[$column->getName()] = $comment->value;
                        }
                    }
                    // elseif(strpos($columnName, '_id') !== false){
                    //     $fktemp= [
                    //         "child"=> $table->getName(), "child_column"=>$columnName,
                    //         "parent"=> str_replace("_id","",$columnName), "parent_column"=> "id" , "cascade"=>true
                    //     ];
                    //     $foreignKeys[]=$fktemp;
                    //     $fks[ str_replace("_id","",$columnName)][] = $fktemp;
                    //     $cds[ $table->getName() ][]   = $fktemp;                        
                    // }
                    
                    $columnNames[] = $column->getName();
                    $columns[] = [
                        "name"=>$column->getName(),
                        "type"=> $column->getType()->getName(),
                        "length"=> $column->getLength(),
                        "unsigned"=> $column->getUnsigned(),
                        "precision"=> $column->getPrecision(),
                        "scale"=> $column->getScale(),
                        "fixed"=> $column->getFixed(),
                        "default"=> $column->getDefault(),
                        "comment"=> $column->getComment(),
                        "nullable"=> $column->getNotnull(),
                        "rules"=>$rule
                    ];
                }
                $fullColumns = $columns;
                if($toModel){
                    $columns = $columnNames;
                    // $required = count($required)>0?'["'.implode('","',$required).'"]':"[]";
                }
                $tables[]=[
                    "table" => $table->getName(),
                    "fullColumns" => $fullColumns,
                    "config" => $table->getComment()?json_decode($table->getComment()):null,
                    "columns"=>$columns,
                    "values"=>$defaults,
                    "foreign_keys" => $foreignKeys,
                    "required" => $required,
                    "rules" => $rulesArr,
                    "uniques" => $unique,
                    'triggers'=>\App\Helpers\DBS::getTriggers($table->getName()),
                    'is_view'=>false
                ];
            }
            $views = $schemaManager->listViews();
            foreach($views as $view){
                
                if( strpos($view->getName(),"pg_catalog.")!==false || strpos($view->getName(),"information_schema.")!==false ){
                    continue;
                }
                $columnNames = \Schema::getColumnListing(str_replace('public.','',$view->getName()));
                $columns     = [];
                foreach($columnNames as $key => $column){
                    $columns[] = [
                        "name"=>$column,
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
                    "rules" => "[]",
                    "uniques" => [],
                    'triggers'=>[],
                    'is_view'=>true
                ];
            }
        }catch(Exception $e){
            trigger_error($e->getMessage());
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
                'LOCALE' => 'EN',
                'QUEUE_CONNECTION' => 'database',
                'APP_URL' => 'http://localhost',
                'DB_CONNECTION' => 'mysql',
                'DB_DATABASE' => 'trial2',
                'SERVERSTATUS' => 'OPEN',
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
                'LOG_CHANNEL' => '777',
                'SINGLE_LOGIN' => 'true',
                'FORMAT_DATE_FRONTEND' => 'd/m/Y',
                'FIREBASE_KEY' => 'xxx',
                'GIT_ENABLE'=>'false',
                'GIT_PUSH_START'=>'16:00',
                'GIT_URL'=>'https://larahan:larahansuperuser2019@gitlab.com/exampleproject',            
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
        if($request->has('details') ){
            return $this->getFullTables();
        }
        $schemaManager = DB::getDoctrineSchemaManager();
        $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $tables = $schemaManager->listTableNames();
        $tableNames = [];
        foreach ($tables as $table) {
            $table = $schemaManager->listTableDetails($table);
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
            if(strpos("$file.php", "0_0_0_0_$tableName.php")!==false){
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
        $realTableName = getBasic($tableName)->getTable();
        $dstTableName = $request->name;
        if(strpos($realTableName, ".")!==false){
            $schema = explode(".", $realTableName)[0];
            $dstTableName = "$schema.$dstTableName";
        }
        if(Schema::hasTable($realTableName)){
            Schema::rename($realTableName, $request->name);
        }else{ //misal View Table
            Schema::rename($realTableName, $request->name);
        }
        if($request->has('models')){
            File::put( "$this->modelsPath/CustomModels/$request->name.php", 
                str_replace( $tableName,$request->name,File::get( "$this->modelsPath/CustomModels/$tableName.php" ) )
            );
            File::put( "$this->modelsPath/BasicModels/$request->name.php",
                str_replace($tableName,$request->name, File::get( "$this->modelsPath/BasicModels/$tableName.php" ) )
            );
            File::put(base_path('database/migrations/projects')."/0_0_0_0_"."$request->name.php", str_replace([
                str_replace("_","",$tableName)." ","'$tableName'","\"$tableName\"",
            ],[
                str_replace("_","",$request->name)." ","'$request->name'", "\"$request->name\""
            ],File::get( base_path('database/migrations/projects')."/0_0_0_0_"."$tableName.php" ) ));
            $alterPath = base_path("database/migrations/alters/0_0_0_0_$tableName.php");
            if(File::exists( $alterPath )){
                File::put(base_path('database/migrations/alters')."/0_0_0_0_"."$request->name.php", str_replace([
                    str_replace("_","",$tableName)." ","'$tableName'","\"$tableName\"",
                ],[
                    str_replace("_","",$request->name)." ","'$request->name'", "\"$request->name\""
                ],File::get( base_path('database/migrations/alters')."/0_0_0_0_"."$tableName.php" ) ));
            }
            File::delete( "$this->modelsPath/CustomModels/$tableName.php" );
            File::delete( "$this->modelsPath/BasicModels/$tableName.php" );
            File::delete( base_path('database/migrations/projects')."/0_0_0_0_"."$tableName.php" );
        }
        $this->createModels( $request, 'abcdefghijklmnopq' );
        Cache::forget('migration-list');
        if(env('GIT_ENABLE', false)){
            $this->git_push(".","<RENAME $tableName -> $request->name>");
        }
        return "rename table OK";
    }
    public function deleteTables(Request $request, $tableName){
        Schema::dropIfExists($tableName);
        if($request->has('models')){
            $customModel = "$this->modelsPath/CustomModels/$tableName.php";
            $basicModel = "$this->modelsPath/BasicModels/$tableName.php";
            File::delete( $customModel );
            File::delete( $basicModel );
            if(env('GIT_ENABLE', false)){ 
                $this->git_push(".","<DROP $tableName>");       
            }
        }
        return "delete table OK";
    }
    public function migrateDefault(Request $request){ 
        Artisan::call("migrate:".($request->fresh?"fresh":"refresh"),[
                "--path"=>"database/migrations/__defaults" , "--force"=>true
            ]
        );
        if($request->has('seed')){
            Artisan::call("db:seed");
        }
        if($request->has('passport')){
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
        
        $className = "\\App\\Models\\CustomModels\\$tableName";   
        $class = new $className();
        if(isset($class->password)){
            if( !isset($request->password)){
                return response()->json("nopassword",401);
            }elseif($request->password!==$class->password){
                return response()->json("nopassword",401);
            }
        }
        if($request->has('basic')){
            return File::get(app()->path()."/Models/BasicModels/$tableName.php");
        }
        if($request->has('custom')){
            return File::get(app()->path()."/Models/CustomModels/$tableName.php");
        }
        $basic = File::get(app()->path()."/Models/BasicModels/$tableName.php");
        $file = File::get(app()->path()."/Models/CustomModels/$tableName.php");
        if($request->has('script_only')){
            return ['basic'=> $basic, 'custom'=>$file];
        }
        return [
            'last_update' => $class->lastUpdate,
            'table' => $class->getTable(),
            'columns' => $class->columns,
            'text'=>$file
        ];
    }

    public function updateModelsOne(Request $request, $tableName=null){
        $disabled = explode(',', str_replace(" ","",ini_get('disable_functions')) );
        $canExec =  !in_array('exec', $disabled);
        $return = 0;
        if ( $canExec ){
            $tempFile="$this->modelsPath/CustomModels/$tableName"."_temp.php";
            try{
                File::put($tempFile,$request->text);
                $output=null;
                $return=null;
                $phpBin = env('PHPBIN','php');
                exec("$phpBin -l $tempFile", $output, $return);
            }catch(Exception $e){
                File::delete($tempFile);
                // $file = File::put(app()->path()."/Models/CustomModels/$tableName.php", $request->text);
                trigger_error($e->getMessage());
            }
            File::delete($tempFile);
        }else{
            $oldFile = File::get("$this->modelsPath/CustomModels/$tableName.php");
            if( File::exists( "$this->modelsPath/CustomModels/$tableName.php") ){
                File::delete("$this->modelsPath/CustomModels/$tableName.php" );
            }
            File::put( "$this->modelsPath/CustomModels/$tableName.php", $request->text );
            $className = "\\App\\Models\\CustomModels\\$tableName";
            try{
                $testedClass = new $className();
            }catch(\Throwable $e){
                File::put("$this->modelsPath/CustomModels/$tableName.php", $oldFile );
                return response()->json('Error: '.$e->getMessage(),422);
            }
        }
        if($return===0){

            $file = putFileDiff("$this->modelsPath/CustomModels/$tableName.php", $request->text );
            if(env('GIT_ENABLE', false)){ 
                $this->git_push(".","<SAVE MODEL $tableName>");       
            }
            devTrack( 'Update API', $tableName, $file );
            return "update Model OK";
        }else{
            return response()->json($output,422);
        };
    }
    public function getPhysicalForeignKeys(){
        $schema = $this->getFullTables(true);
        return (array)$schema;
    }
    public function setPhysicalForeignKeys(Request $request){
        $schema = $this->getFullTables(true);
        $tables = $schema['tables'];
        Schema::disableForeignKeyConstraints();
        if($request->has('drop') && $request->drop=='true'){
            foreach($schema['children'] as $child => $data){
                foreach($data as $ch){
                if($ch['physical']){
                        Schema::table($ch['child'], function (Blueprint $table)use($ch) {
                            $table->dropForeign( [  $ch['child_column']] );
                        });
                    }
                }
            }            
        }else{
            foreach($schema['children'] as $child => $data){
                foreach($data as $ch){
                    if($ch['physical']){
                        Schema::table($ch['child'], function (Blueprint $table)use($ch) {
                            $table->dropForeign( [  $ch['child_column']] );
                        });
                    }
                }
                foreach($data as $ch){
                    if(!Schema::hasTable($ch['parent'])){
                        continue;
                    }
                    if(!$ch['physical']){
                        $type="";
                        foreach($tables as $table){
                            if($table['table']==$ch['parent']){                                
                                foreach($table['fullColumns'] as $col){
                                    if($col['name'] == $ch['parent_column']){
                                        $type = strtolower($col['type']);
                                    }
                                }
                            }
                        }
                        if(strpos($type,"big")!==false){
                            $type="unsignedBigInteger";
                        }elseif(strpos($type,"medium")!==false){
                            $type="unsignedMediumInteger";
                        }elseif(strpos($type,"small")!==false){
                            $type="unsignedSmallInteger";
                        }elseif(strpos($type,"tiny")!==false){
                            $type="unsignedTinyInteger";
                        }else{
                            $type="unsignedInteger";
                        }
                        addFK:
                        try{
                            $updateArray = [];
                            $updateArray[$ch['child_column']] = "1";
                            DB::table($ch['child'])->where($ch['child_column'],null)->update($updateArray);
                            Schema::table($ch['child'], function (Blueprint $table)use($ch,$type) {
                                $table->$type($ch['child_column'])->nullable(false)->change();
                                $table->foreign($ch['child_column'])->references($ch['parent_column'])->on($ch['parent']);
                            });
                        }catch(Exception $e){
                            if(strpos($e->getMessage(),") is not present in table ")!==false ){
                                $string = $e->getMessage();
                                $string = explode(")=(",$string)[1];
                                $string = explode(') is not present in table "', $string);
                                $id = $string[0];
                                $table = explode('"',$string[1])[0];
                                $sample = (array)DB::table($table)->first();
                                foreach($sample as $key=>$val){
                                    if(!in_array($key,['id','created_at','updated_at']) && $sample[$key]!=null ){
                                        if (DateTime::createFromFormat('Y-m-d H:i:s', $val) !== FALSE) {
                                            continue;
                                        }else if( DateTime::createFromFormat('Y-m-d', $val) !== FALSE ){
                                            continue;
                                        }else if( strpos($key,"_id") !== FALSE ){
                                            continue;
                                        }
                                        $sample[$key] = $val.$id;
                                    }
                                }                                
                                // return response()->json(['id'=>$id, 'table'=>$table, 'string'=> $e->getMessage()],400);
                                $sample['id'] = $id;
                                DB::table($table)->insert( $sample);
                                goto addFK;
                            }else{
                                return response()->json($e->getMessage(),400);
                            }
                        }
                    }
                }
            }
        }
        Schema::enableForeignKeyConstraints();
        return "OK";
    }
    public function createModels(Request $request, $tableName=null) {
        $this->prefixNamespace = "App\Models\BasicModels";
        $this->prefixNamespaceCustom = "App\Models\CustomModels";
        $this->hasMany = "
    public function __child() :\HasMany
    {
        return \$this->hasMany('App\Models\BasicModels\__child', '__cld_column', '__parent_column');
    }";
        $this->belongsTo ="
    public function __relatedColumn() :\BelongsTo
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
        
        if($request->has('fresh') && $request->fresh=='true'){
            File::delete( File::glob("$this->modelsPath/CustomModels/*.*") );
            File::delete( File::glob("$this->modelsPath/BasicModels/*.*") );
        }
        $dataForJSON = [];
        $tableKhusus = $tableName;
        
        foreach($schema['tables'] as $key => $table)
        {
            $table = (object)$table;
            $tableName = $table->table;
            $className = count(explode('.',$tableName))>1?explode('.',$tableName)[1]:$tableName;
            $cfg = (array)$table->config;
            $cfg['rules'] = $table->rules;
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
            $cfg['required'] = isset( $cfg['required'] )? array_merge( $cfg['required'], array_filter( $table->required,function($arr)use($cfg){ if(!in_array($arr,$cfg['required'])){return $arr;} } ) ):$table->required;
            $dataForJSONArray = [
                "model" => $tableName,
                "fullColumns" =>$table->fullColumns,
                "columns" => $table->columns,
                "is_view"=>$table->is_view,
                "config" =>[
                    'guarded'   => isset($cfg['guarded'])?$cfg['guarded']:['id'], 
                    'hidden'    => isset($cfg['hidden'])?$cfg['hidden']:[], 
                    'required'  => isset($cfg['required'])? $cfg['required']:[], 
                    'createable'=> isset($cfg['createable'])? $cfg['createable']:array_values(array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )),
                    'updateable'=> isset($cfg['updateable'])? $cfg['updateable']:array_values(array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )),
                    'searchable'=> isset($cfg['searchable'])? $cfg['searchable']:array_values(array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )),
                    'deleteable'=> isset($cfg['deleteable'])?($cfg['deleteable']=="false"?false:true):true,
                    'deleteOnUse'=> isset($cfg['deleteOnUse'])?($cfg['deleteOnUse']=="false"?false:true):false,
                    'casts'     => isset($cfg['casts'])?$cfg['casts']:['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'],
                    'rules'     => isset($cfg['rules'])?$cfg['rules']:[]
                ]
            ];
            $colsArray = [];
            foreach($table->fullColumns as $col){
                $colsArray[] = $col['name'].":".str_replace("\\","", strtolower($col['type']) ).(is_numeric($col['length'])?":{$col['length']}":"");
            }
            $paste = str_replace([
                "__namespace","__class","__table","__columnsFull","__columns"
            ],[
                $this->prefixNamespace, $className, $tableName,'["'.implode('","',$colsArray).'"]', '["'.implode('","',$table->columns).'"]'
            ],$data);
            if( ($request->has('rewrite_custom') && $request->rewrite_custom=='true') || !File::exists( "$this->modelsPath/CustomModels/$className.php" ) ){
                $pasteCustom = str_replace([
                    "__namespace","__class","__basicClass"
                ],[
                    $this->prefixNamespaceCustom, $className, "\\$this->prefixNamespace\\$className"
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
                    if(!$fk->real){continue;}
                    if( count( explode(".", $fk->child) )>1 ){
                        $fk->child = explode(".", $fk->child)[1];
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
                
                $paste = str_replace("__hasManyThrough","",$paste);
                $paste = str_replace("__hasMany",$hasMany,$paste);
                
                // $paste = str_replace("__hasManyThrough","",$paste);
                // $paste = str_replace("__hasMany","",$paste);
            }
            $paste = str_replace("__detailsHeirs", count($detailsHeirs)==0?"[]":'["'.implode('","',$detailsHeirs).'"]' ,$paste);
            $paste = str_replace("__heirs", count($heirs)==0?"[]":'["'.implode('","',$heirs).'"]' ,$paste);
            $paste = str_replace("__detailsChild", count($detailsChild)==0?"[]":'["'.implode('","',$detailsChild).'"]' ,$paste);
            $paste = str_replace("__details", count($details)==0?"[]":'["'.implode('","',$details).'"]' ,$paste);
            $paste = str_replace("__hasManyThrough","",$paste);
            $paste = str_replace("__hasMany","",$paste);
            $dataForJSON[] = array_merge($dataForJSONArray,[
                "details"=>$details,
                "heirs"=>$heirs
            ]);
            if(in_array($tableName, array_keys($schema['children']) )){
                foreach($schema['children'][$tableName] as $fk){
                    $fk=(object)$fk;
                    $joins[] = "$fk->parent.$fk->parent_column=$fk->child.$fk->child_column";
                    $belongsTo.=str_replace([
                        "__parent", "__child_column","__prt_column","__relatedColumn"
                    ],[
                        $fk->parent, $fk->child_column, $fk->parent_column, (Str::endsWith($fk->child_column,'_id')? substr($fk->child_column, 0, -3) : $fk->child_column)
                    ],$this->belongsTo);
                }
                $paste = str_replace("__belongsTo",$belongsTo,$paste);
                $paste = str_replace("__joins", '["'.implode('","',$joins).'"]' ,$paste);
            }else{
                $paste = str_replace("__belongsTo","",$paste);
                $paste = str_replace("__joins", '[]' ,$paste);
            }
            
            $paste = str_replace("__belongsTo","",$paste); //mematikan belongsTo
            $paste = str_replace([
                "__config_guarded","__config_required","__config_createable",
                "__config_updateable","__config_searchable","__config_deleteable",
                "__config_cascade","__config_deleteOnUse","__config_casts", "__config_rules", "__config_unique"
            ], [
                isset($cfg['guarded'])? (!is_array($cfg['guarded'])? "'".$cfg['guarded']."'":'["'.implode('","',$cfg['guarded']).'"]'):"['id']", 
                isset($cfg['required'])? (!is_array($cfg['required'])? "'".$cfg['required']."'":'["'.implode('","',$cfg['required']).'"]'):$table->required, 
                isset($cfg['createable'])? (!is_array($cfg['createable'])? "'".$cfg['createable']."'":'["'.implode('","',$cfg['createable']).'"]'):'["'.implode('","',array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )).'"]',
                isset($cfg['updateable'])? (!is_array($cfg['updateable'])? "'".$cfg['updateable']."'":'["'.implode('","',$cfg['updateable']).'"]'):'["'.implode('","',array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )).'"]',
                isset($cfg['searchable'])? (!is_array($cfg['searchable'])? "'".$cfg['searchable']."'":'["'.implode('","',$cfg['searchable']).'"]'):'["'.implode('","',array_filter($table->columns,function($dt){ if($dt!='id'){return $dt;} } )).'"]',
                isset($cfg['deleteable'])?$cfg['deleteable']:"true",
                isset($cfg['cascade'])?$cfg['cascade']:"true",
                isset($cfg['deleteOnUse'])?$cfg['deleteOnUse']:"false",
                isset($cfg['casts'])?str_replace(["{","}",'":'],["[","\t]",'"=>'],json_encode($cfg['casts'], JSON_PRETTY_PRINT)):"['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y']",
                isset($cfg['rules'])?str_replace(["{","}",'":'],["[","\t]",'"=>'],json_encode($cfg['rules'], JSON_PRETTY_PRINT)):"[]",
                str_replace(["{","}",'":'],["[","\t]",'"=>'],json_encode($table->uniques, JSON_PRETTY_PRINT))
            ],$paste);

            if( File::exists( "$this->modelsPath/BasicModels/$className.php") ){
                File::delete("$this->modelsPath/BasicModels/$className.php" );
            }
            File::put( "$this->modelsPath/BasicModels/$className.php",$paste);
            if( $tableKhusus!==null && $className!==$tableKhusus ){continue;}
            if( ($request->has('rewrite_custom') && $request->rewrite_custom=='true') || !File::exists( "$this->modelsPath/CustomModels/$className.php" ) ){
                File::put( "$this->modelsPath/CustomModels/$className.php",$pasteCustom);
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
                    if(Str::startsWith($stringku, '0_')) $stringku = Str::replaceFirst('0_','', $stringku);
					$results[] = $stringku;
                }
            } else if($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
                if (strpos($path, '.php') !== false && count(explode("_",$path))>3) {
                    $path = str_replace($dir,"",$path);
                    $stringku = implode("_",array_slice(explode("_",$path), 4));   
                    if(Str::startsWith($stringku, '0_')) $stringku = Str::replaceFirst('0_','', $stringku);
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

    public function readMigrationsOrCache(Request $req){
        $self = $this;
        
        $devRole = config('devrole');
        
        if( $devRole == 'frontend' ){
            $migrationLists = [
                'models'=>[] 
            ];
        }else{
            $migrationLists = Cache::rememberForever('migration-list', function ()use($self, $req) {
                return $self->readMigrations( $req, null);
            });
            if( !is_array($migrationLists) ){
                $migrationLists = $self->readMigrations( $req, null);
            }
        }
        
        
        if( $devRole == 'backend' ){
            $jsFiles = [];
            $bladesFiles = [];
            $coreFiles = [];
        }else{
            
            $jsFiles = array_filter( scandir( base_path('public/js') ), function($dt) {
                return !in_array($dt,['.','..','README.md']);
            });
            
            $bladesFiles = array_filter( scandir( base_path('resources/views/projects') ), function($dt) {
                return !in_array($dt,['.','..','readme.md']);
            });
    
            $coreFiles = $devRole == 'frontend'?[]:array_filter( scandir( app_path('Cores') ), function($dt) {
                return !in_array($dt,['.','..','README.md']);
            });
        }
        
        return array_merge($migrationLists,[
            "js"=> array_values( $jsFiles ),
            "blades"=> array_values( $bladesFiles ),
            "cores"=> array_values( $coreFiles ),
            "role"=> $devRole
        ]);
    }

    public function readMigrations(Request $req, $table=null){
        try{
            if($table){
                $migrationPath = base_path("database/migrations/projects/0_0_0_0_$table.php");
                if(!File::exists( $migrationPath )){
                    return response()->json("migration file [$table] tidak ada",400);
                }
                return File::get( $migrationPath );
            }else{
                $data = $this->getDirContents( base_path('database/migrations/projects') );
                $schemaManager = DB::getDoctrineSchemaManager();
                $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
                $tables = $schemaManager->listTableNames();
                $arrayTables = []; $arrayViews = [];
                $fk = 0;
                foreach ($tables as $table) {
                    $table = $schemaManager->listTableDetails($table);
                    $tableNames = explode('.', $table->getName());
                    if( count($tableNames)>1 ){
                        $tableName=$tableNames[1];
                    }else{
                        $tableName=$tableNames[0];
                    }
                    $arrayTables[] =  $tableName;
                    $FK = $table->getForeignKeys();
                    $fk += count($FK);
                    $triggers=\App\Helpers\DBS::getTriggers($table->getName());
                    foreach($triggers as $trigger){
                        $arrayTables[] = $trigger->trigger_name;
                    }
                }
                $views = $schemaManager->listViews();
                foreach ($views as $view) {            
                    if( strpos($view->getName(),"pg_catalog.")!==false || strpos($view->getName(),"information_schema.")!==false ){
                        continue;
                    }
                    $viewName = str_replace("public.","",$view->getName() );
                    $arrayTables[] =  $viewName;
                    $arrayViews[]=$viewName;
                }
                $models = [];
                
                $addedTables = [];
                foreach($data as $file){
                    if($file==""){continue;};
                    $stringClass = str_replace([".php"],[""], $file);
                    $modelCandidate = "\App\Models\BasicModels\\$stringClass";
                    $addedTables[] = $stringClass;
                    $models[] =[
                        "file" => $file,
                        "model"=> class_exists( $modelCandidate )?true:false,
                        "table"=> in_array($stringClass, $arrayTables)?true:false,
                        "alias"=>class_exists( $modelCandidate )?( isset((new $modelCandidate )->alias)?true:false ):false,
                        "view" => in_array($stringClass, $arrayViews)?true:false,
                    ];
                }

                //  Create New Migration File if needed
                if( env("AUTOCREATE_MIGRATION") ){
                    $noMigrationTables = array_filter( $arrayTables, function( $tb ) use( $addedTables ){
                        return !in_array( $tb, $addedTables );
                    });
                    foreach($noMigrationTables as $tb){
                        $this->createModels( $req, $tb );
                        $hasil = Artisan::call('migrate:generate', [
                            '--path'    => database_path("migrations/projects"),
                            '--tables'  => $tb,
                            '--no-interaction'   => true,
                        ]);
                        $migrationFiles = array_filter(File::glob(database_path('migrations/projects/*.*')),function($dt)use($tb){
                            $regex = "/(\d+)_(\d+)_(\d+)_(\d+)_create_$tb".'_table.php/';
                            return preg_match($regex,basename($dt));
                        });
                        $migrationText = "";
                        
                        if( count( $migrationFiles )> 0 ){
                            $migrationText = File::get( array_values($migrationFiles)[0]);
                            // \Storage::disk("base")->move("database/migrations/projects/".basename(array_values($migrationFiles)[0]), "database/migrations/projects/0_0_0_0_"."$tb.php");
                            File::put(base_path('database/migrations/projects')."/0_0_0_0_"."$tb.php", (str_replace([
                                "class Create","Table extends"
                            ],[
                                "class "," extends"
                            ],$migrationText))."\r\n\r\n\r\n\r\n" );
                            
                            File::delete(array_values($migrationFiles)[0]);
                        }
                        $modelCandidate = "\App\Models\BasicModels\\$tb";
                        $models[] =[
                            "file" => $tb,
                            "model"=> class_exists( $modelCandidate )?true:false,
                            "table"=> in_array($tb, $arrayTables)?true:false,
                            "alias"=>class_exists( $modelCandidate )?( isset((new $modelCandidate )->alias)?true:false ):false,
                            "view" => in_array($tb, $arrayViews)?true:false
                        ];
                    }
                }

                return [
                    "models"=>$models,
                    "realfk"=>$fk 
                ];
            }
        }catch(Exception $e){
            return response()->json(["error"=>$e->getFile()."-".$e->getLine()."-".$e->getMessage()],400);
        }
    }

    public function readAlter(Request $req, $table=null){
        if( $table ){
            $alterPath = base_path("database/migrations/alters/0_0_0_0_$table.php");
            if(!File::exists( $alterPath )){
                return str_replace([
                    "__class__","__table__",
                ],[
                    str_replace("_","", $table), getBasic($table)->getTable()
                ], $this->getAlterFile());
            }
            return File::get( $alterPath );
        }
    }
    public function readLog(Request $req, $table=null){
        return getLog($table.".json");
    }
    public function readTest(Request $req, $table=null){
        $file = getTest($table);
        $schema = getSchema($table);
        $schema = str_replace(["{","}",'":'],["[","]",'" =>'],json_encode($schema, JSON_PRETTY_PRINT));
        return str_replace('__payload__', str_replace("\n","\n\t\t","$schema"), $file);
    }
    public function queries10rows(Request $req, $table=null){
        $model = getBasic($table);
        $columns = $model->columns;
        $orderCol = in_array("id", $columns)?"id":$columns[0];
        $rows = DB::table($model->getTable())->orderBy($orderCol, 'desc')->limit(10)->get()->toArray();
        if($req->has('json')){
            if(!$rows){
                $fakeData = [];
                foreach( $model->columns as $col){
                    $fakeData[ $col ]='(NULL)';
                }
                $rows = [$fakeData];
            }
            return $rows;
        }

        if(!$rows){
            $rows = [];
        }
        $htmlData = "<div class='bg-white text-center' style='padding:5px;max-height:93vh;min-height:15vh;width:100vw;position:fixed;left:0;top:5%;overflow:auto;'>
        <table cellspacing='0' cellpadding='0' border=1 class='text-dark table table-striped' style='width:100%;font-size:0.75em;'>
        <thead class='bg-success text-white'><tr>{{HEADER}}</tr></thead><tbody>{{BODY}}</tbody><tfoot>{{FOOTER}}</tfoot></table></div>";
        $header = "";
        foreach($columns as $col){
            $header.="<th class='font-bold'>$col</th>";
        }
        $htmlData = str_replace("{{HEADER}}", $header, $htmlData);

        $tbody = "";
        $tr="<tr>{{tds}}</tr>";
        foreach($rows as $row){
            $row = (array)$row;
            $tds = "";
            foreach($columns as $col){
                $tds.="<td>{$row[$col]}</td>";
            }
            $tbody.=(str_replace("{{tds}}", $tds, $tr));
        }
        $htmlData = str_replace("{{BODY}}", $tbody, $htmlData);
        $htmlData = str_replace("{{FOOTER}}", $tbody==''?"<p class='text-dark text-center'>No Data</p>":'', $htmlData);

        return $htmlData;
    }

    public function truncate(Request $req, $table){
        $model = getCustom( $table );
        if(!$model->truncatable){
            return response()->json("$table is not truncatable", 401);
        }
        $model->truncate();
        devTrack( 'Truncating', $table );
        return "$table has been truncated";
    }

    public function doTest(Request $req, $table=null){
        putenv("APP_ENV=testing");
        $disabled = explode(',', str_replace(" ","",ini_get('disable_functions')) );
        $canExec =  !in_array('exec', $disabled);
        if($canExec ){
            $return = 0;
            try{
                $table = Str::camel(ucfirst($table));
                $className = $table."Test";
                $fileExe = base_path("vendor/phpunit/phpunit/phpunit");
                $fileLog = base_path("testlogs/$className.txt");
                $basePath = base_path();
                $output  = null;
                $return  = null;
                // $phpBin = 'php'.substr(phpversion(),0,3);
                $phpBin = env('PHPBIN','php');
                exec("cd $basePath && $phpBin $fileExe --filter=$className --testdox-text=testlogs/$className.txt", $output, $return);
                $fileLogRes = File::get($fileLog);;
            }catch(Exception $e){
                return response($e->getMessage(), 400);
            }
            return [ 
                "output" => str_replace(['Failed','OK'],[
                    "<span class='text-danger'> Failed </span>",
                    "<span class='text-success'> OK </span>"
                ], join("<br>", $output) ) ,
                "text" => str_replace(["\n",'[ ]','[x]'],[
                    "<br>",
                    "<span class='text-danger'>[ Failed! ]</span>",
                    "<span class='text-success'>[ Success ]</span>"
                ],$fileLogRes),
                "failed" => $return?true:false
            ];
        }else{
            abort(401, "exec PHP cannot be used!, do with CLI");
        }
    }
    public function doMigrate( Request $req, $table=null )
    {
        $now = Carbon::now();
        $subDomain = getOriginServer();
        $cacheKey = "log-migration-$subDomain";
        
        $migrationLogs = Cache::get( $cacheKey )??[];
        
        $migrationLogs[ $table ] = [
            'time' => $now->timestamp,
            'ts'    => $now->format('Y-m-d H:i:s'),
            'type'  => $req->has('down') ? 'down':($req->has('alter') ? 'alter' : 'migrate')
        ];

        Cache::forever( $cacheKey, $migrationLogs );

        Schema::disableForeignKeyConstraints();
        File::delete(glob(base_path('database/migrations')."/*.*"));
        $dir = "projects";
        if($req->has('alter')){
            $dir = "alters";
        }
        $migrationPath = base_path("database/migrations/$dir/0_0_0_0_$table.php");
        if(!File::exists( $migrationPath )){
            return response()->json("migration file [$table] tidak ada",400);
        }
        
        if( !$req->has('alter') ){
            if( Str::contains( $table, "_after_" ) || Str::contains( $table, "_before_" ) ){
                $samaran = str_replace(['_after_','_before_'],["_timing_","_timing_"],$table);
                $tableName = explode("_timing_",$samaran)[0];
                $modelObj = getBasic($tableName);
                if($modelObj){
                    $tableName = $modelObj->getTable();
                }
                try{
                    DB::unprepared("                    
                        DROP TRIGGER IF EXISTS $table ON $tableName;
                        DROP FUNCTION IF EXISTS fungsi_"."$table();
                    ");
                }catch(Exception $e){}
            }
        }

        if( $req->has('down') ){
            $realTable = $table;
            $modelObj = getBasic($table);
            if( $modelObj ){
                $realTable = $modelObj->getTable();
            }
            
            //  asumsi table atau view
            if( Schema::hasTable( $realTable ) ){
                Schema::dropIfExists( $realTable );
            }else{
                DB::unprepared("DROP VIEW IF EXISTS $realTable;");
            }

            Cache::forget('migration-list');
            devTrack( 'Migrate Down', $table );
            return "database migration ok, $table was downed successfully";
        }
        
        try{
            $file = "database/migrations/$dir/0_0_0_0_$table.php";
            $exitCode = Artisan::call( 'migrate:refresh', [
                '--path' => $file,
                '--force' => true,
            ]);
            $this->createModels( $req, $table );
            Cache::forget( 'migration-list' );
        }catch(Exception $e){
            return response()->json(["error"=>$e->getMessage()], 422);
        }

        Schema::enableForeignKeyConstraints();

        if($req->has('alter')){
            if(env('GIT_ENABLE', false)){ 
                $this->git_push(".","<ALTER $table>");       
            }
            devTrack( 'Migrate Alter', $table );
            return "database alter ok, $table model altered successfully";
        }else{
            if(env('GIT_ENABLE', false)){ 
                $this->git_push(".","<MIGRATE $table>");       
            }
            devTrack( 'Migrate Up', $table );
            return "database migration ok, $table model recreated successfully";
        }
    }
    public function deleteAll(Request $req, $table){
        try{
            File::delete(glob(base_path('database/migrations')."/*.*"));//g penting sih
            if(strpos($table,"_after_")!==false || strpos($table,"_before_")!==false){
                $samaran = str_replace(['_after_','_before_'],["_timing_","_timing_"],$table);
                $tableName = getBasic(explode("_timing_",$samaran)[0])->getTable();
                DB::unprepared("
                    DROP TRIGGER IF EXISTS $table ON $tableName;
                    DROP FUNCTION IF EXISTS fungsi_"."$table();
                ");
            }else{
                $realTable = $table;
                $modelObj = getBasic($table);
                if($modelObj){
                    $realTable = $modelObj->getTable();
                }
                try{
                    DB::unprepared("DROP TABLE IF EXISTS $realTable");
                }catch(Exception $e){}
                try{
                    DB::unprepared("DROP VIEW IF EXISTS $realTable;");
                }catch(Exception $e){}
            }
            if( File::exists( "$this->modelsPath/BasicModels/$table.php") ){
                File::delete("$this->modelsPath/BasicModels/$table.php" );
            }
            if( File::exists( "$this->modelsPath/CustomModels/$table.php") ){
                File::delete("$this->modelsPath/CustomModels/$table.php" );
            }
            if( File::exists( base_path("database/migrations/projects/0_0_0_0_$table.php") ) ){
                File::delete( base_path("database/migrations/projects/0_0_0_0_$table.php") );
            }
            if( File::exists( base_path("database/migrations/alters/0_0_0_0_$table.php") ) ){
                File::delete( base_path("database/migrations/alters/0_0_0_0_$table.php") );
            }
        }catch(Exception $e){
            return response()->json($e->getMessage(),422);
        }
        
        if(env('GIT_ENABLE', false)){ 
            $this->git_push(".","<DROP $table>");       
        }
        
        Cache::forget('migration-list');
        devTrack( 'Delete All Backend', $table );
        return response()->json("Model, Migrations, Table, Trigger terhapus semua");
    }
    public function editAlter(Request $req, $table=null){
        $res = putFileDiff( base_path("database/migrations/alters/0_0_0_0_$table.php") , $req->text); 
        if(env('GIT_ENABLE', false)){ 
            $this->git_push(".","<SAVE ALTER $table>");       
        }
        devTrack( 'Updating Migration Alter', $table, $res );
        return "update Alter OK";
    }
    public function editTest(Request $req, $table=null){
        $table = Str::camel(ucfirst($table));
        $path = base_path("tests/$table"."Test.php");
        File::put($path, $req->text);
        devTrack( 'Updating Testing', $table );
        return "update Test OK";
    }
    
    public function refreshAlias(Request $req,$table){
        // return response()->json($parent,400);
        $className = "\\App\\Models\\BasicModels\\$table";   
        $class = new $className();
        $parent = $class->getTable();
        try{
            $stringModelSrc = File::get("$this->modelsPath/BasicModels/$parent.php");
            $stringModelSrc = str_replace( [
                "class $parent",
                "public \$lastUpdate"
            ],[
                "class $table",
                "public \$alias=true;\npublic \$lastUpdate"
            ],$stringModelSrc);
            File::put( "$this->modelsPath/BasicModels/$table.php",$stringModelSrc);
        }catch(Exception $e){
            return response()->json($e,400);
        }
        return "update Basic Model Alias from $parent OK";
    }
    public function editMigrations(Request $req, $table=null){
        if($table){
            $migrationPath = base_path("database/migrations/projects/0_0_0_0_$table.php");
            if(!File::exists( $migrationPath )){
                return response()->json("migration file [$table] tidak ada",400);
            }
            $file = putFileDiff( $migrationPath , $req->text); 
            if(env('GIT_ENABLE', false)){ 
                $this->git_push(".","<SAVE MIGRATION $table>");       
            }
            devTrack( 'Updating Migration', $table, $file );
            return "update Migration OK";
        }
        $data = $this->getDirContents( base_path('database/migrations/projects') );
        Cache::forget('migration-list');
        if(!$req->has('modul')){
            return response()->json("Maaf modul wajib diisi", 400);
        }

        if(Str::endswith($req->modul, ".php")){
            $path = app_path("Cores/$req->modul");
            $template = File::get( base_path( "templates/core.stub" ) );
            File::put( $path, Str::replace("__class", explode(".", $req->modul)[0], $template ));
            return response()->json("pembuatan file core berhasil, silahkan refresh list");
        }elseif(Str::endswith($req->modul, ".js")){
            $path = public_path("/js/$req->modul");
            File::put( $path, "//   javascript");
            return response()->json("pembuatan file javascript berhasil, silahkan refresh list");
        }elseif(Str::endswith($req->modul, ".blade")){
            $path = resource_path("views/projects/$req->modul.php");
            File::put( $path, "<?php\n // blade php file");
            return response()->json("pembuatan file blade berhasil, silahkan refresh list");
        }elseif(Str::endswith($req->modul, ".frontend")){
            $single = str_replace(".frontend", '', $req->modul);
            $path = resource_path("views/projects/$single.blade.php");
            File::put( $path, "<?php\n // blade php file");
            $pathJS = public_path("/js/$single.js");
            File::put( $pathJS, "//   javascript");
            return response()->json("pembuatan file blade & js berhasil, silahkan refresh list");
        }

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
            File::put(base_path('database/migrations/projects')."/0_0_0_0_$modul.php", str_replace([
                "__class__","__table__",
            ],[
                str_replace("_","",$modul),$tableSrc
            ],File::get( base_path("templates/migrationalias.stub") ) ));
            return response()->json("pembuatan file migration Alias OK");
        }elseif(strpos("x".$req->modul, "view ")!==false && count(explode(" ",$req->modul))==2 ){
            $modul   = explode(" ",  $req->modul)[1];
            if(in_array("$modul.php", $data)){
                return response()->json("maaf nama model $modul telah terpakai", 400);
            }
            
            File::put(base_path('database/migrations/projects')."/0_0_0_0_$modul.php", str_replace([
                "__class__","__table__",
            ],[
                str_replace("_","",$modul),$modul
            ],File::get( base_path("templates/migrationview.stub") ) ));
            return response()->json("pembuatan file migration View OK");
        }elseif(strpos("x".$req->modul, "trigger ")!==false && count(explode(" ",$req->modul))==4 ){
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
        
        File::put(base_path('database/migrations/projects')."/0_0_0_0_$modul.php", str_replace([
            "__class__","__table__",
        ],[
            str_replace("_","",$modul),$modul
        ],$this->getMigrationFile() ));
        
        return response()->json("pembuatan file migration OK");
    }

    public function getCoreFile(Request $req, string $filename = null ){
        if( $filename ){
            $path = app_path("Cores/$filename.php");
            return File::get( $path );
        }
        return [];
    }

    public function saveCoreFile(Request $req, string $filename ){
        $path = app_path("Cores/$filename.php");
        $res = putFileDiff( $path , $req->text ); 
        devTrack( 'Updating Core', $filename, $res );
        return response()->json("core file was saved");
    }

    public function deleteCoreFile(Request $req, string $filename ){
        $path = app_path("Cores/$filename.php");
        File::delete( $path ); 
        devTrack( 'Deleting Core', $filename );
        return response()->json("core file was delete");
    }

    public function getJsFile(Request $req, string $filename = null ){
        if( $filename ){
            $path = public_path("js/$filename.js");
            return File::get( $path );
        }

        return [];
    }

    public function saveJsFile(Request $req, string $filename ){
        $path = public_path("js/$filename.js");
        $file = putFileDiff( $path , $req->text); 
        devTrack( 'Updating JS', $filename, $file );
        $notified = wssNotify( type: "reload", message: $filename );
        return response()->json( $notified ? $notified : "js file was updated successfully" );
    }

    public function deleteJsFile(Request $req, string $filename ){

        $path = public_path("js/$filename.js");
        $file = File::delete( $path ); 
        devTrack( 'Deleting JS', $filename );
        return response()->json("js file was delete");
    }

    public function getBladeFile(Request $req, string $filename = null ){
        if( $filename ){
            $path = base_path("resources/views/projects/$filename.blade.php");
            return File::get( $path );
        }

        return [];
    }

    public function saveBladeFile(Request $req, string $filename ){

        $path = base_path("resources/views/projects/$filename.blade.php");
        $file = putFileDiff( $path , $req->text);
        devTrack( 'Updating Blade', $filename, $file );
        $notified = wssNotify( type: "reload", message: $filename );
        return response()->json( $notified ? $notified : "blade file was updated successfully" );
    }

    public function deleteBladeFile(Request $req, string $filename ){
        $path = base_path("resources/views/projects/$filename.blade.php");
        $file = File::delete( $path ); 
        devTrack( 'Deleting Blade', $filename );
        return response()->json("blade file was deleted");
    }

    public function getNotice(Request $req){
        $model = $req->data;
        if(strpos($model,".")!==false){
            $model = explode(".",$req->data)[1];
        }
        $model = getCustom($model);
        return method_exists( $model, "frontendNotice" )?$model->frontendNotice():"tidak ada catatan";
    }
    public function uploadLengkapi(Request $request){
        $data = $request->data;
        $dataArray = [];
        foreach($data as $index => $dt){
            foreach($dt as $indexCol => $col){
                if( strpos( strtolower($col),"select ")!==false ){
                    $data[$index][$indexCol] = getRawData( $col );
                }elseif( strpos( strtolower($col),"bcrypt::")!==false ){
                    $hasher = app()->make('hash');
                    $data[$index][$indexCol] = $hasher->make( str_replace(['bcrypt::'], [''], $col) );
                }
            }
        }
        return $data;
    }
    public function uploadWithCreate(Request $req){
        DB::disableQueryLog();
        $data = $req->data; 
        $tempTable = @$req->table??'temp_uploaders';
        try{  
            Schema::dropIfExists($tempTable);
            Schema::create($tempTable,function (Blueprint $table)use($data) {
                $table->bigIncrements('_id');
                foreach( array_keys( $data[0]) as $key ){
                    $table->text( str_replace(' ', '_', trim(strtolower($key))) )->nullable();
                }
            });
            
            DB::beginTransaction();
            $insertData = collect($data)->map(function($dt){
                $formattedData = [];
                foreach( $dt as $k => $v ){
                    $formattedData[str_replace(' ', '_', trim(strtolower($k)))] = $v;
                }
                return $formattedData;
            })->chunk(500);
            foreach($insertData as $chunkedData){
                DB::table($tempTable)->insert($chunkedData->toArray());
            }
            DB::commit();
        }catch(Exception $e){
            DB::rollback();
            return response()->json([
                'index'=>'entahlah',
                'error'=>$e->getMessage()
            ],422);
        }
        return count($data)." rows inserted successfully in table `$tempTable`!";
    }
    public function uploadTest(Request $request){
        DB::disableQueryLog();
        $table = $request->table;
        $data = $request->data;
        $final=$request->final;
        $columns = $request->columns;
        $dataNew = array_map(function($dt)use($columns){
            $dataTemp = Arr::only($dt,$columns); 
            foreach($dataTemp as $i => $tmp){
                if($tmp==""||strtolower($tmp)=="null"||$tmp==null){
                    unset($dataTemp[$i]);
                }
            }
            return $dataTemp;
        },$data);
        DB::beginTransaction();
        $indexOpt = 1;
        try{
            foreach($dataNew as $index => $dt){
                DB::table($table)->insert($dt);
                $indexOpt++;
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json([
                'index'=>$indexOpt,
                'error'=>$e->getMessage()
            ],422);
        }
        if($final===true){
            DB::commit();
            return 'inserting ok';
        }else{
            DB::rollback();
            return 'testing ok';

        }
    }
    public function git_push($filename, $commit = 'new'){
        $agent = new \Jenssegers\Agent\Agent();
        $lokasi = new \Stevebauman\Location\Location;
        $giturl = env("GIT_URL");
        $realpath = base_path();
        File::put(base_path(".gitignore"),
                "/vendor
                \n/public/uploads
                \n/public/models.json
                \n*.bat
                \n/.idea
                \nHomestead.json
                \nHomestead.yaml
                \n.env"
        );
        $gitWrapper = new \GitWrapper\GitWrapper();
      	$git = $gitWrapper->workingCopy(base_path());
        try{
          	$gitWrapper->git('status');
        }catch(Exception $e){
        	$git = $gitWrapper->init(base_path());
            $gitWrapper->git("remote add origin ".env("GIT_URL"));
            $commit = "first time";
        }
        $platform = $agent->platform();
        $platformversion = $agent->version($agent->platform());
        $browser=$agent->browser();
        $browserversion=$agent->version($agent->browser());
        $ip = app()->request->ip();
        // $location=$lokasi->get($ip);
        $commit.=" [$platform-$platformversion $browser-$browserversion IP:$ip]";
      	if( strpos($gitWrapper->git('status'),"nothing to commit")!==false){
        	
        }else{
          $git->add($filename);
          $git->commit($commit);        
        }
        $time = explode(":", env('GIT_PUSH_START', "15:00") );
        $hour = $time[0];
        $mins = $time[1];
        if( (date("H")*60+date("i")) >= ($hour*60+$mins) ){
            return $git->push("origin","master");
        }
        return 'ok';
    }
    public function uploadTemplate(Request $request){
        $id = $request->id;
        if($id!==null){
            DB::table($request->table)->where('id',$id)->update([
                'template' => $request->template,
                'updated_at' => Carbon::now()
            ]);
        }else{
            DB::table($request->table)->insert([
                'name' => $request->name,
                'template' => $request->template,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ]);
        }
        return DB::table($request->table)->select('name','template','id')->get();;
    }

    public function paramaker(Request $req){
        $isNew =  !is_numeric($req->id);
        $id = $isNew?-1:$req->id;
        $validator = Validator::make($req->all(), [
            "name"=>"unique:default_params,name,$id|required",
            "prepared_query"=>"required"
        ]);
        $preparedQuery = $req->prepared_query;
        preg_match_all("/(:\w+)/", $preparedQuery, $m);
        
        $preparedQueryParams = array_map(function($dt){
            return str_replace( ":", "", $dt);
        },$m[0]);
        
        $preparedQueryParamsFixed = [];
        foreach($preparedQueryParams as $qparam){
            if(!in_array( $qparam, $preparedQueryParamsFixed )){
                $preparedQueryParamsFixed[] = $qparam;
            }
        }
        $dataArr = $req->except('id','changed');
        $dataArr['params'] = implode(",", $preparedQueryParamsFixed);

        if ( $validator->fails()) {
           return response()->json($validator->errors()->all(), 422);
        }
        if($isNew){
            return DB::table("default_params")->insertGetId( $dataArr );
        }else{
            getBasic("default_params")->find($req->id)->update( $dataArr );
            return 'update ok';
        }
    }

    public function runQuery( Request $req ){
        if( !$req->has('statement')){
            return response()
                    ->json([
                        "message"=>"statement is required, commit is optional"
                    ], 400);
        }

        $state = $req->statement;
        $isCommit = Str::contains( Str::lower($state), ':commit' );
        $state = str_ireplace( ':commit', '', $state );
        $stateToLower = Str::lower($state);
        $isNeedTransaction = Str::contains( $stateToLower, "delete from") || Str::contains( $stateToLower, "update ") || Str::contains( $stateToLower, "insert into");
        try{
            if($isNeedTransaction){
                DB::beginTransaction();
            }

            if( Str::contains($state, ";") ){
                $stateArr = explode(";", $state);
                
                foreach( $stateArr as $q ){
                    if( !$q ){
                        continue;
                    }
                    $qLower = Str::lower( $q );
                    if( Str::contains( $qLower, "delete from") || Str::contains( $qLower, "update ") || Str::contains( $qLower, "insert into") ){
                        DB::unprepared( $q );
                        $result = 'query ok';
                    }else{
                        $result = DB::select( $q );
                    }
                }
            }else{
                $result = DB::select( $state );
            }
        }catch( Exception $e ){
            DB::rollback();
            return response()->json( $e->getFile().":".$e->getMessage(), 400);
        }

        if( $isNeedTransaction ){
            if($isCommit){
                DB::commit();
            }else{
                DB::rollback();
            }
        }

        return [
            'data'=>$result
        ];
    }

    public function runBackup(){
        try{
            Artisan::call("backup");
        }catch(Exception $e){
            return response()->json($e->getMessage(), 422);
        }
        return 'backup ok';
    }

    public function getBackup( Request $req ){

        if( !$req->has('key') || $req->key!=env("LARADEVPASSWORD","bismillah") ){
            return response()->json("unauthorized", 401);
        }

        $path = env( 'BACKUP_PATH') ?? base_path('app_generated_backup');
        $timeStamp = 'last';
        
        if( ! File::exists( $path ) || $req->has('fresh') ){
            $timeStamp = date("Y-m-d H:i:s");
            try{
                Artisan::call("backup");
            }catch(Exception $e){
                return response()->json($e->getMessage(), 422);
            }
            sleep(1);
        }

        $zip_file = storage_path( "temporary_file_backup_$timeStamp.zip" );

        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($files as $name => $file)
        {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();

                // extracting filename with substr/strlen
                $relativePath = substr($filePath, strlen($path) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        return response()->download($zip_file)->deleteFileAfterSend(true);
    }
}

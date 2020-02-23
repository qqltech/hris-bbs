<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ModelerController extends Controller
{
    private $modelsPath = "";
    private $prefixNamespace = "";
    private $prefixNamespaceCustom = "";
    private $hasMany ="";
    private $belongsTo ="";

    function __construct(){
        umask(0000);
        $this->modelsPath = app()->path()."/Models";
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
    }
    private function getConnection($conn){
        $conn = (object)$conn;
        $defaultConn = config('database.connections.flying'.$conn->driver);
        $newConn     = array_merge($defaultConn, (array)$conn);
        config(['database.connections.flying'.$conn->driver=>$newConn]);
        return DB::connection('flying'.$conn->driver);
    }

    private function getTables($connection){
        try{
            $conn = $this->getConnection($connection);
            // $conn->disableForeignKeyConstraints();
            $conn->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $data = $conn->getDoctrineSchemaManager()->listTables();
            $tables = [];
            $fks = [];
            $cds = [];
            foreach ($data as $table) {
                $foreignKeys = [];
                $required = [];
                $rawForeignKeys = $table->getForeignKeys();
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
                foreach ($table->getColumns() as $column) {
                    $columns[] = $column->getName();
                    $comment = $column->getComment();
                    if($comment!=null && $comment!=""){
                        $comment = json_decode($comment);
                        if( isset($comment->fk) ){
                            $fk = $comment->fk;
                            $arrayFK = explode(".", $fk);
                            $fktemp= [
                                "child"=> $table->getName(), "child_column"=>$column->getName(),
                                "parent"=> $arrayFK[0], "parent_column"=> $arrayFK[1] 
                            ];
                            $foreignKeys[]=$fktemp;
                            $fks[ $arrayFK[0]][] = $fktemp;
                            $cds[ $table->getName() ][]   = $fktemp;

                        }
                        if( isset($comment->required) ){
                            $isRequired = $comment->required;
                            if($isRequired){
                                $required[]=$column->getName();
                            }
                        }
                    }
                }
                $columns = '["'.implode('","',$columns).'"]';
                $required = count($required)>0?'["'.implode('","',$required).'"]':"[]";
                $tables[]=[
                    "table" => $table->getName(),
                    "columns"=>$columns,
                    "foreign_keys" => $foreignKeys,
                    "required" => $required
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

    public function modelFromDB(Request $request) {
        $data = file_get_contents($request->frame_basic);
        $dataCustom = file_get_contents($request->frame_custom);
        $schema = $this->getTables($request->connection);
        
        foreach($schema['tables'] as $table)
        {
            $table = (object)$table;
            $tableName = $table->table;

            $paste = str_replace([
                "__namespace","__class","__table","__columns", "__required", "__lastupdate"
            ],[
                $this->prefixNamespace, $tableName, $tableName, $table->columns, $table->required, date('d/m/Y H:i:s')
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
            $joins = [];
            if(in_array($tableName, array_keys($schema['foreignkeys']) )){
                foreach($schema['foreignkeys'][$tableName] as $fk){
                    $fk=(object)$fk;
                    $hasMany.=str_replace([
                        "__child", "__cld_column","__parent_column"
                    ],[
                        $fk->child, $fk->child_column, $fk->parent_column
                    ],$this->hasMany);
                }
                $paste = str_replace("__hasMany",$hasMany,$paste);
            }else{
                $paste = str_replace("__hasMany",$hasMany,$paste);
            }
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
                $paste = str_replace("__belongsTo",$belongsTo,$paste);
                $paste = str_replace("__joins", '["'.implode('","',$joins).'"]' ,$paste);
            }else{
                $paste = str_replace("__belongsTo",$belongsTo,$paste);
                $paste = str_replace("__joins", '[]' ,$paste);
            }

            File::put( "$this->modelsPath/BasicModels/$tableName.php",$paste);
            if($request->rewrite_custom || !File::exists( "$this->modelsPath/CustomModels/$tableName.php" ) ){
                File::put( "$this->modelsPath/CustomModels/$tableName.php",$pasteCustom);
            }
        }
        return "ok";
    }
}

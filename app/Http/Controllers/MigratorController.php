<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;

class MigratorController extends Controller
{
    private function getConnection($conn){
        $conn = (object)$conn;
        $defaultConn = config('database.connections.flying'.$conn->driver);
        $newConn     = array_merge($defaultConn, (array)$conn);
        config(['database.connections.flying'.$conn->driver=>$newConn]);
        return DB::connection('flying'.$conn->driver);
    }
    
    private function create($connection_id, $tableName, $columns){
        $connection = \App\Models\Laradev\LaradevDBConnections::find($connection_id);
        $defaultConn = $this->getConnection($connection->connection);
        $conn = $defaultConn->getSchemaBuilder();
        // $conn = $this->getConnection($connection)->getSchemaBuilder();
        // $conn->disableForeignKeyConstraints();
        $conn->dropIfExists($tableName);
        $conn->create($tableName, function (Blueprint $table)use($columns,$defaultConn,$tableName) {
            $table->bigIncrements('id');
            foreach($columns as $column){
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
        // $conn->enableForeignKeyConstraints();
    }
    private function renameTable($request){
        try {
            $conn = $this->getConnection($request->connection)->getSchemaBuilder();
            // $conn->disableForeignKeyConstraints();
            $conn->rename($request->table, $request->newtable);
            // $conn->enableForeignKeyConstraints();
        } catch (\Exception $e) {
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return response()->json(['status'=>"renaming table $request->table to $request->newtable OK"]);
    }
    private function dropTable($request){
        try {
            $conn = $this->getConnection($request->connection)->getSchemaBuilder();
            // $conn->disableForeignKeyConstraints();
            $conn->dropIfExists($request->table);
            // $conn->enableForeignKeyConstraints();
        } catch (\Exception $e) {
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return response()->json(['status'=>"dropping table $request->table OK"]);
    }
    private function check($request){
        try {
            $connection = $request->connection;
            if($request->connection["driver"]=="pgsql"){                
                $connection["database"] = "postgres";
            }else{
                $connection = $request->connection;
            }
            $conn = $this->getConnection($connection);
            return $conn->getDoctrineSchemaManager()->listDatabases();
            // if($conn->getPdo()){
            //     return $conn->getDoctrineSchemaManager()->listDatabases();
            //     return response()->json(['status'=>'DB Connection OK']);
            // }else{
            //     return response()->json(['status'=>'Database not Found'],422);
            // }
        } catch (\Exception $e) {
            return response()->json(['status'=>$e->getMessage()],422);
        }
    }

    private function migrate($request){
        try{
            $this->create($request->connection_id,$request->table, $request->columns);
        }catch(Exception $e){
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return $request->merge(['status'=>"migration $request->table OK"]);
    }

    private function getTables($request){
        try{
            $connection = \App\Models\Laradev\LaradevDBConnections::find($request->connection_id);
            $conn = $this->getConnection($connection->connection);
            $conn->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            // $conn->disableForeignKeyConstraints();
            $data = $conn->getDoctrineSchemaManager()->listTables();
            $tables = [];
            foreach ($data as $table) {

                $columns = [];
                foreach ($table->getColumns() as $column) {
                    $columns[] = [ 
                        "name"=>$column->getName(),
                        "type"=> "".$column->getType(),
                        "length"=> "".$column->getLength(),
                        "default"=> "".$column->getDefault(),
                    ];
                }
                $tmp=[
                    "name"=>$table->getName(),
                    "actual_FK"=>$table->getForeignKeys(),
                    "actual_indexes"=>array_keys($table->getIndexes()), 
                    "actual_columns"=>$columns,
                    "project"=>$connection->project,
                    "connection_name"=>$connection->name
                ];
                $tables[]   =  $tmp;
            }
        }catch(\Exception $e){
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return $request->merge(['status'=>'get tables success', 'tables'=>$tables]);
    }
    
    private function getActualTables($request){
        try{
            $connection = \App\Models\Laradev\LaradevDBConnections::find($request->connection_id);
            $conn = $this->getConnection($connection->connection);
            $conn->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            // $conn->disableForeignKeyConstraints();
            $data = $conn->getDoctrineSchemaManager()->listTables();
            $tables = [];
            foreach ($data as $table) {

                $columns = [];
                foreach ($table->getColumns() as $column) {
                    $columns[] = [ 
                        "name"=>$column->getName(),
                        "type"=> "".$column->getType(),
                        "length"=> "".$column->getLength(),
                        "default"=> "".$column->getDefault(),
                    ];
                }
                $tmp=[
                    "name"=>$table->getName(),
                    "actual_FK"=>$table->getForeignKeys(),
                    "actual_indexes"=>array_keys($table->getIndexes()), 
                    "actual_columns"=>$columns,
                    "project"=>$connection->project,
                    "connection_name"=>$connection->name
                ];
                $tables[]   =  $tmp;
            }
            $connection->tables()->createMany($tables);
        }catch(\Exception $e){
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return $request->merge(['status'=>'get tables success', 'tables'=>$tables]);
    }
    private function getColumns($request){
        try{
            $conn = $this->getConnection($request->connection);
            $conn->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $data = $conn->getDoctrineSchemaManager()->listTableColumns($request->column);
            $columns = [];
            foreach ($data as $column) {
                $columns[]= $column->getName() . ' : ' . $column->getType() ;
            }
        }catch(\Exception $e){
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return $request->merge(['status'=>'get all columns', 'columns'=>$columns]);
    }

    private function addColumn($request, $tableName, $column=null){
        try{
            if (!Type::hasType('double')) {
                    Type::addType('double', FloatType::class);
            }
            if($column){
                $conn = $this->getConnection($request->connection)->getSchemaBuilder();
                // $conn->disableForeignKeyConstraints();
                $column = (object)$column;
                $conn->table($tableName, function($table) use($column)
                {
                    $datatype = $column->datatype;
                    $name = $column->name;
                    $table->$datatype($name);
                });
                // $conn->enableForeignKeyConstraints();
            }
        }catch(\Exception $e){
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return $request->merge(['status'=>"add Column to $tableName OK"]);
    }
    private function dropColumn($request, $tableName, $column=null){
        try{
            if($column){
                $conn = $this->getConnection($request->connection)->getSchemaBuilder();
                // $conn->disableForeignKeyConstraints();
                $column = (object)$column;
                $conn->table($tableName, function($table) use($column)
                {
                    $name = $column->name;
                    $table->dropColumn($name);
                });
                // $conn->enableForeignKeyConstraints();
            }
        }catch(\Exception $e){
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return $request->merge(['status'=>"drop column from $tableName OK"]);
    }

    private function updateColumn($request, $tableName, $column=null){
        try{
            if (!Type::hasType('double')) {
                Type::addType('double', FloatType::class);
            }
            if($column){
                $conn = $this->getConnection($request->connection)->getSchemaBuilder();
                // $conn->disableForeignKeyConstraints();
                $column = (object)$column;
                $conn->table($tableName, function($table) use($column)
                {
                    $datatype = $column->datatype;
                    $name = $column->name;
                    $to = $column->to;
                    if($name!=$to){
                        $table->renameColumn($name,$to);
                    }
                });
    
                $conn->table($tableName, function($table) use($column)
                {
                    $datatype = $column->datatype;
                    $name = $column->name;
                    $to = $column->to;
                    $table->$datatype($to)->change();
                });
                // $conn->enableForeignKeyConstraints();
            }
        }catch(\Exception $e){
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return $request->merge(['status'=>"update column on $tableName OK"]);
    }

    private function coba($request){
        return \Carbon\Carbon::now()->toDateTimeString();
    }
    public function index(Request $request, $query){
        switch($query){
            case "check": 
                return $this->check($request);
                break;
            case "tables": 
                return $this->getTables($request);
                break;
            case "table-copy": 
                return $this->getActualTables($request);
                break;
            case "table-create": 
                return $this->migrate($request);
                break;
            case "table-rename": 
                return $this->renameTable($request);
                break;
            case "table-drop": 
                return $this->dropTable($request);
                break;
            case "columns": 
                return $this->getColumns($request);
                break;
            case "coba": 
                return $this->coba($request);
                break;
            default: 
                return response()->json(["status"=>"query salah"],422);
        }
    }

    public function get(Request $request, $query, $var){
        switch($query){
            case "table": 
                return $this->readTables($var);
            default: 
                return response()->json(["status"=>"query salah"],422);
        }
    }

    private function readTables($id){
        try{
            $tables = \App\Models\Laradev\LaradevDBConnections::with("tables")->find($id);
        }catch(\Exception $e){
            return response()->json(['status'=>$e->getMessage()],422);
        }
        return response()->json($tables);
    }
}

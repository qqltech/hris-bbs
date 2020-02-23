<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Schema;

class PLSQL {

	private $when="";
	private $table;
	private $time="before" ;
	private $action;
	private $query;
	private $code;
	private $declare="";

	public static function table($table){
		$a= new PLSQL();
		return $a->setTable($table);
	}

	public function setTable($table){
		$this->table = $table;
		return $this;
	}

	public function before($action){
		$this->action = $action;
		return $this;
	}

	public function after($action){
		$this->action = $action;
		$this->time = "after";
		return $this;
	}

	public function when($string){
		if($this->when != ""){
			$string= " AND ".$string;
		}
		$this->when .= $string;
		return $this;
	}

	public function whenOr($string){
		if($this->when != ""){
			$string= " OR ".$string;
		}
		$this->when .= $string;
		return $this;
	}

	public function script($text){
		
		$this->code = $text;
		return $this;
	}

	public function declare($variable){
		$text="";
		if(Schema::getConnection()->getDriverName()=='mysql'){
			if(is_array($variable)){
				foreach($variable  as $key=>$isi){
					$text .= "DECLARE $key $isi;";
				}
			}
			if($this->declare == ""){
				$text= $text;
			}
		}elseif(Schema::getConnection()->getDriverName()=='pgsql'){
			if(is_array($variable)){
				foreach($variable  as $key=>$isi){
					if( strpos(strtolower($isi), "double") !== false ){
						$isi= $isi." precision";
					}
					$text .= "$key $isi;";
				}
			}
			if($this->declare == ""){
				$text= "DECLARE ".$text;
			}
		}

		$this->declare .= $text;
		return $this;
	}

	public function pgsqlCreate() {
		if($this->when !== ""){
			$this->when = " when (".$this->when.") ";
		}
		 $this->query="
		 DROP TRIGGER IF EXISTS ".$this->table."_".$this->time."_".$this->action." ON $this->table;
		 DROP FUNCTION IF EXISTS fungsi_".$this->table."_".$this->time."_".$this->action."();
		 CREATE OR REPLACE FUNCTION fungsi_".$this->table."_".$this->time."_".$this->action."() 
		 \n
		 RETURNS trigger
		 \n
		 LANGUAGE 'plpgsql'
		 AS
		 $$
		 $this->declare
		 BEGIN
		 $this->code
		 RETURN NEW;
		 END$$; 
		 
		 CREATE TRIGGER ".$this->table."_".$this->time."_".$this->action."
		 ".$this->time." ".$this->action." ON ".$this->table."
		 \n
		 FOR EACH ROW
		 \n
		 $this->when
		 \n
		 EXECUTE PROCEDURE fungsi_".$this->table."_".$this->time."_".$this->action."();
		 ";
		  return $this->query;
	 }
	 
	 public function drop() {
		 $this->query="
		 DROP TRIGGER IF EXISTS ".$this->table."_".$this->time."_".$this->action." ON $this->table;
		 DROP FUNCTION IF EXISTS fungsi_".$this->table."_".$this->time."_".$this->action."();	
		 ";
		 try{
			\DB::unprepared($this->query);
		 }catch(\Exception $e){
			$this->query="
				DROP TRIGGER IF EXISTS ".$this->table."_".$this->time."_".$this->action.";
				DROP FUNCTION IF EXISTS fungsi_".$this->table."_".$this->time."_".$this->action."();		
			";
			\DB::unprepared($this->query);
			return "mysql trigger/func/view terhapus";
		 }
	 }

	 public function mysqlCreate() {
		if($this->when !== ""){
			$this->when = " when (".$this->when.") ";
		}
		 $this->query="
		 DROP TRIGGER IF EXISTS ".$this->table."_".$this->time."_".$this->action." ;
		 CREATE TRIGGER ".$this->table."_".$this->time."_".$this->action."
		 ".$this->time."
		 ".$this->action." ON ".$this->table."
		 FOR EACH ROW
		 BEGIN 
			$this->declare
			$this->code 
		 END;
		 ";
		  return $this->query;
	 }

	 public function create(){
		if(Schema::getConnection()->getDriverName()=='mysql'){
			$query = $this->mysqlCreate();
		}elseif(Schema::getConnection()->getDriverName()=='pgsql'){
			$query = $this->pgsqlCreate();
		}
		\DB::unprepared($query);
	 }

	public function createView(){
		\DB::unprepared("
			CREATE OR REPLACE VIEW ".$this->table." AS ".$this->code.";");
	}

	public function createMaterializedView(){
		\DB::unprepared("
			CREATE MATERIALIZED VIEW ".$this->table." AS ".$this->code.";");
	}

	public function index($column, $type="btree"){
		\DB::unprepared("
			CREATE INDEX ".$this->table."_$column ON $this->table USING $type($column);");
	}
}
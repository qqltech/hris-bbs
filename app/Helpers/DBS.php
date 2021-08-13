<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Schema;

class DBS extends \Illuminate\Support\Facades\DB {
	private $vars="";
	private $table_tujuan;
	private $data;
	private $query;
	private $where;
	public static function command($query)
	{
		$a= new DBS();
		return $a->setvar($query);
	}
	public static function invoke_table($table){
		$a= new DBS();
		return $a->setTable($table);
	}
	public function setTable($table){
		$this->table_tujuan = $table;
		return $this;
	}
	public function setvar($query){
		if(is_array($query)){
			$merged=[];
			foreach($query as $row){
				if(!is_string($row)){
					$merged[]=str_replace('"',"",str_replace_array('?', $row->getBindings(), $row->toSql()).";\n");
				}else{
					if( getDriver() == 'pgsql' && strpos(" ".$row, 'UPDATE') == FALSE){
						$row = str_replace("set "," ",$row);
						$row = str_replace("SET "," ",$row);
				}
					$merged[]=$row." ";
				}
			}
			$query=implode($merged);
		}else{
			if(strpos(" ".$query, 'INSERT') == FALSE){
				$query=str_replace_array('?', $query->getBindings(), $query->toSql()).";";
			}
		}
		if($this->vars !==""){
			$this->vars .= $query;
		}else{
			$this->vars = $query;
		}
		return $this;
	}
	public function create(){
		return $this->vars;
	}

	public function invoke_insert($variable){
		$kolom=[];
		$value=[];
		if(is_array($variable)){
			foreach($variable  as $key => $isi){
				$kolom[]= $key;
				$value[]= $isi;
			}
		}
		$kolom="(".implode(",",$kolom).") ";
		$value="VALUES(".implode(",",$value).") ";

		$this->query="INSERT INTO $this->table_tujuan $kolom $value;";

		return $this->query;
	}
	public function invoke_delete($case){
		$this->query="DELETE FROM $this->table_tujuan WHERE $case;";
		return $this->query;
	}
	public function invoke_update($variable){
		$text=[];
		if(is_array($variable)){
			foreach($variable  as $key => $isi){
				$text[] = "$key = $isi";
			}
		}
		$text= implode(", ",$text);

		$this->query="UPDATE $this->table_tujuan SET $text WHERE $this->where;";

		return $this->query;
	}

	public function invoke_where($string){
		if($this->where != ""){
			$string= " AND ".$string;
		}
		$this->where .= $string;
		return $this;
	}

	public function invoke_whereOr($string){
		if($this->where != ""){
			$string= " OR ".$string;
		}
		$this->where .= $string;
		return $this;
	}

	public static function getTriggers($table){
		$data = null;
		if( getDriver() == 'pgsql'){
			$data = new \Staudenmeir\LaravelCte\Query\Builder(\DB::connection());
			$data = $data->from("information_schema.triggers")
			->select("prosrc as action_statement","event_object_table",
					"action_timing","event_manipulation","trigger_name")
			->withExpression('mytrigger', 
					\DB::table('pg_trigger')
					->join("pg_proc","pg_proc.oid", "=", "pg_trigger.tgfoid")
					->select("pg_trigger.tgname","pg_proc.prosrc")
				)
			->join('mytrigger', 'tgname', '=', 'trigger_name');
		}elseif( getDriver() == 'mysql'){
			$data = \DB::table("information_schema.triggers")
			->select("action_statement","event_object_table",
					"action_timing","event_manipulation","trigger_name");
			
		}else{
			return [];
		}
		return $data->where('event_object_table',$table)->get();
	}
}
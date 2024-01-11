<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_schedules extends Model
{   
    use ModelTrait;

    protected $table    = 'default_schedules';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["title","every","every_param","class_name","func_name","parameter_values","days","start_at","end_at","note","status","last_executed_at","end_executed_at","created_at","updated_at"];

    public $columns     = ["id","title","every","every_param","class_name","func_name","parameter_values","days","start_at","end_at","note","status","last_executed_at","end_executed_at","created_at","updated_at"];
    public $columnsFull = ["id:bigint","title:string:191","every:string:191","every_param:string:191","class_name:string:191","func_name:string:191","parameter_values:json","days:json","start_at:string:191","end_at:string:191","note:text","status:string:191","last_executed_at:datetime","end_executed_at:datetime","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = ["default_schedules_failed"];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [
    "title"=> "unique:default_schedules,title"
	];
    public $required    = ["title","every","class_name","func_name"];
    public $createable  = ["title","every","every_param","class_name","func_name","parameter_values","days","start_at","end_at","note","status","last_executed_at","end_executed_at","created_at","updated_at"];
    public $updateable  = ["title","every","every_param","class_name","func_name","parameter_values","days","start_at","end_at","note","status","last_executed_at","end_executed_at","created_at","updated_at"];
    public $searchable  = ["title","every","every_param","class_name","func_name","parameter_values","days","start_at","end_at","note","status","last_executed_at","end_executed_at","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function default_schedules_failed() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\default_schedules_failed', 'default_schedules_id', 'id');
    }
    
    
}

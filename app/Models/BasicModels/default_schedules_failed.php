<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_schedules_failed extends Model
{   
    use ModelTrait;

    protected $table    = 'default_schedules_failed';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["default_schedules_id","title","note","status","created_at","updated_at"];

    public $columns     = ["id","default_schedules_id","title","note","status","created_at","updated_at"];
    public $columnsFull = ["id:bigint","default_schedules_id:bigint","title:string:191","note:text","status:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["default_schedules.id=default_schedules_failed.default_schedules_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["default_schedules_id","title"];
    public $createable  = ["default_schedules_id","title","note","status","created_at","updated_at"];
    public $updateable  = ["default_schedules_id","title","note","status","created_at","updated_at"];
    public $searchable  = ["default_schedules_id","title","note","status","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function default_schedules() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_schedules', 'default_schedules_id', 'id');
    }
}

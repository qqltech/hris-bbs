<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_params extends Model
{   
    use ModelTrait;

    protected $table    = 'default_params';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["name","note","prepared_query","params","modul","editor_name","is_active","created_at","updated_at"];

    public $columns     = ["id","name","note","prepared_query","params","modul","editor_name","is_active","created_at","updated_at"];
    public $columnsFull = ["id:bigint","name:string:100","note:string:255","prepared_query:text","params:string:191","modul:string:50","editor_name:string:191","is_active:boolean","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [
    "name"=> "unique:default_params,name"
	];
    public $required    = ["name","prepared_query","modul"];
    public $createable  = ["name","note","prepared_query","params","modul","editor_name","is_active","created_at","updated_at"];
    public $updateable  = ["name","note","prepared_query","params","modul","editor_name","is_active","created_at","updated_at"];
    public $searchable  = ["name","note","prepared_query","params","modul","editor_name","is_active","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

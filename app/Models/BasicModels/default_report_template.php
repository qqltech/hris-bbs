<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_report_template extends Model
{   
    use ModelTrait;

    protected $table    = 'default_report_template';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["name","default","template","created_at","updated_at"];

    public $columns     = ["id","name","default","template","created_at","updated_at"];
    public $columnsFull = ["id:bigint","name:string:100","default:string:100","template:text","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["name","default","template","created_at","updated_at"];
    public $updateable  = ["name","default","template","created_at","updated_at"];
    public $searchable  = ["name","default","template","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

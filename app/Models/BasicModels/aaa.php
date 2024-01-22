<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class aaa extends Model
{   
    use ModelTrait;

    protected $table    = 'aaa';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["samplecolumn","hey"];

    public $columns     = ["id","samplecolumn","created_at","updated_at","hey"];
    public $columnsFull = ["id:bigint","samplecolumn:string:100","created_at:datetime","updated_at:datetime","hey:string:191"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["hey"];
    public $createable  = ["samplecolumn","hey"];
    public $updateable  = ["samplecolumn","hey"];
    public $searchable  = ["id","samplecolumn","created_at","updated_at","hey"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

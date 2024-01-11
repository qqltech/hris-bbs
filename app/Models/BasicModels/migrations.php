<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class migrations extends Model
{   
    use ModelTrait;

    protected $table    = 'migrations';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["migration","batch"];

    public $columns     = ["id","migration","batch"];
    public $columnsFull = ["id:integer","migration:string:191","batch:integer"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["migration","batch"];
    public $createable  = ["migration","batch"];
    public $updateable  = ["migration","batch"];
    public $searchable  = ["migration","batch"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

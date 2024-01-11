<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_jobs extends Model
{   
    use ModelTrait;

    protected $table    = 'default_jobs';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["queue","payload","attempts","reserved_at","available_at","created_at"];

    public $columns     = ["id","queue","payload","attempts","reserved_at","available_at","created_at"];
    public $columnsFull = ["id:bigint","queue:string:191","payload:text","attempts:smallint","reserved_at:integer","available_at:integer","created_at:integer"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["queue","payload","attempts","available_at"];
    public $createable  = ["queue","payload","attempts","reserved_at","available_at","created_at"];
    public $updateable  = ["queue","payload","attempts","reserved_at","available_at","created_at"];
    public $searchable  = ["queue","payload","attempts","reserved_at","available_at","created_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

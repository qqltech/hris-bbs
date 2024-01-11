<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_jobs_failed extends Model
{   
    use ModelTrait;

    protected $table    = 'default_jobs_failed';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["connection","queue","payload","exception","failed_at"];

    public $columns     = ["id","connection","queue","payload","exception","failed_at"];
    public $columnsFull = ["id:bigint","connection:text","queue:text","payload:text","exception:text","failed_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["connection","queue","payload","exception","failed_at"];
    public $createable  = ["connection","queue","payload","exception","failed_at"];
    public $updateable  = ["connection","queue","payload","exception","failed_at"];
    public $searchable  = ["connection","queue","payload","exception","failed_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

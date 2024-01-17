<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class oauth_personal_access_clients extends Model
{   
    use ModelTrait;

    protected $table    = 'oauth_personal_access_clients';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["client_id","created_at","updated_at"];

    public $columns     = ["id","client_id","created_at","updated_at"];
    public $columnsFull = ["id:integer","client_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["client_id"];
    public $createable  = ["client_id","created_at","updated_at"];
    public $updateable  = ["client_id","created_at","updated_at"];
    public $searchable  = ["client_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

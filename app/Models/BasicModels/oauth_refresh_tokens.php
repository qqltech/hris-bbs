<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class oauth_refresh_tokens extends Model
{   
    use ModelTrait;

    protected $table    = 'oauth_refresh_tokens';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["access_token_id","revoked","expires_at"];

    public $columns     = ["id","access_token_id","revoked","expires_at"];
    public $columnsFull = ["id:string:100","access_token_id:string:100","revoked:boolean","expires_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["access_token_id","revoked"];
    public $createable  = ["access_token_id","revoked","expires_at"];
    public $updateable  = ["access_token_id","revoked","expires_at"];
    public $searchable  = ["access_token_id","revoked","expires_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

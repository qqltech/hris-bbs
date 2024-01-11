<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class oauth_auth_codes extends Model
{   
    use ModelTrait;

    protected $table    = 'oauth_auth_codes';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["user_id","client_id","scopes","revoked","expires_at"];

    public $columns     = ["id","user_id","client_id","scopes","revoked","expires_at"];
    public $columnsFull = ["id:string:100","user_id:integer","client_id:integer","scopes:text","revoked:boolean","expires_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["client_id","revoked"];
    public $createable  = ["user_id","client_id","scopes","revoked","expires_at"];
    public $updateable  = ["user_id","client_id","scopes","revoked","expires_at"];
    public $searchable  = ["user_id","client_id","scopes","revoked","expires_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

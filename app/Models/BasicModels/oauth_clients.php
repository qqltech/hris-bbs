<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class oauth_clients extends Model
{   
    use ModelTrait;

    protected $table    = 'oauth_clients';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["user_id","name","secret","provider","redirect","personal_access_client","password_client","revoked","created_at","updated_at"];

    public $columns     = ["id","user_id","name","secret","provider","redirect","personal_access_client","password_client","revoked","created_at","updated_at"];
    public $columnsFull = ["id:integer","user_id:integer","name:string:191","secret:string:100","provider:string:191","redirect:text","personal_access_client:boolean","password_client:boolean","revoked:boolean","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["name","secret","redirect","personal_access_client","password_client","revoked"];
    public $createable  = ["user_id","name","secret","provider","redirect","personal_access_client","password_client","revoked","created_at","updated_at"];
    public $updateable  = ["user_id","name","secret","provider","redirect","personal_access_client","password_client","revoked","created_at","updated_at"];
    public $searchable  = ["user_id","name","secret","provider","redirect","personal_access_client","password_client","revoked","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

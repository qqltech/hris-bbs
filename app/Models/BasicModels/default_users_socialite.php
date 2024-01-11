<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_users_socialite extends Model
{   
    use ModelTrait;

    protected $table    = 'default_users_socialite';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["default_users_id","provider","username","email","token","avatar","status","created_at","updated_at"];

    public $columns     = ["id","default_users_id","provider","username","email","token","avatar","status","created_at","updated_at"];
    public $columnsFull = ["id:bigint","default_users_id:bigint","provider:string:191","username:string:191","email:string:191","token:string:191","avatar:string:255","status:string:20","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["default_users.id=default_users_socialite.default_users_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["default_users_id","provider","status"];
    public $createable  = ["default_users_id","provider","username","email","token","avatar","status","created_at","updated_at"];
    public $updateable  = ["default_users_id","provider","username","email","token","avatar","status","created_at","updated_at"];
    public $searchable  = ["default_users_id","provider","username","email","token","avatar","status","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function default_users() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'default_users_id', 'id');
    }
}

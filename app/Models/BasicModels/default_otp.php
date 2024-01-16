<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_otp extends Model
{   
    use ModelTrait;

    protected $table    = 'default_otp';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["type","to","ip_address","code","jenis","redaksi","client_path","note","user_id","expired_at","verified_at","created_at","updated_at"];

    public $columns     = ["id","type","to","ip_address","code","jenis","redaksi","client_path","note","user_id","expired_at","verified_at","created_at","updated_at"];
    public $columnsFull = ["id:bigint","type:string:100","to:string:100","ip_address:string:100","code:string:100","jenis:string:191","redaksi:string:191","client_path:string:191","note:string:191","user_id:bigint","expired_at:datetime","verified_at:datetime","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["type","to","ip_address","code","redaksi"];
    public $createable  = ["type","to","ip_address","code","jenis","redaksi","client_path","note","user_id","expired_at","verified_at","created_at","updated_at"];
    public $updateable  = ["type","to","ip_address","code","jenis","redaksi","client_path","note","user_id","expired_at","verified_at","created_at","updated_at"];
    public $searchable  = ["type","to","ip_address","code","jenis","redaksi","client_path","note","user_id","expired_at","verified_at","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_divisi extends Model
{   
    use ModelTrait;

    protected $table    = 'm_divisi';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_comp_id","m_dir_id","nama","desc","is_active","creator_id","last_editor_id"];

    public $columns     = ["id","m_comp_id","m_dir_id","nama","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_comp_id:bigint","m_dir_id:bigint","nama:string:100","desc:text","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_comp.id=m_divisi.m_comp_id","m_dir.id=m_divisi.m_dir_id","default_users.id=m_divisi.creator_id","default_users.id=m_divisi.last_editor_id"];
    public $details     = [];
<<<<<<< HEAD
    public $heirs       = ["m_dept","m_kary","m_lembur","m_spd","m_standart_gaji","m_tunj_kemahalan","t_final_gaji_det","t_grup_kerja","t_jadwal_kerja","t_mutasi","t_mutasi","t_pelamar","t_perhitungan_gaji","t_spd"];
=======
    public $heirs       = ["m_dept","m_lembur","m_standart_gaji","m_kary","m_tunj_kemahalan","m_spd","t_final_gaji_det","t_jadwal_kerja","t_pelamar","t_perhitungan_gaji","t_grup_kerja","t_mutasi","t_mutasi","t_spd"];
>>>>>>> 948888082c55682e4f2fa49dea57e435c4a70be9
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["nama","is_active"];
    public $createable  = ["m_comp_id","m_dir_id","nama","desc","is_active","creator_id","last_editor_id"];
    public $updateable  = ["m_comp_id","m_dir_id","nama","desc","is_active","creator_id","last_editor_id"];
    public $searchable  = ["id","m_comp_id","m_dir_id","nama","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function m_dir() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_dir', 'm_dir_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
}

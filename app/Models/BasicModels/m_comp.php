<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_comp extends Model
{   
    use ModelTrait;

    protected $table    = 'm_comp';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["nama","is_active","desc","creator_id","last_editor_id"];

    public $columns     = ["id","nama","is_active","desc","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nama:string:50","is_active:boolean","desc:text","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["default_users.id=m_comp.creator_id","default_users.id=m_comp.last_editor_id"];
    public $details     = [];
<<<<<<< HEAD
<<<<<<< HEAD
    public $heirs       = ["m_divisi","m_dir","m_general","m_jam_kerja","m_jarak_kota","m_kary","m_kary_det_bhs","m_kary_det_kartu","m_kary_det_kel","default_users","generate_approval","generate_approval_log","m_approval","m_contract","m_kary_det_org","m_kary_det_pel","m_kary_det_pemb","m_kary_det_pend","m_kary_det_pk","m_kary_det_pres","m_knd_dinas","m_libur_nasional","m_lokasi","m_menu","m_pengesahan_doc","m_pengurangan_gaji","m_periode","m_posisi","m_role","m_role_access","m_role_det","m_spd","m_spd_det_transport","m_standart_gaji","m_tunj_kemahalan","m_zona","presensi_absensi","t_cuti","t_final_gaji","t_grup_kerja","t_hasil_tes","t_jadwal_kerja","t_lembur","t_loker","t_mutasi","t_pelamar","t_potongan","t_riwayat_posisi","t_rpd","t_sgp","t_spd"];
=======
    public $heirs       = ["default_users","generate_approval","generate_approval_log","m_approval","m_contract","m_dir","m_general","m_divisi","m_jarak_kota","m_jam_kerja","m_kary_det_bhs","m_kary_det_kartu","m_kary_det_kel","m_kary_det_org","m_kary_det_pel","m_kary_det_pemb","m_kary_det_pend","m_kary_det_pk","m_kary_det_pres","m_knd_dinas","m_lokasi","m_libur_nasional","m_menu","m_pengesahan_doc","m_pengurangan_gaji","m_periode","m_role_access","m_standart_gaji","m_role","m_role_det","m_kary","m_tunj_kemahalan","m_spd_det_transport","m_posisi","m_spd","m_zona","presensi_absensi","t_final_gaji","t_cuti","t_jadwal_kerja","t_pelamar","t_grup_kerja","t_loker","t_hasil_tes","t_mutasi","t_rpd","t_riwayat_posisi","t_potongan","t_sgp","t_spd","t_lembur"];
>>>>>>> 948888082c55682e4f2fa49dea57e435c4a70be9
=======
    public $heirs       = ["m_kary_det_pel","m_pengesahan_doc","t_cuti","m_periode","t_final_gaji","m_kary_det_kartu","t_potongan_det_bayar","m_kary_det_pemb","m_general","m_kary","default_users","t_spd","m_dir","t_potongan","t_grup_kerja","m_menu","m_kary_det_bhs","m_kary_det_pk","m_pengurangan_gaji","t_pelamar","m_kary_det_kel","t_hasil_tes","t_riwayat_posisi","m_kary_det_org","m_approval","m_divisi","m_contract","m_kary_det_pend","presensi_lokasi","t_rpd","generate_approval","presensi_absensi","m_role","t_lembur","m_role_access","m_role_det","m_lokasi","m_zona","m_jam_kerja","t_sgp","generate_approval_log","t_loker","m_tunj_kemahalan","m_knd_dinas","m_libur_nasional","m_spd","t_jadwal_kerja","m_spd_det_transport","m_posisi","m_standart_gaji","t_mutasi","m_kary_det_pres","m_jarak_kota"];
>>>>>>> parent of 9488880 (update 16-01-24)
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["is_active"];
    public $createable  = ["nama","is_active","desc","creator_id","last_editor_id"];
    public $updateable  = ["nama","is_active","desc","creator_id","last_editor_id"];
    public $searchable  = ["id","nama","is_active","desc","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
}

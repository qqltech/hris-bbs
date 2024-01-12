<?php
namespace App\Cores;

use App\Models\CustomModels\m_dir;
use App\Models\CustomModels\m_comp;
use App\Models\CustomModels\m_pengguna;

class Response
{
    public function store($model){
        if(isset($model['platform'])){
            if(!$model['m_dir_id']){
                $model['direktorat'] = 'ADMIN INSTANSI';
            }else{
                $model['direktorat'] = m_dir::where('id', @$model['m_dir_id'] ?? 9)->pluck('nama')->first();
            }
            $model['company'] = m_comp::where('id', @$model['m_comp_id'] ?? 9)->pluck('nama')->first();
            $model['m_kary_id'] = m_pengguna::where('default_user_id', $model['id'])->pluck('m_kary_id')->first();
            $model['avatar'] = url('')."/sjg.png";
        }
        return $model;
    }
}
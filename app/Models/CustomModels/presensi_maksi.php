<?php

namespace App\Models\CustomModels;

class presensi_maksi extends \App\Models\BasicModels\presensi_maksi
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function custom_get_maksi(){
        $m_kary_id = auth()->user()->m_kary_id;
        $data = [];
        $data = $this->where('tanggal', date('Y-m-d'))
                ->whereRaw("
                    presensi_maksi.id not in(select d.presensi_maksi_id from presensi_maksi_det d where d.m_kary_id = ?)
                ", [$m_kary_id ?? 0])->orderBy('id','desc')->first();
        if($data){
            // GROUP TIPE MENU
            $groupedData = 
                array_reduce(@$data->lauk ?? [], function ($carry, $item) {
                    $key = @$item['tipe_lauk_id'] . '-' . @$item['tipe_lauk_id'];
                    if (!isset($carry[$key])) {
                        $carry[$key] = @$item;
                    }
                    return $carry;
                }, []);
            $group_data = array_values($groupedData);

            $data->group_data = array_map(function ($item) use($data){
                $groupedDataLauk = 
                    array_reduce(@$data->lauk ?? [], function ($carry, $itm) use($item){
                        if (@$itm['tipe_lauk_id'] == @$item['tipe_lauk_id']) {
                            // Filter data lauk berdasarkan tipe_lauk.value yang sesuai
                            $carry[] = $itm;
                        }
                        return $carry;
                    }, []);
                
                return [
                    'tipe_lauk_id' => @$item['tipe_lauk_id'],
                    'tipe_lauk' => m_general::where('id', $item['tipe_lauk_id'])->pluck('value')->first(),
                    'detail'   => array_values($groupedDataLauk)
                ];
            }, $group_data);

            // hapus key lauk karena sudah dipakai semua valuenya
            unset($data->lauk);
        }



        return response(['data'=>$data]);
    }
}
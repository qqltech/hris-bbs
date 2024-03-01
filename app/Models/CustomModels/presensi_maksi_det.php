<?php

namespace App\Models\CustomModels;

class presensi_maksi_det extends \App\Models\BasicModels\presensi_maksi_det
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function custom_pesan_maksi($req)
    {
        $m_kary_id = default_users::where('id', auth()->user()->id)->pluck('m_kary_id')->first();

        $check_exists_maksi = presensi_maksi::where('id',$req->presensi_maksi_id)->where('presensi_maksi.tanggal', date('Y-m-d'))->exists();

        if(!$check_exists_maksi) return response([
            'message' => 'Menu makan siang hari ini sudah tidak aktif',
            'errors' => ['Menu makan siang hari ini sudah tidak aktif']
        ], 404);

        $check_exists = $this->join('presensi_maksi','presensi_maksi.id','presensi_maksi_det.presensi_maksi_id')
            ->where('presensi_maksi_det.m_kary_id',$m_kary_id)
            ->where('presensi_maksi.tanggal', date('Y-m-d'))->exists();

        if($check_exists) return response([
            'message' => 'Anda sudah pesan makan hari ini, coba kembali besok - '.$m_kary_id,
            'errors' => ['Anda sudah pesan makan hari ini, coba kembali besok']
        ], 422);

        $this->create([
            'presensi_maksi_id' => $req->presensi_maksi_id,
            'm_kary_id' => $m_kary_id,
            'lauk' => json_encode($req->pesan)
        ]);

        if($check_exists) return response([
            'message' => 'Pesan makan berhasil, terimakasih :)',
            'errors' => ['Pesan makan berhasil, terimakasih :)']
        ]);
    }

    
}
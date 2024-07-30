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

        $currentDate = new \DateTime();
        $currentDate->modify('+1 day');
        $nextDay = $currentDate->format('Y-m-d');

        // $check_exists_maksi = presensi_maksi::where('id',$req->presensi_maksi_id)
        //     ->where('status','POSTED')->where('presensi_maksi.tanggal', $nextDay)->exists();

        // if(!$check_exists_maksi) return response([
        //     'message' => 'Menu makan siang hari ini sudah tidak aktif',
        //     'errors' => ['Menu makan siang hari ini sudah tidak aktif']
        // ], 422);

        // $check_exists = $this->join('presensi_maksi','presensi_maksi.id','presensi_maksi_det.presensi_maksi_id')
        //     ->where('presensi_maksi_det.m_kary_id',$m_kary_id)
        //     ->where('presensi_maksi.tanggal', $nextDay)->exists();

        // if($check_exists) return response([
        //     'message' => 'Anda sudah pesan makan hari ini, coba kembali besok - '.$m_kary_id,
        //     'errors' => ['Anda sudah pesan makan hari ini, coba kembali besok']
        // ], 422);

        $this->create([
            'presensi_maksi_id' => $req->presensi_maksi_id,
            'm_kary_id' => $m_kary_id,
            'lauk' => json_encode($req->pesan)
        ]);

        return response([
            'message' => 'Pesan makan berhasil, terimakasih :)',
            'errors' => ['Pesan makan berhasil, terimakasih :)']
        ]);
    }

    public function custom_cancel($req)
    {
        $m_kary_id = auth()->user()->m_kary_id;
        $currentDate = new \DateTime();
        $currentDate->modify('+1 day');
        $nextDay = $currentDate->format('Y-m-d');

        $id_det = $this
            ->select('presensi_maksi_det.id')->join('presensi_maksi','presensi_maksi.id','presensi_maksi_det.presensi_maksi_id')
            ->where('presensi_maksi_det.m_kary_id', $m_kary_id)
            ->where('presensi_maksi.tanggal', $nextDay)->pluck('id')->first();

        $this->find($id_det)->delete();

        return response([
            'message' => 'Pesan makan berhasil dibatalkan :)',
            'errors' => ['Pesan makan berhasil dibatalkan :)']
        ]);
    }

    
}
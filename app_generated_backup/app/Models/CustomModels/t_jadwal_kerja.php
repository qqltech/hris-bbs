<?php

namespace App\Models\CustomModels;

class t_jadwal_kerja extends \App\Models\BasicModels\t_jadwal_kerja
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData  = array_merge( $arrayData,[
            'nomor' => $this->helper->generateNomor('KODE JADWAL KERJA')
        ]);
       
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function custom_generate(){
        $validator = \Validator::make(app()->request->all(), [
            'grup_kerja_id' => 'required'
        ]);
        if($validator->fails()) return $this->helper->responseValidate($validator);

        $grup_kerja = t_grup_kerja::find(req('grup_kerja_id') ?? 0);

        if(!$grup_kerja) return $this->helper->customResponse("Grup kerja id tersbut tidak ditemukan", 422);

        $date_from = $grup_kerja->date_from;
        $date_to = $grup_kerja->date_to;

        $date_from = new \DateTime($grup_kerja->date_from);
        $date_to = new \DateTime($grup_kerja->date_to);

        // Menghitung jumlah hari antara tanggal_awal dan tanggal_akhir
        $interval = $date_from->diff($date_to);
        $jumlah_hari = $interval->days;

        $data = [];
        for ($i = 0; $i <= $jumlah_hari; $i++) {
            $date = $date_from->format('Y-m-d');
            // Menambahkan satu hari untuk iterasi berikutnya
            $date_from->add(new \DateInterval('P1D'));

            $kary = m_kary::selectRaw("m_kary.*, m_posisi.desc_kerja posisi, m_jam_kerja.kode jam_kerja")
                    ->where('m_dept_id', $grup_kerja->m_dept_id)
                    ->join('m_posisi', 'm_posisi.id','m_kary.m_posisi_id')
                    ->join('m_jam_kerja', 'm_jam_kerja.id','m_kary.m_jam_kerja_id')
                    ->get();
            foreach($kary as $d){
                $data[] = [
                    'tanggal'   => $date,
                    'm_kary_id' => $d->id,
                    'nik' => $d->nik,
                    'nama_lengkap' => $d->nama_lengkap,
                    'm_posisi_id' => $d->m_posisi_id,
                    'posisi.nama' => $d->m_posisi_id,
                    'm_jam_kerja_id' => $d->m_jam_kerja_id,
                    'status'    => 'DRAFT'
                ];
            }
        }

        return $this->helper->customResponse('OK', 200, $data);
    }
}
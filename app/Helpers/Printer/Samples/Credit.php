<?php
namespace App\Helpers\Printer\Samples;
use App\Helpers\Printer\PrinterTable as Table;
use App\Helpers\Printer\Epson;

// TITLE: NOTA RETUR atau CREDIT NOTE
class Credit
{
    public function print( $data = [], $printerName = 'EPSONLQIT1', $isDebug=true){ 
        config(['isPrinting'=>!$isDebug]);
        $epsonCls = new Epson;
        $SC = "\x1b";
        $maxChars = 120;

        $data = [
            "page_idx" => @$data['page_idx']??1,
            "page_total" => @$data['page_total']??1,
            "_current_time" => @$data['current_time'] ?? strtoupper(date('d-M-Y H:i:s')),
            "_ou" => @$data['ou'] ?? 'PCI\\PCI - Pabrik Lebani',
            "_no" => @$data['no'] ?? 'CNEB-220620-000043',
            "_no_fp" => @$data['no_fp'] ?? '010.006-22.18365767',
            "_date_fp" => @$data['date_fp'] ?? '28-NOV-22',
            "_vendor" => @$data['vendor'] ?? "PT. ANEKA KARYA UNGGUL",
            "_vendor_address" => @$data['vendor_address'] ?? "G WALK SHOP HOUSES A 1 NO. 08 RT.000 RW.000 ONTAR SAMBIKEREP",
            "_vendor_npwp" => @$data['vendor_npwp'] ?? "72.331.728.6-604.000",

            "_cmp" => @$data['cmp'] ?? "PT. ANEKA KARYA UNGGUL",
            "_cmp_address" => @$data['cmp_address'] ?? "Jl. Panglima Sudirman No. 23-25",
            "_cmp_npwp" => @$data['cmp_npwp'] ?? "75.331.728.6-604.000",
            "_cmp_date_pengukuhan" => @$data['cmp_date_pengukuhan'] ?? "09-APR-2007",
            
            "_date" => @$data['date'] ?? "28-NOV-22",
            "_date_ttd" => @$data['date'] ?? "28-NOV-22",
            "_modul" => @$data['modul'] ?? "AP",
            "_count" => @$data['count'] ?? 1,
            "_total" => @$data['total'] ?? "3434.00",

            "_total_dpp" => @$data['total_dpp'] ?? "3434",
            "_total_potongan" => @$data['total_potongan'] ?? "2.00",
            "_total_ppn" => @$data['total_ppn'] ?? "1212",

            "details" =>[],
            "_details" => @$data['details']??[
                [
                    "no"=>"1",
                    "item" => "K MURANO 30.00X30.00",
                    "qty" => "-1.00",
                    "price" => "20,000",
                    "price_sell" => "20,000"
                ]
            ]
        ];
        
        $textData = "";
        $textData .= (new Table)->getOutputPolos( [// 107 = 120-(jumlah kolom*3)+1
            ['name'=>'a','key'=>'a','width'=>12],['name'=>'b','key'=>'b','width'=>83], ['name'=>'c','key'=>'c','width'=>15]
        ], [
            ["a"=>"PEMBELI", "b"=>"",  "c"=>""],
            ["a"=>"Nama", "b"=>$data['_vendor'],  "c"=>"Hal {$data['page_idx']}    Dari {$data['page_total']}"],
            ["a"=>"Alamat", "b"=>$data['_vendor_address'],  "c"=>""],
            ["a"=>"NPWP", "b"=>$data['_vendor_npwp'],  "c"=>""]
        ]);
        
        $textData .= "\n \n";
        $textData .= $SC.'E'; // bold
            $notaRetur = str_pad("NOTA RETUR", 40,' ', STR_PAD_LEFT);
            $textData .= $notaRetur;
            $textData .= $SC.'F'; // bold end
            $textData .= str_pad("Nomor    : ".$data['_no'], 119-strlen($notaRetur),' ', STR_PAD_LEFT);

        $textData .= "\n";
        $textData .= $SC.'E'; // bold
        $notaRetur = str_pad("ATAS FAKTUR PAJAK NO. ".$data['_no_fp'], 55,' ', STR_PAD_LEFT);
        $textData .= $notaRetur;
        $textData .= $SC.'F'; // bold end
        $textData .= str_pad("Tanggal Faktur Pajak ".$data['_date_fp']."    ", 120-strlen($notaRetur),' ', STR_PAD_LEFT);

        $textData .= "\n";

        $textData .= (new Table)->getOutputPolos( [// 107 = 120-(jumlah kolom*3)+1
            ['name'=>'a','key'=>'a','width'=>15],['name'=>'b','key'=>'b','width'=>60],['name'=>'c','key'=>'c','width'=>38]
        ], [
            ["a"=>"KEPADA PENJUAL", "b"=>"",  "c"=>""],
            ["a"=>"Nama", "b"=> ":    ".$data['_cmp'], "c"=>""],
            ["a"=>"Alamat", "b"=> ":    ".$data['_cmp_address'], "c"=>""],
            ["a"=>"NPWP", "b"=> ":    ".$data['_cmp_npwp'], "c"=>""],
            ["a"=>"S.K. Pengukuhan", "b"=> ":    ".$data['_cmp_npwp'], "c"=>"Tanggal Pengukuhan   ".$data['_cmp_date_pengukuhan']]
        ]);
        $textData .= "\n";

        $metaHeaders = [// 120 = 98+(jumlah kolom*3)+1 // 15+1=16 = 104
            ['name'=>'No','key'=>'auto','width'=>2],
            ['name'=>'Nama Barang / Jasa Kena Pajak','key'=>'item','width'=>53,'header_align'=>'center'],
            ['name'=>'Kwantum','key'=>'qty','width'=>10,'align'=>'right','header_align'=>'center'],
            ['name'=>'Harga Menurut Satuan','key'=>'price','width'=>19,'header_align'=>'center','align'=>'right'],
            ['name'=>'Harga Jual','key'=>'price_sell','width'=>19,'header_align'=>'center','align'=>'right']
        ];

        $textData .= (new Table)->getOutput($metaHeaders, $data['_details']);

        $textData .= "\n";

        $textData .= $SC.'E'; // bold

        $textData .= (new Table)->getOutputPolos( [// 107 = 120-(jumlah kolom*3)+1
            ['name'=>'a','key'=>'a','width'=>44],['name'=>'b','key'=>'b','width'=>47],['name'=>'c','key'=>'c','width'=>19, "align"=>"right"]
        ], [
            ["a"=>"Surabaya,     {$data['_date_ttd']}", "b"=>"JUMLAH HARGA JUAL",  "c"=>$data['_total']],
            ["a"=>"       Pembeli", "b"=> "DIKURANGI POTONGAN YANG TELAH DITERIMA", "c"=>$data['_total_potongan']],
            ["a"=>"", "b"=> "", "c"=>""],
            ["a"=>"", "b"=> "DASAR KENA PAJAK", "c"=>$data['_total_dpp']],
            ["a"=>"(                    )", "b"=> "  PPN YANG DIKURANGKAN = 11% x DASAR KENA PAJAK", "c"=>$data['_total_ppn']]    
        ]);

        $textData .= $SC.'F'; // bold


        return $epsonCls->generate( $textData, $config=[
            'printer'=> $printerName,
            'w'=>120, // width chars
            'formatter'=>function($p, $txt, $isHeading=false){
                if($isHeading){
                    $p->bold(false);
                }
                // MENGHILANGKAN GARIS VERTICAL
                // $txt = str_replace(chr(193), chr(196), $txt); // ┴ jadi ─
                // $txt = str_replace(chr(194), chr(196), $txt); // ┬ jadi  ─
                // $txt = str_replace(chr(192), ' ', $txt); // └ hilang
                // $txt = str_replace(chr(217), ' ', $txt); // ┘ hilang
                // $txt = str_replace(chr(191), ' ', $txt); // ┐ hilang
                // $txt = str_replace(chr(218), ' ', $txt); // ┌ hilang
                // $txt = str_replace(chr(179), ' ', $txt); // │ jadi |
                // GARIS VERTICAL END
                if(config('isPrinting')){
                    $txt = str_replace("-", chr(196), $txt); // - jadi ─
                }
                return $txt;
            }
        ], function($p)use($SC, $data, $maxChars){
            $p->setCPI($p::CPI_3);
            $p->setLineSpacing();
            // $p->setBarcode('12345');
        });
        
    }
}
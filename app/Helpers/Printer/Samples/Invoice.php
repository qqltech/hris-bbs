<?php
namespace App\Helpers\Printer\Samples;
use App\Helpers\Printer\PrinterTable as Table;
use App\Helpers\Printer\Epson;

// TITLE: Tanda Pemberitahuan Pembayaran
class InvoiceAP
{
    public function print( $data = [], $printerName = 'EPSONLQIT1', $isDebug=true ){
        config(['isPrinting'=>!$isDebug]);
        $epsonCls = new Epson;
        $SC = "\x1b";
        $maxChars = 120;

        $data = [
            "_current_time" => @$data['current_time'] ?? strtoupper(date('d-M-Y H:i:s')),
            "_ou" => @$data['ou'] ?? 'PCI\\PCI - Pabrik Lebani',
            "_no" => @$data['no'] ?? 'APB-STDI-220419-000033',
            "_vendor" => @$data['vendor'] ?? "Laritta Co.",
            "_date" => @$data['date'] ?? "28-NOV-22",
            "_modul" => @$data['modul'] ?? "AP",
            "_count" => @$data['count'] ?? 1,

            "_total" => @$data['total'] ?? "1.990,094,483.00",
            "_total_rounding" =>  @$data['total_rounding'] ?? "1.090,094,483.00",
            "_details" => @$data['details']??[
                [
                    "invoice_no"=>"FK22002687",
                    "po_no" => "FOBI-2112-000169",
                    "bpb_no" => "BPBI-2204-000753",
                    "due_date" => "06-JUL-22",
                    "amount" => "1,000,000,000.00",
                    "sj_no" => "-",
                ]
            ]
        ];
        

        $textData = "";        
        
        $metaHeaders = [// 120 = 98+(jumlah kolom*3)+1 // 15+1=16 = 104
            ['name'=>'Invoice No.','key'=>'invoice_no','width'=>12],
            ['name'=>'PO No.','key'=>'po_no','width'=>19,'separator'=>'-'],
            ['name'=>'BPB No.','key'=>'bpb_no','width'=>19,'separator'=>'-'],
            ['name'=>'Due Date','key'=>'due_date','width'=>9,'align'=>'center'],
            ['name'=>'Amount','key'=>'amount','width'=>25,'header_align'=>'right','align'=>'right'],
            ['name'=>'SJ No.','key'=>'sj_no','width'=>17,'separator'=>'-'],
        ];

        $textData .= (new Table)->getOutput($metaHeaders, $data['_details']);

        $textData .= "\n";
        $textData .= (new Table)->getOutput([// 120 = 98+(jumlah kolom*3)+1
            ['name'=>'  Rounding:','key'=>null,'width'=>11],
            ['name'=>'','key'=>null,'width'=>20],
            ['name'=>'','key'=>null,'width'=>19],
            ['name'=>'','key'=>null,'width'=>10],
            ['name'=> $data['_total_rounding'],'key'=>'','width'=>25,'header_align'=>'right','align'=>'right'],
            ['name'=>'','key'=>'','width'=>17],
        ]);
        $textData .= "\n";
        $textData .= (new Table)->getOutput([// 120 = 98+(jumlah kolom*3)+1
            ['name'=>'  TOTAL:','key'=>null,'width'=>11],
            ['name'=>'','key'=>null,'width'=>20],
            ['name'=>'','key'=>null,'width'=>19],
            ['name'=>'','key'=>null,'width'=>10],
            ['name'=> $data['_total'],'key'=>'','width'=>25,'header_align'=>'right','align'=>'right'],
            ['name'=>'','key'=>'','width'=>17],
        ]);
        $textData .= "\n \n";

        $textData .= (new Table)->getOutput([// 120 = 98+(jumlah kolom*3)+1
            ['name'=>'Penerima,','key'=>null,'width'=>34],
            ['name'=>'','key'=>null,'width'=>17],
            ['name'=>'','key'=>null,'width'=>17],
            ['name'=>'','key'=>null,'width'=>17],
            ['name'=>'','key'=>null,'width'=>17],
        ]);
        $textData .= "\n \n \n \n";

        $textData .= (new Table)->getOutput([// 120 = 98+(jumlah kolom*3)+1
            ['name'=>'  ----------------------','key'=>null,'width'=>34],
            ['name'=>'','key'=>null,'width'=>17],
            ['name'=>'','key'=>null,'width'=>17],
            ['name'=>'','key'=>null,'width'=>17],
            ['name'=>$data['_current_time'],'key'=>null,'width'=>17, 'header_align'=>'right'],
        ]);
        
        return $epsonCls->generate( $textData, $config=[
            'printer'=> $printerName,
            'w'=>120, // width chars
            'formatter'=>function($p, $txt){
                // MENGHILANGKAN GARIS VERTICAL
                $txt = str_replace(chr(193), chr(196), $txt); // ┴ jadi ─
                $txt = str_replace(chr(194), chr(196), $txt); // ┬ jadi  ─
                $txt = str_replace(chr(192), ' ', $txt); // └ hilang
                $txt = str_replace(chr(217), ' ', $txt); // ┘ hilang
                $txt = str_replace(chr(191), ' ', $txt); // ┐ hilang
                $txt = str_replace(chr(218), ' ', $txt); // ┌ hilang
                $txt = str_replace(chr(179), ' ', $txt); // │ hilang
                // GARIS VERTICAL END
                if(config('isPrinting')){
                    $txt = str_replace("-", chr(196), $txt); // - jadi ─
                }
                return $txt;
            }
        ], function($p)use($SC, $data, $maxChars){
            $p->setCPI($p::CPI_3);
            $txt = str_pad("No. ", $maxChars-strlen($data['_no']), ' ', STR_PAD_LEFT);
            $p->text(substr($txt, 0, $maxChars-strlen($data['_no'])));
                $p->setUnderline();
                $p->text($data['_no']);
            $p->setUnderline(false);
            $p->bold(true);
            $p->text("\n");
            $txt = str_pad("Tanda Pemberitahuan Pembayaran", $maxChars, ' ', STR_PAD_BOTH);
            // $rightSide = "";
            $p->text($txt);
            $p->bold(false);
            $p->text("\n\n");
            $p->text(" Dari ");
                $p->setUnderline();
                $p->text($data['_vendor']);
                $p->setUnderline(false);
            $p->text("\n");
                $prefix = " Dengan total jumlah uang ";
                $p->text($prefix);
                $p->setUnderline();
                $terbilang = ucwords((string)((new \NumberFormatter("id-ID", \NumberFormatter::SPELLOUT))->format(round($data['_total'])))).' Rupiah';
                if( strlen($terbilang)+strlen($prefix)>$maxChars ){
                    $terbilangArr = explode( PHP_EOL, wordwrap( $terbilang, $maxChars-strlen($prefix), PHP_EOL ) );
                    $p->text($terbilangArr[0]);
                    $p->setUnderline(false);
                    $p->text("\n");
                    $prefix = str_pad( "", strlen($prefix),' ' );
                    $p->text($prefix);
                    $p->setUnderline();
                    $p->text($terbilangArr[1]);
                    $p->setUnderline(false);
                    
                }else{
                    $p->text($terbilang);
                }
                $p->setUnderline(false);
                $p->text("\n");
            $p->setLineSpacing();
        });
        
    }
}
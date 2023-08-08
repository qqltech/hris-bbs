<?php

namespace App\Helpers;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
/**
 * Digunakan untuk report, export ke excel, pdf, dan html versi blade dengan traditional-css
 */
class Exporter {

    public $html='';
    public $config = [
        // 'title' => 'data-exported',
        // 'no_page' => false,
        // 'orientation' => 'P',
        // 'size' => 'A4',
    ];

    function __construct( string $name, array $data = null, array $config = null  )
    {
        $this->config = $config;
        $this->html = view( "projects.$name", compact( 'data' ) );
    }

    public function toHtml(){
        return $this->html;        
    }

    public function toPdf(){
        $pdf = new PDF();
        $noPaginate = @$this->config['no_page'] ?? false;
        $pdf->setHeaderData( [
                'header_callback'=>function($hd){
            },
            'footer_callback'=>  $noPaginate?null:function($ft){
                $ft->SetFont('helvetica', 'I', 8);
                $ft->SetRightMargin(-7);
                $ft->Cell($w=0, $h=6, $txt='Halaman ' .$ft->getAliasNumPage().'/'.$ft->getAliasNbPages(), $border=0, $ln=false, $align='R', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M');
                
                // $ft->writeHTML( '<img width="120" style="text-align:center" height="50" src="https://image.shutterstock.com/image-photo/example-word-written-on-wooden-260nw-1765482248.jpg">' );
            }
        ]);

        $pdf->SetMargins( $left=8, $top=3, $right=6, $keepmargins=false );
        $pdf->PageNo();
        $pdf->setTitle( (@$this->config['title']??'Data-exported') );
        $orientation = @$this->config['orientation'] ?? 'L';
        $size = @$this->config['size'] ?? 'A4';
        $pdf->AddPage( $orientation, $size/*$size=[217, 180]*/);
        $pdf->writeHTML($this->html, true, false,true,false,'');
        $pdf->Output( (@$this->config['title']??'Data-exported').'.pdf', 'I' );
        exit();
    }

    public function toExcel(){
        $reader = new Html();
        $spreadsheet = $reader->loadFromString( $this->html );
        $highestColumn = $spreadsheet->getActiveSheet()->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
        $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();

        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->getStyle("A:$highestColumn")->getAlignment()->setVertical('center');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.(@$this->config['title']??'Data-exported').'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        return $writer->save('php://output');
    }
}
<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helpers\CustomPDF;

class NonApiController extends Controller
{
    public function resources( Request $request, $name ){
        $view = view("projects.web_$name", compact('request'));
        if( !@$request->export ) return $view;
        return $this->export( html: $view->render(), name: $name );
    }

    function export( string $html, string $name ){
        try{
            $req = @app()->request;

            $title = config('export_title') ?? @$req->title ?? "Data-$name";

            if( in_array( strtolower( $req->export ),['excel','csv','xls','xlsx'] ) ){
                $reader = new Html();
                $spreadsheet = $reader->loadFromString( $html );
                $highestColumn = $spreadsheet->getActiveSheet()->getHighestColumn(); // e.g 'F'
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();

                $worksheet = $spreadsheet->getActiveSheet();
                $worksheet->getStyle("A:$highestColumn")->getAlignment()->setVertical('center');

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
                header('Cache-Control: max-age=0');
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                return $writer->save('php://output');
            }
        }catch(\Exception $e){
            if( \Str::contains( strtolowr( @$e->getMessage()??''),' maaf') ){
                return explode('(View',$e->getMessage())[0];
            }
        }

        $pdf = new CustomPDF();
        $noPaginate = config('export_no_page') ?? @$req->has('no_page') ?? false;
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
        $pdf->setTitle( $title );
        $orientation = config('export_orientation') ?? @$req->orientation ?? 'L';
        $size = config('export_size') ?? @$req->size ?? 'A4';
        $pdf->AddPage( $orientation, $size/*$size=[217, 180]*/);
        $pdf->writeHTML($html, true, false,true,false,'');
        $pdf->Output( $title.'.pdf', 'I' );
        exit();
    }
}
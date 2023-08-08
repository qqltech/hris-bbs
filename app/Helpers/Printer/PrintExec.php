<?php

namespace App\Helpers\Printer;
use Illuminate\Support\Carbon;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class PrintExec
{
    public $debug = true;
    function __construct( $isDebug = true ){
        $this->debug = $isDebug;
    }

    public function getTemplatesNonEps()
    {
        $files = glob(resource_path("views/printers/*"));
        return array_map(function($dt){
            $fileNameArr = explode('/', $dt);
            return str_replace(".blade.php","",end($fileNameArr));
        }, $files);
    }

    /**
     * Fungsi untuk printing dengan smart function pembagi halaman otomatis
     */
    public function print(  $templateObj, $data, $printerName, $maxLines = 32  )
    {

        $dataArr = [];
        $mappedDetailIdx = [];
        $detailsOriginal = [];
        $freeLength = [];

        foreach($data['details'] as $idx=>$dt ){
            $dt ['_idx'] = $idx;
            $detailsOriginal[] = $dt;
        }

        unset($data['details']);
        
        do {
            $pengurangan = 0;
            $detailsProcess = array_filter( $detailsOriginal, function( $dt )use( $mappedDetailIdx ){
                return !in_array( $dt['_idx'], $mappedDetailIdx );
            });
            $dataTest = $data;
            do {
                $dataTest['details'] = array_slice( $detailsProcess, 0,  count($detailsProcess)-$pengurangan);
                $res = $templateObj->print( $dataTest, $printerName, $isDebug=true);
                $pengurangan++;
            } while ( count(explode("\n",  $res )) > $maxLines && $pengurangan<count($detailsProcess) );


            foreach($dataTest['details'] as $dt){
                $mappedDetailIdx[] = $dt['_idx'];
            }

            $sisaLength = $maxLines-count(explode("\n",  $res ));
            $freeLength[] = $sisaLength;
            
            for( $i = 0; $i < $sisaLength; $i++ ){
                $dataTest[ 'details' ][] = [];
            }

            $dataArr[] = $dataTest;

        } while ( count($mappedDetailIdx)!=count( $detailsOriginal ) );
    
        //  PRINTING KE MESIN
        $result = "";
        foreach($dataArr as $idx => $data){
            $data['page_idx'] = $idx+1;
            $data['page_total'] = count($dataArr);
            $data['page_freespace'] = $freeLength[$idx];

            $result .= "\n".str_pad(" Page {$data['page_idx']} ", 120, "=", STR_PAD_BOTH)."\n\n";
            $result .= $templateObj->print( $data, $printerName, $this->debug);
        }

        return "<pre>$result</pre>";
    }
    
    /**
     * Fungsi untuk printing NON Dot Matrix dengan smart function pembagi halaman otomatis
     */
    public function printNonDotMatrix( $data , $printerName = null, $config = [ 'size'=>'A4', 'break'=>false, 'orientation' => 'P' ])
    {
        $pdf = new PDF();
        $pdf->setHeaderData( [
            'header_callback'=>function($hd){
            },
            'footer_callback' => @$config['no_page'] ? null : function ($ft) {
                $ft->SetFont('helvetica', 'I', 8);
                $ft->SetRightMargin(-7);
                $ft->Cell($w=0, $h=6, $txt='Halaman ' .$ft->getAliasNumPage().'/'.$ft->getAliasNbPages(), $border=0, $ln=false, $align='R', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M');
                
                // $ft->writeHTML( '<img width="120" style="text-align:center" height="50" src="https://image.shutterstock.com/image-photo/example-word-written-on-wooden-260nw-1765482248.jpg">' );
            }
        ]);

        $pdf->SetMargins($left = (@$config['margin_left'] ?? 5), $top = (@$config['margin_top'] ?? 5), $right = (@$config['margin_right'] ?? 5), $keepmargins = false);
        // $pdf->PageNo();
        $pdf->setTitle( 'Data' );
        $pdf->SetAutoPageBreak( true );
        
        $pdf->SetFont('FreeSans');
        $htmlArr = [];
        foreach($data as $dt){
          $value = $dt['data'];
          $htmlArr[] = view("printers.{$dt['template']}", compact('value'))->render() . "<div style='font-size:20px;'></div>";
          // trigger_error(gettype(config('printer_end_details')));
          while( @config('printer_continue_details') ){
            $htmlArr[]=view("printers.{$dt['template']}", compact('value'))->render() . "<div style='font-size:20px;'></div>";
          }  
        }
        $groupings = @$config['break'] ? $htmlArr : [];
        $placedArr = [];
    
    
        while (!@$config['break'] && count($placedArr) < count($htmlArr)) {
          $pdf->AddPage(@$config['orientation'] ?? 'P', @$config['size'] ?? 'A4');
          $grouped = [];
          foreach ($htmlArr as $idx => $currentHtml) {
            if (in_array($idx, $placedArr)) continue;
    
            $heightBefore = $pdf->GetY();
            $pdf->writeHTML($currentHtml, true, false, true, false, '');
            $heightAfter = $pdf->GetY();
            if ($heightAfter < $heightBefore) {
              $lastPage = $pdf->getPage();
              $pdf->deletePage($lastPage); // rollback
              break;
            }
            $grouped[] = $currentHtml;
            $placedArr[] = $idx;
          }
    
          $groupings[] = implode($grouped);
          if (($lastPage = $pdf->getPage())) {
            $pdf->deletePage($lastPage); // rollback
          }
        }

        foreach($groupings as $idx => $pg){
            $pdf->AddPage( @$config['orientation']??'P', @$config['size']??'A4' );
            $pdf->writeHTML( $pg, true, false, true, false, '');
        }

        $filePath = base_path("public/uploads/abc.pdf");
        if( @$config['no_stream'] ){
            $filePath = base_path("public/uploads/" . uniqid() . ".pdf");
            $pdf->Output($filePath, 'F');
            return response()->download($filePath)->deleteFileAfterSend(true);
        }
          
        if ($this->debug) {
            $pdf->Output($filePath, 'I');
            die();
        }
        $pdf->Output($filePath, 'F');
        
        $processes = ["lpr", "-P", $printerName, "-o", "media=".$config['size'], "-o", "fit-to-page", $filePath];
        $processPrint = new Process($processes);    
        $processPrint->run();

        if (!$processPrint->isSuccessful()) {
            throw new ProcessFailedException($processPrint);
        }

        File::delete($filePath);
        return 'printed';
    }

    function getDevices()
    {
        $process = new Process(['lpstat', '-v']);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        // echo $process->getOutput();
        $responseArray = explode("device for ",$process->getOutput());

        $response = array_values( array_filter($responseArray, fn($dt)=> $dt!=="") );
        $fixedResponse = array_map( function( $dt )
        {
            $data = explode(": ", $dt);
            return [
                "name" => $data[0],
                "connector" => $data[1]
            ];
        }, $response );

        return $fixedResponse;
    }

}
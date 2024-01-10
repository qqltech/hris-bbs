<?php
namespace App\Helpers\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

//  Digunakan untuk direct printer ke EPSON Printer
Class Epson
{
    public function generate( $textData, $config=[ 'w'=>125 ], $callbackHeader=null, $callbackFooter=null ){
        $SC = "\x1b";
        $file =  storage_path('app/receipt').uniqid();
        $connector = new FilePrintConnector($file);
        $printer = new Printer( $connector );
        $printer->lineSpacing(0);
        if(config('pageSizeInch')){
            $printer->setPageInch(config('pageSizeInch'));
        }
        $printer->setCPI(Printer::CPI_3); // text kecil
        // $printer->setPageLines(32);
        // $printer->feedForm();
        // $printer->setPageLines(50);
        // $printer->feed();
        $textArr = explode(PHP_EOL, $textData);
        // $printer->lineSpacing(1);
        if($callbackHeader){
            $callbackHeader( $printer );
        }

        // $printer->feed(); // ganti baris
        // $printer->bold(false);
        // $printer->setJustification(Printer::JUSTIFY_RIGHT); // rata kiri
        // $printer->setQuality(1);
        // $printer->feedForm();
        // $printer->setFontCourier();
        // $printer->setRightMargin(2);
        // $printer->setCPI(Printer::CPI_3); // text normal
        // $printer->setCondensed();
        foreach($textArr as $txtIdx=>$txt){
            // $printer->lineSpacing(0);
            if(!$txt) {
                // $printer->feed(1); // ganti baris
                continue;
            }
            if(Str::contains($txt, "config.")){
                $funcArr = explode(".", str_replace(" ", "", $txt));
                $func = config(end($funcArr));
                $func($printer);
                continue;
            }

            $isHeading = Str::contains($txt, ":::header:::");
            if($isHeading){
                $txt = str_replace(":::header:::", "", $txt);
            }
            
            $isHeadingLineTop = Str::contains((@$textArr[$txtIdx+1]??''), ":::header:::");
            
            $isHeadingLineBot = Str::contains((@$textArr[$txtIdx-1]??''), ":::header:::");

            if($isHeadingLineTop){
                $txt = str_replace("+", config('isPrinting')?chr(194):"+", $txt);
                $txt = str_replace(chr(195), chr(218), $txt);
                $txt = str_replace(chr(180), chr(191), $txt);
            }elseif($txtIdx < (count($textArr)-1)  ){
                $txt = str_replace("+", config('isPrinting')?chr(197):"+", $txt);
                // $txt = str_replace(chr(179), chr(197), $txt);
                
            }elseif($txtIdx == (count($textArr)-1) ){
                $txt = str_replace("+", config('isPrinting')?chr(193):"+", $txt);
                $txt = str_replace(chr(195), chr(192), $txt);
                $txt = str_replace(chr(180), chr(217), $txt);
            }
            // https://theasciicode.com.ar/ascii-control-characters/line-feed-ascii-code-10.html
            if($isHeadingLineBot){
                // $printer->bold();
                $printer->setLineSpacing();
                // $txt = str_replace(chr(179), ' ', $txt);
                $txt = str_replace(chr(195), chr(192), $txt);
                $txt = str_replace(chr(197),  chr(193), $txt);
                $txt = str_replace(chr(180), chr(217), $txt);

            }elseif(!$isHeading){
                $txt = str_replace(chr(179), ' ', $txt);
                $txt = str_replace(chr(195), ' ', $txt);
                $txt = str_replace(chr(180),  ' ', $txt);
                $txt = str_replace(chr(197),  chr(196), $txt);

            }

            if($isHeading){
                $printer->bold();
                //  $textData .= $SC."3";
                // $printer->setQuality(1);
            }
            
            $txt = str_pad($txt, $config['w'], ' ', STR_PAD_BOTH);
            if(@$config['formatter']){
                $txt = $config['formatter']($printer, $txt, $isHeading);
            }

            foreach(str_split($txt) as $col=>$colData ){
                $printer -> getConnector() -> write($colData);
                // $printer->text($txt."\n");
            }
            $printer->text("\n");

            if($isHeading && config('isPrinting')){
                $printer->setEmphasis(false);
            }
            if($isHeadingLineTop||$isHeadingLineBot||$isHeading){
                $printer->bold(false);
                //  $textData .= $SC."3";
                $printer->setLineSpacing();
            }
        }

         // ganti baris

        if($callbackFooter){
            $callbackFooter( $printer );
        }
        // if( $printData = File::get($file) ){
            // $barisCount =  count(explode("\n", $printData)) ;
            // ff($barisCount);
            // for($barisCount=0; $barisCount<25; $barisCount++){
                // $printer->feed();
            // };
        // }
        $printer->feedForm();
        $printer->close();
        // return 'ok';
        if(config('isPrintingDownload')&&config('isPrinting')){
            $res = config('printResult')??[];
            $res[] = $file;
            config( ['printResult'=>$res] );
            return "$file,";
        }elseif(config('isPrinting')){
            $processes = ["lpr", "-P", $config['printer'], "-o", "media=Custom.8.5x6in", "-o", "raw", $file];
            $processPrint = new Process($processes);    
            $processPrint->run();

            if (!$processPrint->isSuccessful()) {
                throw new ProcessFailedException($processPrint);
            }
            File::delete($file);
            return 'printed';
        }
        $content = File::get($file);
        File::delete($file);
        return str_replace([
            $SC.'@',$SC.'2',$SC.'E',$SC.'F'
        ],
        ['','','',''],$content);
    }
}
<?php
namespace App\Helpers\Printer;
use Mike42\Escpos\Printer as ESCP;

//  Digunakan untuk persiapan direct printer ke EPSON Printer
class Printer extends ESCP
{
    const CPI_1 = "P";
    const CPI_2 = "M";
    const CPI_3 = "g";
    const CPI_4 = "p";

    public function getConnector(){
        return $this->connector;
    }

    public function setQuality( $val = 1 )
    {
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC ."x$val");
    }

    public function italic( bool $val = true )
    {
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC . ($val?"4":"5"));
    }
    public function lineSpacing( $val )
    {
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC . $val);
    }
    
    public function bold( bool $val = true )
    {
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC .($val?"E":"F"));
    }
    
    public function setCPI( $cpi = null )
    {
        if(!config('isPrinting')) return;
        $cpi = $cpi??self::CPI_1;
        $this -> connector -> write(self::ESC .$cpi);
    }
    
    public function setCondensed( $val = true )
    {
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC .($val?'SI':'SO'));
    }
    
    public function setSuperscript( $val = true )
    {
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC .($val? 'S0':'T'));
    }
    
    public function setSubscript( $val = true )
    {
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC .($val?'S1':'T'));
    }

    public function setPageInch($val){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."C\x00".chr($val)); // ESC C NUL n
    }

    public function setPageLines($val){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."C".chr($val)); // ESC C NUL n
    }

    public function set240DPI(){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC." Z"); 
    }
    public function setZeroLineSpacing(){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."0"); 
    }
    
    public function setRightMargin($val){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."Q $val"); 
    }
    
    public function setFontRoman(){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."k0"); 
    }
    
    public function setFontSans(){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."k1"); 
    }
    
    public function setFontCourier(){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."k1"); 
    }
    
    public function setAlignment( $num ){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."a".chr($num)); 
    }
    
    public function setBarcode( $num ){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."(B".chr($num)); 
    }
    
    public function setLQMode(){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."x1"); 
    }

    public function setDraftMode(){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."x0"); 
    }
    
    public function setUnderline( $isON=true ){
        if(!config('isPrinting')) return;
        $this -> connector -> write(self::ESC."-".chr($isON?1:0)); 
    }
}
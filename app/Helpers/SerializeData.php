<?php

namespace App\Helpers;
use Illuminate\Support\Carbon;

class SerializeData
{

    public $temp_array;
    private $isApi = false;
    private $settings;

    private function apiCheck( $request ){
        if( $request->header('ajax')!=null ){
            return false;
        }
        if( in_array($request->header('accept'), ['application/json; charset=utf-8','application/json','Application/json' ]) ||
            in_array($request->header('content-type'), ['application/json','application/json;charset=utf-8' ])
        ){
            return true;
        }else{
            return false;
        }        
    }

    public static function get( $array, $tambahan=null, $convert=null)
    {
        $baru = new Detail();
        return $baru->start($array,$tambahan,$convert);
    }

    private function start($array, $tambahan=null, $convert=null){
        $array_child=[];
        
        foreach($array as $key => $value){
            if(is_array($value)){
                $array_child[][$key]=$value;
            }
        }
        
        $hitung=0;
        $akhir=[];
        for ($x = 0; $x <= count($array_child)-1; $x++) {
            foreach($array_child[$x] as $key => $value){
                if($hitung == 0){
                    $hitung= count($value);
                }
                for ($i = 0; $i <= $hitung-1; $i++) {
                    if(!isset($akhir[$i])){
                        $akhir[$i]=[];
                    }

                    $akhir[$i] += [$key=>$value[$i]];
                    if($tambahan!=null){
                        $akhir[$i] += $tambahan;
                    }
                }
            }
        }

        if($convert!=null){
            foreach($convert as $key_convert => $tipe_convert){
                foreach($akhir as $array_data_key => $array_data_value){
                    foreach($array_data_value as $data_key => $data_value){
                        if($key_convert == $data_key){

                            $akhir[$array_data_key][$data_key]=$this->converter($data_value,$tipe_convert);

                        }
                    }
                }
            }
        }
        // $this->temp_array = $akhir;
        return $akhir;
    }

    public static function getDetail( $array, $numberColumns=null, $dateFormat=null, $req=null, $childName=null)
    {
        $baru = new Detail();
        $baru->isApi = $baru->apiCheck($req);
        $baru->settings ['numbers'] = ($numberColumns !=null ) ? $numberColumns : [];
        $baru->settings ['date'] = ($dateFormat !=null ) ? $dateFormat : "Y/m/d";

        return $baru->startNew($array, $childName);
    }

    private function startNew($array, $childName=null)
    {
        $array_child=[];
        
        foreach($array as $key => $value){
            if($childName!=null){
                if(is_array($value)){
                    if($this->isApi && $key==$childName){
                        $array_child[] = $array[$key];
                    }elseif(explode("::",$key)[0]==$childName){
                        $array_child[][$key]=$value;
                    }
                }
            }else{
                if(is_array($value)){
                    if($this->isApi){
                        $array_child[] = $array[$key];
                    }else{
                        $array_child[][$key]=$value;
                    }
                }
            }
        }
        // return $array_child[1];
        $hitung=0;
        $akhir=[];
        for ($x = 0; $x <= count($array_child)-1; $x++) {
            if($this->isApi){
                $akhir = $array_child[$x];
            }else{
                foreach($array_child[$x] as $key => $value){
                    if($hitung == 0){
                        $hitung= count($value);
                    }
                    for ($i = 0; $i <= $hitung-1; $i++) {
                        if(!isset($akhir[$i])){
                            $akhir[$i]=[];
                        }
    
                        if( in_array($key, $this->settings ['numbers'] ) ){
                            $value[$i] = $this->converter($value[$i], "integer");
                        }
    
                        if( count( explode("/",$value[$i]) )==3 ){
                            if( is_numeric(explode("/",$value[$i])[0]) && is_numeric(explode("/",$value[$i])[1]) && is_numeric(explode("/",$value[$i])[2])  ){
                                $value[$i] = $this->converter( $value[$i],'date');
                            }
                        }elseif( count( explode("-",$value[$i]) )==3 ){
                            if( is_numeric(explode("-",$value[$i])[0]) && is_numeric(explode("-",$value[$i])[1]) && is_numeric(explode("-",$value[$i])[2])  ){
                                $value[$i] = $this->converter( $value[$i],'date');
                            }
                        }
                        $akhir[$i] += [ ($childName==null?$key:str_replace("$childName::","",$key))=>$value[$i]];
                    }
                }
            }     
        }
        return $akhir;
    }
    private function converter($value,$jenis)
    {
        if( $jenis == 'integer'){
            return str_replace(array('.'),'' ,$value);
        }elseif( $jenis == 'date'){
            return Carbon::createFromFormat( $this->settings ['date'] ,  $value)->toDateTimeString();
        }
    }

    public static function getParent( $array, $numberColumns=null, $dateFormat=null)
    {
        $baru = new Detail();
        
        $baru->settings ['numbers'] = ($numberColumns !=null ) ? $numberColumns : [];
        $baru->settings ['date'] = ($dateFormat !=null ) ? $dateFormat : "Y/m/d";
        return $baru->startParent($array);
    }

    private function startParent($array)
    {
        $array_child=[];
        
        foreach($array as $key => $value){
            if(!is_array($value)){

                if( in_array($key, $this->settings ['numbers'] ) ){
                    $value = $this->converter($value, "integer");
                }

                if( count( explode("/",$value) ) == 3 ){
                    if( is_numeric(explode("/",$value)[0]) && is_numeric(explode("/",$value)[1]) && is_numeric(explode("/",$value)[2])  ){
                        $value = $this->converter( $value,'date');
                    }
                }elseif( count( explode("-",$value) ) == 3 ){
                    if( is_numeric(explode("-",$value)[0]) && is_numeric(explode("-",$value)[1]) && is_numeric(explode("-",$value)[2])  ){
                        $value = $this->converter( $value,'date');
                    }
                }

                $array_child[$key]=$value;
            }
        }
        
        return $array_child;
    }

}
<?php

namespace App\Models\CustomModels;
use Illuminate\Support\Facades\Validator;
use DB;

class presensi_absensi extends \App\Models\BasicModels\presensi_absensi
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');

    }

    public $fileColumns = [];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function onRetrieved($model)
    {
        $model->checkout_foto = url('').'/'.$model->checkout_foto;
        $model->checkin_foto = url('').'/'.$model->checkin_foto;
    }

    public function custom_get_by_daily($req)
    {
        $req->month = $req->month.'-01';
        $data = \DB::select("
            SELECT json_agg(json_build_object(
                'all_days_of_month', all_days_of_month,
                'date_to_idn', date_to_idn,
                'day_name_idn', day_name_idn,
                'type', type,
                'presentase', presentase,
                'attend', attend,
                'cuti', cuti,
                'alpha', alpha,
                'total_kary', total_kary
            )) AS monthly_report
            FROM generate_monthly_report(?,?,?)",[$req->month,$req->divisi_id,$req->dept_id]);
        
        if(count($data)){   
            return $this->helper->customResponse('OK',200,json_decode($data[0]->monthly_report));
        }else{
            return $this->helper->customResponse('OK',200,[]);
        }

    }

    public function custom_get_by_date($req)
    {
        $data = \DB::select("
            SELECT json_agg(json_build_object(
                'm_kary_id', m_kary_id,
                'default_user_id', default_user_id,
                'kode', kode,
                'nama_lengkap', nama_lengkap,
                'dept', dept,
                'absensi', absensi
            )) AS att_report
            FROM get_employee_attendance_report(?,?,?)",[$req->date,$req->divisi_id,$req->dept_id]);

        
        if(count($data)){   
            return $this->helper->customResponse('OK',200,json_decode($data[0]->att_report));
        }else{
            return $this->helper->customResponse('OK',200,[]);
        }
    }

    public function custom_checkin($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) 
            return $this->helper->responseValidate($validator);

        DB::beginTransaction();
        try {
            $distance = $this->distance($req->lat, $req->long);
            if ($distance) {
                $data["on_scope"] = true;
                $data["region"] = $distance->nama;
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = null;
            } else {
                $data["on_scope"] = false;
                $data["region"] = "Out Scope";
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = $req->catatan_in ?? null;
            }

            $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->exists();
            if ($check_exists_absen == true) 
                return $this->helper->customResponse("Anda sudah checkin hari ini", 422);

            if ($req->hasFile("foto")) {
                $file = $req->file("foto");
                $fileName =
                    auth()->user()->username .
                    ":::" .
                    md5(time()) .
                    "." .
                    $file->getClientOriginalExtension();
                $file->move(public_path("uploads/presensi"), $fileName);
            } else {
                trigger_error("IMAGE NOT VALID");
            }

            $this->create([
                "tanggal" => date("Y-m-d"),
                "checkin_time" => date("H:i:s"),
                "checkin_foto" => "uploads/presensi/$fileName",
                "checkin_lat" => $data["checkin_lat"],
                "checkin_long" => $data["checkin_long"],
                "checkin_address" => $data["checkin_address"],
                "checkin_region" => $data["region"],
                "checkin_on_scope" => $data["on_scope"],
                "catatan_in" => $data["catatan_in"],
                "default_user_id" => auth()->user()->id,
                "creator_id" => auth()->user()->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkin gagal, coba kembali nanti - ".$e->getMessage(), 400);
        }
        return $this->helper->customResponse("Checkin berhasil", 200, $data);
    }

    public function custom_checkout($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) return $this->helper->responseValidate($validator);

        DB::beginTransaction();
        try {
            $distance = $this->distance($req->lat, $req->long);
            if ($distance) {
                $data["on_scope"] = true;
                $data["region"] = $distance->nama;
                $data["checkout_lat"] = $req->lat;
                $data["checkout_long"] = $req->long;
                $data["checkout_address"] = $req->address;
                $data["catatan_out"] = null;
            } else {
                $data["on_scope"] = false;
                $data["region"] = "Out Scope";
                $data["checkout_lat"] = $req->lat;
                $data["checkout_long"] = $req->long;
                $data["checkout_address"] = $req->address;
                $data["catatan_out"] = $req->catatan_out ?? null;
            }

            $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "ATTEND")
                ->exists();
            if ($check_exists_absen) 
                return $this->helper->customResponse("Anda sudah checkout hari ini", 422);
            
            $check_not_exists_checkin = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "WORKING")->exists();
             if ($check_exists_absen) 
                return $this->helper->customResponse("Anda belum checkin hari ini", 422);

            if ($req->hasFile("foto")) {
                $file = $req->file("foto");
                $fileName =
                    auth()->user()->username .
                    ":::" .
                    md5(time()) .
                    "." .
                    $file->getClientOriginalExtension();
                $file->move(public_path("uploads/presensi"), $fileName);
            } else {
                trigger_error("IMAGE NOT VALID");
            }

            $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "WORKING")
                ->update([
                    "checkout_time" => date("H:i:s"),
                    "checkout_foto" => "uploads/presensi/$fileName",
                    "checkout_lat" => $data["checkout_lat"],
                    "checkout_long" => $data["checkout_long"],
                    "checkout_address" => $data["checkout_address"],
                    "checkout_region" => $data["region"],
                    "checkout_on_scope" => $data["on_scope"],
                    "catatan_out" => $data["catatan_out"],
                    "status" => "ATTEND",
                ]);
            DB::commit();
       } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkout gagal, coba kembali nanti - ", 422);
        }
        return $this->helper->customResponse("Checkout berhasil", 200, $data);
    }

    private function distance($lat, $long)
    {
        $distance = DB::select("select distance_location(?,?)", [$lat, $long]);
        if (count($distance)) {
            $location = json_decode($distance[0]->distance_location);
            return @$location[0] ?? false;
        } else {
            return false;
        }
    }

    public function custom_distance_check($req)
    {
        $distance = $this->distance($req->lat, $req->long);
        if ($distance) {
            $data["on_scope"] = true;
            $data["region"] = $distance->nama;
            $data["checkout_lat"] = $distance->lat;
            $data["checkout_long"] = $distance->long;
            $data["checkout_address"] = $req->address;
        } else {
            $data["on_scope"] = false;
            $data["region"] = "Out Scope";
            $data["checkout_lat"] = $req->lat;
            $data["checkout_long"] = $req->long;
            $data["checkout_address"] = $req->address;
        }
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_status($model)
    {
        $data = [
            'status' => $this->where('tanggal', date('Y-m-d'))->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND'
        ];
        return $this->helper->customResponse("OK", 200, $data);
    }
    public function scopeFilter($model)
    {
        if(req('date_from') && req('date_to')){
            return $model->whereBetween('tanggal',[req('date_from'),req('date_to')])->where('default_user_id', auth()->user()->id ?? 0);
        }
    }
}

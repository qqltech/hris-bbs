<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class upload_kary extends Model
{   
    use ModelTrait;

    protected $table    = 'upload_kary';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["nik","nama","directline","direktorat","divisi","departemen","principle","area","work_location","job_title_name"];

    public $columns     = ["nik","nama","directline","direktorat","divisi","departemen","principle","area","work_location","job_title_name"];
    public $columnsFull = ["nik:string:512","nama:string:512","directline:string:512","direktorat:string:512","divisi:string:512","departemen:string:512","principle:string:512","area:string:512","work_location:string:512","job_title_name:string:512"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["nik","nama","directline","direktorat","divisi","departemen","principle","area","work_location","job_title_name"];
    public $updateable  = ["nik","nama","directline","direktorat","divisi","departemen","principle","area","work_location","job_title_name"];
    public $searchable  = ["nik","nama","directline","direktorat","divisi","departemen","principle","area","work_location","job_title_name"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}

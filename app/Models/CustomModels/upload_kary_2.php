<?php

namespace App\Models\CustomModels;

class upload_kary_2 extends \App\Models\BasicModels\upload_kary_2
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}
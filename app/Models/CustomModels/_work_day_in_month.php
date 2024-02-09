<?php

namespace App\Models\CustomModels;

class _work_day_in_month extends \App\Models\BasicModels\_work_day_in_month
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}
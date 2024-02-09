<?php

namespace App\Models\CustomModels;

class _cuti_reguler_terpakai extends \App\Models\BasicModels\_cuti_reguler_terpakai
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}
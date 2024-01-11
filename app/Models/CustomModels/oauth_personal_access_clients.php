<?php

namespace App\Models\CustomModels;

class oauth_personal_access_clients extends \App\Models\BasicModels\oauth_personal_access_clients
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}
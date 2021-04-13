<?php

namespace App\Models\Defaults;

use Illuminate\Database\Eloquent\Model;

class FileStorage extends Model
{
    protected $table = 'file_storage';
    protected $guarded = ['id'];
}

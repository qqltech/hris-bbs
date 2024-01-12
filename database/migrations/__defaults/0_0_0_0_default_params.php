<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class defaultparams extends Migration
{
    public function up()
    {
        Schema::create('default_params', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name',100)->unique();
            $table->string('note',255)->nullable();
            $table->longText('prepared_query');
            $table->string('params')->nullable(); //    param1,param2,param3
            $table->string('modul', 50);
            $table->string('editor_name')->nullable();
            $table->boolean('is_active')->default(1)->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }
    public function down()
    {
        Schema::dropIfExists('default_params');
    }
}






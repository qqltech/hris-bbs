<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultFileStorage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('default_file_storage');
        Schema::create('default_file_storage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('table',20)->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->enum('type',['file','image'])->nullable();
            $table->text('filename')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_storage');
    }
}

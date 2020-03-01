<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->nullable();
            $table->string('table',100)->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->enum('action',[ 'update','delete','create' ])->nullable();
            $table->string('status')->nullable();
            $table->text('value')->nullable();
            $table->text('error')->nullable();
            $table->text('tambahan')->nullable();
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
        Schema::dropIfExists('default_activities');
    }
}

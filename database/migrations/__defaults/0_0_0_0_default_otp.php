<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultOtp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_otp', function (Blueprint $table) {
            $table->id()->from(1);
            
            $table->string('type',100)->default('email');
            $table->string('to',100);
            $table->string('ip_address',100);
            $table->string('code',100);
            $table->string('jenis')->nullable()->default('password');
            $table->string('redaksi');
            $table->string('client_path')->nullable();
            $table->string('note')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->dateTime('verified_at')->nullable();

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
        Schema::dropIfExists('default_otp');
    }
}

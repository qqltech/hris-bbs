<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultUsersSocialite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_users_socialite', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("default_users_id")->comment('{"fk":"default_users.id"}');
            $table->string('provider');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('token')->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('status', 20)->default("ACTIVE");
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
        Schema::dropIfExists('default_users_socialite');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Defaults\User;

class DefaultUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('username',60)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('role',60)->nullable()->default('admin');
            $table->string('project')->nullable();
            $table->string('status',20)->default("ACTIVE");
            $table->rememberToken();
            $table->timestamps();
        });
        $hasher = app()->make('hash');
        User::create(
            [
                'name' => "trial",
                'email' => "trial@trial.trial",
                'username'=>"trial",
                'password' => $hasher->make("trial")
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('default_users');
    }
}

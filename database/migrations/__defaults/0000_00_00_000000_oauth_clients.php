<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OauthClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('oauth_clients');
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index()->nullable()->comment('{"src": "default_users.id"}');
            $table->string('name');
            $table->string('secret', 100);
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });
        DB::table("oauth_clients")->insert([[
            "user_id"      => 1,
            "name"      => "Password Grant Client",
            "secret"    => "ZJpXX9gGYqMhruw5gl5lgC4FywMwuHxe24uIw0Dk",
            "redirect"  => url(),
            "personal_access_client" => false,
            "password_client"   =>   true,
            "revoked"   => false,
            "created_at"=>\Carbon\Carbon::now(),
            "updated_at"=>\Carbon\Carbon::now()
        ],[
            "user_id"      => 1,
            "name"      => "Personal Access Client",
            "secret"    => "TiRlLOaIcy98aO6LgqTyPkNqyl31AL9wf1dcHGuV",
            "redirect"  => url(),
            "personal_access_client" => true,
            "password_client"   =>   false,
            "revoked"   => false,
            "created_at"=>\Carbon\Carbon::now(),
            "updated_at"=>\Carbon\Carbon::now()
        ]]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_clients');
    }
}

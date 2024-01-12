<?php
use Illuminate\Database\Seeder;
use App\Models\Defaults\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $hasher = app()->make('hash');
        // DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        // $this->call('UsersTableSeeder');
        User::truncate();
        // factory(User::class, 5)->create();
        User::create(
            [
                'name' => "trial",
                'email' => "trial@trial.trial",
                'username'=>"trial",
                'password' => $hasher->make("trial")
            ]
        );
        // DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        DB::table("oauth_clients")->insert([
            "id"        => 1,
            "name"      => "Personal Access Client",
            "secret"    => "TiRlLOaIcy98aO6LgqTyPkNqyl31AL9wf1dcHGuV",
            "redirect"  => url(),
            "personal_access_client" => true,
            "password_client"   =>   false,
            "revoked"   => false,
            "created_at"=>Carbon::now(),
            "updated_at"=>Carbon::now()
        ]);
        DB::table("oauth_clients")->insert([
            "id"        => 2,
            "name"      => "Password Grant Client",
            "secret"    => "ZJpXX9gGYqMhruw5gl5lgC4FywMwuHxe24uIw0Dk",
            "redirect"  => url(),
            "personal_access_client" => false,
            "password_client"   =>   true,
            "revoked"   => false,
            "created_at"=>Carbon::now(),
            "updated_at"=>Carbon::now()
        ]);
        DB::table("oauth_personal_access_clients")->insert([
            "id"        => 1,
            "client_id" => 1,
            "created_at"=>Carbon::now(),
            "updated_at"=>Carbon::now()
        ]);

    }
    
}

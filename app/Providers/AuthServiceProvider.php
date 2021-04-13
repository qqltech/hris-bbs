<?php

namespace App\Providers;

use App\Models\Defaults\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;

use Starlight93\Oauth2\Passport;

class AuthServiceProvider extends ServiceProvider
{
    
    public function register()
    {
        //
    }
    public function boot()
    {
        Passport::routes();
        Passport::tokensExpireIn(Carbon::now()->addDays(15));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));

    }
}

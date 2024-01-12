<?php

namespace App\Providers;

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
        // Passport::routes();
        Passport::tokensExpireIn(Carbon::now()->addDays( isMobile() ? 180 : 30 ));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
    }
}

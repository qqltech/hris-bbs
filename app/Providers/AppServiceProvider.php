<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Validator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);
        Validator::extend('date_multi_format', function($attribute, $value, $formats) {
            foreach($formats as $format) {
              $parsed = date_parse_from_format($format, $value);
              if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                return true;
              }
            }
            return false;
        }, "The :attribute format must be:[Y-m-d H:i:s], [Y-m-d] or [d/m/Y].");

        Validator::extend('forbidden', function ($attribute, $value, $parameters) {
            return false;
        }, "The :attribute field is forbidden to send.");

        Validator::extend('no_space_only', function ($attribute, $value, $parameters) {
            if( str_replace( [' ',"\t","\n"], ["","",""], $value ) == '' ){
                return false;
            }
            return true;
        }, "The :attribute field must not be whitespaces only.");
    }
}

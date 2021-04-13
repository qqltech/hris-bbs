<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);
$locale = strtolower(env("LOCALE","EN"));
app('translator')->setLocale($locale);
$app->withFacades();

$app->withEloquent();

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->middleware([
    // App\Http\Middleware\ExampleMiddleware::class
    \Fruitcake\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'throttle' => Starlight93\Oauth2\Middleware\ThrottleRequests::class,
    'cors' => \Fruitcake\Cors\HandleCors::class,
    'laradev' => App\Http\Middleware\Laradev::class,
]);

$app->register(App\Providers\AppServiceProvider::class); //DEFAULT
$app->register(Stevebauman\Location\LocationServiceProvider::class);
$app->register(Fruitcake\Cors\CorsServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class); //DEFAULT
$app->register(Starlight93\Oauth2\PassportServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
    require __DIR__.'/../routes/laradev.php';
    require __DIR__.'/../routes/operation.php';
    require __DIR__.'/../routes/public.php';
    require __DIR__.'/../routes/docs.php';
    require __DIR__.'/../routes/custom.php';
});

$app->singleton('filesystem', function ($app) { 
    return $app->loadComponent('filesystems', 'Illuminate\Filesystem\FilesystemServiceProvider', 'filesystem'); 
});

collect(scandir(__DIR__ . '/../config'))->each(function ($item) use ($app) {
    $app->configure(basename($item, '.php'));
});

$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);
if (!class_exists('Excel')) {
    class_alias('Maatwebsite\Excel\Facades\Excel', 'Excel');
}
if (!class_exists('Carbon')) {
    class_alias('Carbon\Carbon', 'Carbon');
}
if (!class_exists('ExportExcel')) {
    class_alias('App\Models\Additionals\ExportExcel', 'ExportExcel');
}
if (!class_exists('Api')) {
    class_alias('App\Http\Controllers\ApiFixedController', 'Api');
}
if (!class_exists('Mail')) {
    class_alias('Illuminate\Support\Facades\Mail', 'Mail');
}
if (!class_exists('MailTemplate')) {
    class_alias('App\Mails\SendMailable', 'MailTemplate');
}
\Illuminate\Http\Request::macro('hanya', function($array) {
    $diff = array_filter(array_keys($this->all()),function($dt)use($array){
        if( !in_array($dt,$array) && strpos($dt,"_d_") !==true ){
            return $dt;
        }
    });
    foreach($diff as $isi){
        $this->getInputSource()->remove($isi);
    }
    return $this;
});
\Illuminate\Http\Request::macro('reuse', function($array) {
    foreach(array_keys($this->all()) as $isi){
        $this->getInputSource()->remove($isi);
    }
    $this->merge($array);
    return $this;
});

\Illuminate\Http\Request::macro('getMetaData', function() {
    foreach(array_keys($this->all()) as $isi){
        $this->getInputSource()->remove($isi);
    }
    return $this;
});

return $app;

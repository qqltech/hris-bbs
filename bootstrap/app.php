<?php

require_once __DIR__.'/../vendor/autoload.php';

$envFile = ".env"; // default .env file

$port = @$_SERVER['SERVER_PORT'];
if( $port && ( $port!='80' || $port!='443' ) && file_exists( dirname(__DIR__) . '/' . ".env.$port" ) ){
    $envFile = ".env.$port"; // jika port selain 80 & 443 maka akan mengutamakan .env.{port} jika ada
}else{
    $subDomain = strtolower(explode('.', @$_SERVER['HTTP_ENV'] ?? @$_SERVER['HTTP_HOST'] ?? '.')[0]);
    if( $subDomain && file_exists( dirname(__DIR__) . '/' . ".env.$subDomain" ) ){
        $envFile = ".env.$subDomain"; // jika membawa header HTTP_ENV atau tidak, maka akan mencoba pakai .env.{subdomain} jika ada
    }
}

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__), $envFile // dynamic .env file jika diperlukan
))->bootstrap();

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);
$locale = strtolower(env("LOCALE","EN"));
date_default_timezone_set(env("APP_TIMEZONE","Asia/Jakarta"));
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
    'project' => App\Http\Middleware\ProjectMiddleware::class,
]);

//  for swoole service provider
if( env('SWOOLE', false) ){ // belum update
    // $app->register(SwooleTW\Http\LumenServiceProvider::class);
}
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(App\Providers\AppServiceProvider::class); //DEFAULT
$app->register(Fruitcake\Cors\CorsServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class); //DEFAULT
$app->register(Starlight93\Oauth2\PassportServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
// $app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
if (env('AUTOCREATE_MIGRATION') && class_exists(\MigrationsGenerator\MigrationsGeneratorServiceProvider::class)) {
    $app->register(\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
}
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
    require __DIR__.'/../routes/laradev.php';
    require __DIR__.'/../routes/operation.php';
    require __DIR__.'/../routes/public.php';
    require __DIR__.'/../routes/docs.php';
    require __DIR__.'/../routes/operation-lite.php';
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
if (!class_exists('Str')) {
    class_alias('Illuminate\Support\Str', 'Str');
}
if (!class_exists('Arr')) {
    class_alias('Illuminate\Support\Arr', 'Arr');
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
if (!class_exists('File')) {
    class_alias('Illuminate\Support\Facades\File', 'File');
}
if (!class_exists('Cache')) {
    class_alias('Illuminate\Support\Facades\Cache', 'Cache');
}
if( !class_exists('HasMany') ) {
    class_alias('Illuminate\Database\Eloquent\Relations\HasMany', 'HasMany');
}
if( !class_exists('BelongsTo') ) {
    class_alias('Illuminate\Database\Eloquent\Relations\BelongsTo', 'BelongsTo');
}
\Illuminate\Http\Request::macro('getMetaData', function() {
    foreach(array_keys($this->all()) as $isi){
        $this->getInputSource()->remove($isi);
    }
    return $this;
});

if( $bootstrap = env("BOOTSTRAP_RESOLVER") ){
    $funcArr = explode(".", $bootstrap);
    $class = getCore($funcArr[0]) ?? getCustom($funcArr[0]);
    $func = $funcArr[1];
    return $class->$func( $app );
}
return $app;

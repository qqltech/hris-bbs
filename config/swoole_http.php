<?php
if( !env('SWOOLE', false) ){ // belum update
    return [];
}
return [
    'server' => [
        'host' => env('SWOOLE_HTTP_HOST', '127.0.0.1'),
        'port' => env('SWOOLE_HTTP_PORT', '1214'),
        'public_path' => base_path('public'),
        // Options here will pass to Swoole server's configuration directly
        'options' => [
            // You can run your application in deamon
            'daemonize' => env('SWOOLE_HTTP_DAEMONIZE', false),
            // Normally this value should be 1~4 times lager according to your cpu cores 
            'reactor_num' => env('SWOOLE_HTTP_REACTOR_NUM', swoole_cpu_num() * 2),
            'worker_num' => env('SWOOLE_HTTP_WORKER_NUM', swoole_cpu_num() * 2),
            'task_worker_num' => env('SWOOLE_HTTP_TASK_WORKER_NUM', swoole_cpu_num() * 2),
            // This value should be larger than `post_max_size` and `upload_max_filesize` in `php.ini`.
            // This equals to 10 MB
            'package_max_length' => 10 * 1024 * 1024,
            'buffer_output_size' => 10 * 1024 * 1024,
            // Max buffer size for socket connections
            'socket_buffer_size' => 128 * 1024 * 1024,
            // Worker will restart after processing this number of request
            'max_request' => env('SWOOLE_MAX_REQUEST', 1000),
            // Enable coroutine send
            'send_yield' => true,
            // You must add --enable-openssl while compiling Swoole
            'ssl_cert_file' => null,
            'ssl_key_file' => null,
        ],
    ],

    "instances"=>[
        'auth'
    ],
    "providers"=>[
        \App\Providers\AppServiceProvider::class, //DEFAULT
        \App\Providers\AuthServiceProvider::class,
        \Stevebauman\Location\LocationServiceProvider::class,
        \Fruitcake\Cors\CorsServiceProvider::class,
        \Starlight93\Oauth2\PassportServiceProvider::class,
        \App\Providers\EventServiceProvider::class,
        \Illuminate\Mail\MailServiceProvider::class,
        \Illuminate\Notifications\NotificationServiceProvider::class,
        \Maatwebsite\Excel\ExcelServiceProvider::class
    ],

    // You can customize your swoole tables here. 
    // See https://wiki.swoole.com/wiki/page/p-table.html for more detailed information.
    'tables' => [
        // 'table_name' => [
        //     'size' => 1024,
        //     'columns' => [
        //         ['name' => 'column_name', 'type' => 1, 'size' => 1024],
        //     ]
        // ],
    ]
];
<?php

return [
    'defaults' => [
        'guard' => 'api',
    ],
    'guards' => [
        'api' => ['driver' => 'passport', 'provider' => 'users'],
    ],
    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => \App\Models\Defaults\User::class]
    ],
    'passwords' => [
    ],

];

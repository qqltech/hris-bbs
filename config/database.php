<?php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'timezone' => env('DB_TIMEZONE', '+07:00'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'timezone' => env('DB_TIMEZONE', '+07:00'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'timezone' => env('DB_TIMEZONE', '+07:00'),
            'database' => env('DB_DATABASE', DATABASE_PATH('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => false,
        ],
        'flyingmysql' => [
            'driver' => 'mysql',
            'host' => '',
            'port' => '',
            'database' => '',
            'username' => '',
            'password' => '',
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
        ],
        'flyingpgsql' => [
            'driver' => 'pgsql',
            'host' => '',
            'port' => '',
            'database' => '',
            'username' => '',
            'password' => '',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'flyingsqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => '',
            'port' => '',
            'database' => '',
            'username' => '',
            'password' => '',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ]

    ],
    'migrations' => 'migrations',
    'redis' => [
        'client' => 'predis',
        'options' => [
            'parameters' => ['password' => env('REDIS_PASSWORD', null)],
        ],
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            'client' => env('REDIS_CLIENT', 'phpredis'),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
            'client' => env('REDIS_CLIENT', 'phpredis'),
        ]

    ],
    'dbal' => [
        'types' => [
            'geometry' => '\Doctrine\DBAL\Types\StringType',
            'geography' => '\Doctrine\DBAL\Types\StringType',
        ]
    ]
];

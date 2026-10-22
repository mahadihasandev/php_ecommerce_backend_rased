<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION') ?: (env('DATABASE_URL') || env('POSTGRES_URL') || env('PGHOST') || env('DB_URL') ? 'pgsql' : 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => (env('DB_DATABASE') && (env('DB_DATABASE') === ':memory:' || str_contains(env('DB_DATABASE'), '.sqlite') || file_exists(env('DB_DATABASE'))))
                ? env('DB_DATABASE')
                : database_path('database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => (function () {
            $rawUrl = env('DB_URL') ?: env('DATABASE_URL') ?: env('POSTGRES_URL') ?: env('DATABASE_URL_UNPOOLED');

            if ($rawUrl) {
                $parsed = parse_url($rawUrl);
                $host = $parsed['host'] ?? '127.0.0.1';
                $port = $parsed['port'] ?? 5432;
                $database = ltrim($parsed['path'] ?? '/neondb', '/');
                $username = isset($parsed['user']) ? urldecode($parsed['user']) : 'root';
                $password = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
            } else {
                $host = env('DB_HOST') ?: env('PGHOST_UNPOOLED') ?: env('PGHOST', '127.0.0.1');
                $port = env('DB_PORT') ?: env('PGPORT', '5432');
                $database = env('DB_DATABASE') ?: env('PGDATABASE', 'laravel');
                $username = env('DB_USERNAME') ?: env('PGUSER', 'root');
                $password = env('DB_PASSWORD') ?: env('PGPASSWORD', '');
            }

            // Extract Neon endpoint ID if connecting to neon.tech
            $endpoint = '';
            if (str_contains($host, '.neon.tech')) {
                $parts = explode('.', $host);
                $endpoint = str_replace('-pooler', '', $parts[0]);
                $host = str_replace('-pooler.', '.', $host);
            }

            $finalHost = $endpoint ? "{$host};options='endpoint={$endpoint}'" : $host;

            return [
                'driver' => 'pgsql',
                'url' => null, // Explicitly null so Laravel does not parse and wipe the options
                'host' => $finalHost,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8',
                'prefix' => '',
                'prefix_indexes' => true,
                'search_path' => 'public',
                'sslmode' => env('DB_SSLMODE', 'require'),
                'options' => [
                    \PDO::ATTR_EMULATE_PREPARES => true,
                ],
            ];
        })(),

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
            'parameters' => [
                'timeout' => env('REDIS_TIMEOUT', 0.2),
                'read_write_timeout' => env('REDIS_READ_TIMEOUT', 0.5),
            ],
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'timeout' => env('REDIS_TIMEOUT', 0.2),
            'read_timeout' => env('REDIS_READ_TIMEOUT', 0.5),
            'max_retries' => env('REDIS_MAX_RETRIES', 1),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 50),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 200),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'timeout' => env('REDIS_TIMEOUT', 0.2),
            'read_timeout' => env('REDIS_READ_TIMEOUT', 0.5),
            'max_retries' => env('REDIS_MAX_RETRIES', 1),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 50),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 200),
        ],

    ],

];

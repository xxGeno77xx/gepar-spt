<?php

return [
    'oracle' => [
        'driver' => 'oracle',
        'tns' => env('DB_TNS_oracle', ''),
        'host' => env('DB_HOST_oracle', ''),
        'port' => env('DB_PORT_oracle', '1521'),
        'database' => env('DB_DATABASE_oracle', ''),
        'service_name' => env('DB_SERVICE_NAME_oracle', ''),
        'username' => env('DB_USERNAME_oracle', ''),
        'password' => env('DB_PASSWORD_oracle', ''),
        'charset' => env('DB_CHARSET_oracle', 'AL32UTF8'),
        'prefix' => env('DB_PREFIX_oracle', ''),
        'prefix_schema' => env('DB_SCHEMA_PREFIX_oracle', ''),
        'edition' => env('DB_EDITION_oracle', 'ora$base'),
        'server_version' => env('DB_SERVER_VERSION_oracle', '11g'),
        'load_balance' => env('DB_LOAD_BALANCE_oracle', 'yes'),
        'dynamic' => [],
    ],
    'sessionVars' => [
        'NLS_TIME_FORMAT' => 'HH24:MI:SS',
        'NLS_DATE_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
        'NLS_TIMESTAMP_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
        'NLS_TIMESTAMP_TZ_FORMAT' => 'YYYY-MM-DD HH24:MI:SS TZH:TZM',
        'NLS_NUMERIC_CHARACTERS' => '.,',
    ],
];

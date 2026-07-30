<?php

return [
    'enabled' => env('INSTALLER_ENABLED', true),
    'enforce' => env('INSTALLER_ENFORCE', true),
    'lock_path' => env('INSTALLER_LOCK_PATH', storage_path('app/installed')),
    'environment_path' => env('INSTALLER_ENV_PATH', base_path('.env')),
    'database_connection' => env('INSTALLER_DB_CONNECTION', 'mysql'),
    'storage_link' => public_path('storage'),
    'storage_target' => storage_path('app/public'),
    'minimum_php' => '8.2.0',
    'extensions' => [
        'pdo', 'openssl', 'mbstring', 'tokenizer', 'xml', 'ctype',
        'json', 'bcmath', 'fileinfo', 'zip',
    ],
    'image_driver_required' => true,
];

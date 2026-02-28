<?php

return [
    'database_prefix' => env('TENANCY_DATABASE_PREFIX', 'sd_tenant_'),
    'database_host' => env('TENANCY_DB_HOST', env('DB_HOST', '127.0.0.1')),
    'database_port' => (int) env('TENANCY_DB_PORT', env('DB_PORT', 3306)),
    'database_charset' => env('TENANCY_DB_CHARSET', 'utf8mb4'),
    'database_collation' => env('TENANCY_DB_COLLATION', 'utf8mb4_unicode_ci'),
    'database_admin_host' => env('TENANCY_DB_ADMIN_HOST', env('DB_HOST', '127.0.0.1')),
    'database_admin_port' => (int) env('TENANCY_DB_ADMIN_PORT', env('DB_PORT', 3306)),
    'database_admin_database' => env('TENANCY_DB_ADMIN_DATABASE', env('DB_DATABASE', 'mysql')),
    'database_admin_username' => env('TENANCY_DB_ADMIN_USERNAME', env('DB_USERNAME')),
    'database_admin_password' => env('TENANCY_DB_ADMIN_PASSWORD', env('DB_PASSWORD')),
    'config_disk' => env('TENANCY_CONFIG_DISK', 'local'),
    'config_directory' => env('TENANCY_CONFIG_DIRECTORY', 'tenants'),
    'reserved_paths' => array_values(array_filter(array_map(
        static fn (string $value): string => trim(strtolower($value)),
        explode(',', (string) env(
            'TENANCY_RESERVED_PATHS',
            'admin,api,pricing,faq,blog,login,logout,register,password'
        ))
    ))),
];

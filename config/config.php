<?php

/**
 * Application Configuration
 */

return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'tabletap',
        'username' => 'henilcode',
        'password' => 'henilcode',
        'charset' => 'utf8mb4'
    ],

    'app' => [
        'name' => 'TableTap',
        'url' => 'http://localhost/TableBook',
        'timezone' => 'America/New_York',
        'reservation_duration' => 90, // minutes
        'reservation_buffer' => 10, // minutes between reservations
        'cancellation_cutoff' => 2, // hours before reservation
    ],

    'session' => [
        'lifetime' => 7200, // 2 hours
        'cookie_httponly' => true,
        'cookie_secure' => false, // set to true in production with HTTPS
    ],

    'security' => [
        'csrf_token_name' => '_token',
        'password_min_length' => 8,
    ]
];

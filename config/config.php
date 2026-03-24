<?php

/**
 * Application Configuration
 */

return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'orm',
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
    ],

    'razorpay' => [
        'key_id' => 'rzp_test_SETKXKZKO9NW6n',
        'key_secret' => 'mhS7kmhuo7KPP3F1Axig2jcn',
    ],

    'mail' => [
        'driver' => 'smtp',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'HenilCode',
        'password' => 'vlqyxhejqresrysg',
        'encryption' => 'tls',
        'from_address' => 'henilcode@gmail.com',
        'from_name' => 'TableTap Reservation',
    ],
];
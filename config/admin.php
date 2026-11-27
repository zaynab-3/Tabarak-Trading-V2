<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Secret Admin URL Path Prefix
    |--------------------------------------------------------------------------
    |
    | Instead of '/admin', all administrative routes are prefixed with this
    | secret slug. Requests to '/admin' or '/login' will return 404 Not Found.
    |
    */
    'path' => env('ADMIN_PATH', 'tabarak-manage'),

    /*
    |--------------------------------------------------------------------------
    | Secret Access Key / Passcode
    |--------------------------------------------------------------------------
    |
    | Access to the admin login portal requires this secret key (e.g. ?key=...).
    | Without it, the login page returns a 404 Not Found as if it doesn't exist.
    |
    */
    'access_key' => env('ADMIN_ACCESS_KEY', ''),
];

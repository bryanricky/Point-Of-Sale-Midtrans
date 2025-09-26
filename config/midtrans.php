<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for Midtrans payment gateway.
    | You can find your credentials in your Midtrans account dashboard.
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION'),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED'),
    'is_3ds' => env('MIDTRANS_IS_3DS'),

];
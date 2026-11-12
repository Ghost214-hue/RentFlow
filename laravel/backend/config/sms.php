<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    // HTTPS SMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are used by the SmsService to send SMS messages via an
    | HTTPS API endpoint. Configure them in your .env file.
    |
    */

    'endpoint' => env('SMS_ENDPOINT', ''),

    'api_key' => env('SMS_API_KEY', ''),

    'sender_id' => env('SMS_SENDER_ID', 'RENTFLOW'),

];
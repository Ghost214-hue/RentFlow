<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Password hashing
    |--------------------------------------------------------------------------
    |
    | This file did not exist, so the driver and cost were implicit in the
    | framework's own fallbacks -- invisible, and easy to change by accident.
    | .env already pinned BCRYPT_ROUNDS=12; this makes that explicit and
    | documents what the number costs.
    |
    | bcrypt cost 12 is ~250-400ms per verify, which is a deliberate trade:
    | it is slow enough to make offline cracking expensive, and fast enough
    | that an interactive login still feels instant. Existing hashes are bcrypt
    | and must keep verifying as bcrypt, so Argon2id would need a rehash
    | migration rather than a config change.
    |
    */

    'driver' => env('HASH_DRIVER', 'bcrypt'),

    'bcrypt' => [
        'rounds' => (int) env('BCRYPT_ROUNDS', 10),
        'verify' => true,
        // Limit the password length a hash will even be computed for. bcrypt
        // silently truncates beyond 72 bytes, so without this a 1MB password
        // "works" while only its first 72 bytes matter. Rejecting it is honest.
        'limit' => 72,
    ],

    'argon' => [
        'memory' => (int) env('ARGON_MEMORY', 65536),
        'threads' => (int) env('ARGON_THREADS', 1),
        'time' => (int) env('ARGON_TIME', 4),
        'verify' => true,
    ],

    'rehash_on_login' => true,

];

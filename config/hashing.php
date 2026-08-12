<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | UPLYFT uses Argon2id for superior password hashing security.
    | Argon2id is resistant to both side-channel and GPU attacks.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASHING_DRIVER', 'argon2id'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => env('BCRYPT_VERIFY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Memory: 65536 KiB (64MB)
    | Threads: 2
    | Time: 4 iterations
    |
    | These values provide a good balance between security and performance.
    |
    */

    'argon' => [
        'memory'  => env('ARGON_MEMORY', 65536),
        'threads' => env('ARGON_THREADS', 2),
        'time'    => env('ARGON_TIME', 4),
        'verify'  => env('ARGON_VERIFY', true),
    ],

];

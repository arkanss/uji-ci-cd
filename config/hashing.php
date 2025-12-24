<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => 'argon2id',

    /*
    |--------------------------------------------------------------------------
    | Argon2i Driver Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure the memory, time, and threads memory cost
    | for Argon2i hashing. This costs affects the complexity of the
    | hashes produced by the driver. By default, the values are
    | positioned to take seconds to generate a single hash.
    |
    */

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
    ],

];

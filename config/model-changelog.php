<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global Excluded Fields
    |--------------------------------------------------------------------------
    | These fields are excluded from ALL models by default.
    */

    'global_excluded' => [
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'email_verified_at',
        'password',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mask Value
    |--------------------------------------------------------------------------
    | The placeholder used when a hidden field changes.
    */

    'mask_value' => '********',

];
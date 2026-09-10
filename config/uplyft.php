<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global Admin Emergency Override
    |--------------------------------------------------------------------------
    | When set to true (via .env GLOBAL_ADMIN_EMERGENCY_OVERRIDE=true),
    | the TenantPrivacyScope is bypassed and the Global Admin can access
    | all institute data. This should NEVER be true in normal operation.
    |
    */
    'global_admin_emergency_override' => env('GLOBAL_ADMIN_EMERGENCY_OVERRIDE', false),

    /*
    |--------------------------------------------------------------------------
    | Institute Database Prefix
    |--------------------------------------------------------------------------
    | Used when auto-generating tenant_db_name for each institute.
    |
    */
    'institute_db_prefix' => env('INSTITUTE_DB_PREFIX', 'uplifyt_inst_'),

    /*
    |--------------------------------------------------------------------------
    | Subscription Tier Definitions
    |--------------------------------------------------------------------------
    */
    'subscription_tiers' => [
        'basic'    => 'Basic',
        'standard' => 'Standard',
        'premium'  => 'Premium',
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature keys (tenant_features.feature_key)
    |--------------------------------------------------------------------------
    |
    | Maps former versions table columns to stable keys.
    |
    */

    'features' => [
        'tameru' => [
            'label' => 'TAMERU',
            'legacy_column' => 'tameru',
            'default' => true,
        ],
        'card' => [
            'label' => '名刺',
            'legacy_column' => '名刺',
            'default' => false,
        ],
        'flow' => [
            'label' => 'フロー',
            'legacy_column' => 'フロー',
            'default' => false,
        ],
        'schedule' => [
            'label' => 'スケジュール',
            'legacy_column' => 'スケジュール',
            'default' => false,
        ],
        'transcribe' => [
            'label' => '文書',
            'legacy_column' => '文書',
            'default' => false,
        ],
        'banban' => [
            'label' => 'BANBAN',
            'legacy_column' => 'BANBAN',
            'default' => false,
        ],
        'ichifuji' => [
            'label' => 'TAMERU AI',
            'legacy_column' => 'ichifuji',
            'default' => false,
        ],
    ],

    'support_user_name' => env('TENANCY_SUPPORT_USER_NAME', 'platform_support'),

    'support_user_display_name' => env('TENANCY_SUPPORT_USER_DISPLAY_NAME', 'サポート（platform）'),

    /*
    |--------------------------------------------------------------------------
    | Tenant file storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'cloud_disk' => env('TENANCY_STORAGE_CLOUD_DISK', 's3'),
        // Existing on-prem installs keep files directly under file_upload_path.
        'onpre_flat_default' => env('TENANCY_STORAGE_ONPRE_FLAT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | DB credential split (app vs provision)
    |--------------------------------------------------------------------------
    |
    | App/control/tenant use the shared application MySQL user.
    | Provision uses a separate user that may CREATE DATABASE.
    | Set TENANCY_REQUIRE_SEPARATE_PROVISION_USER=true to fail tenancy:db-check
    | when PROVISION_DB_USERNAME equals the app username.
    |
    */
    'db' => [
        'require_separate_provision_user' => env('TENANCY_REQUIRE_SEPARATE_PROVISION_USER', false),
    ],

];

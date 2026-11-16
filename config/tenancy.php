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
            'name' => 'TAMERU',
            'description' => '電子帳簿保存システム',
            'icon' => 'img/header/tameru_logo_only.svg',
            'legacy_column' => 'tameru',
            'default' => true,
        ],
        'card' => [
            'label' => '名刺',
            'name' => 'ReadBridge',
            'description' => '名刺管理システム',
            'icon' => 'img/header/readbridge_icon.svg',
            'legacy_column' => '名刺',
            'default' => false,
        ],
        'flow' => [
            'label' => 'フロー',
            'name' => 'Rapid',
            'description' => '電子申請システム',
            'icon' => 'img/header/rapid_logo_only.svg',
            'legacy_column' => 'フロー',
            'default' => false,
        ],
        'schedule' => [
            'label' => 'スケジュール',
            'name' => 'Skett',
            'description' => 'スケジュール管理システム',
            'icon' => 'img/header/skett_logo_only.svg',
            'legacy_column' => 'スケジュール',
            'default' => false,
        ],
        'transcribe' => [
            'label' => '文書',
            'name' => '文書抽出',
            'description' => '文書から文字を読み取る',
            'icon' => 'img/document_2_line.svg',
            'legacy_column' => '文書',
            'default' => false,
            'hidden' => true,
        ],
        'banban' => [
            'label' => 'BANBAN',
            'name' => 'BANBAN',
            'description' => '帳票をまとめて取り込む',
            'mark' => 'B',
            'parent' => 'tameru',
            'legacy_column' => 'BANBAN',
            'default' => false,
        ],
        'ichifuji' => [
            'label' => 'TAMERU AI',
            'name' => 'TAMERU AI',
            'description' => '帳票をAIで読み取る',
            'mark' => 'AI',
            'parent' => 'tameru',
            'legacy_column' => 'ichifuji',
            'default' => false,
        ],
    ],

    'support_user_name' => env('TENANCY_SUPPORT_USER_NAME', 'platform_support'),

    'support_user_display_name' => env('TENANCY_SUPPORT_USER_DISPLAY_NAME', 'サポート（platform）'),

    'otp' => [
        'domain' => 'ast-sys.co.jp',
        'ttl_seconds' => 600,
        'resend_seconds' => 30,
        'max_attempts' => 5,
    ],

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

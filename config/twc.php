<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TWC Content Studio Configuration
    |--------------------------------------------------------------------------
    */

    // Audit log retention in days
    'audit_retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 90),

    // Default timezone for new users
    'default_timezone' => env('TWC_DEFAULT_TIMEZONE', 'UTC'),

    // Provider defaults
    'providers' => [
        'tts'     => env('TWC_TTS_PROVIDER', 'edge_tts'),
        'stock'   => env('TWC_STOCK_PROVIDER', 'pexels'),
        'storage' => env('TWC_STORAGE_PROVIDER', 'google_drive'),
        'ai'      => env('TWC_AI_PROVIDER', 'mock'),
    ],

    // Google Drive
    'google_drive' => [
        'root_folder_id'   => env('GOOGLE_DRIVE_FOLDER_ID', ''),
        'credentials_path' => env('GOOGLE_APPLICATION_CREDENTIALS', storage_path('app/google-service-account.json')),
    ],

    // Script generation
    'scripts' => [
        'words_per_second' => 2.5,   // ~150 words per minute
        'max_versions'     => 20,
    ],

    // Pipeline
    'pipeline' => [
        'max_qa_loops'           => 3,
        'auto_create_drive'      => (bool) env('TWC_AUTO_CREATE_DRIVE', true),
        'default_target_duration' => 60,
    ],
];
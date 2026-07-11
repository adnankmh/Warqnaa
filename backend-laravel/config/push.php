<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (HTTP v1)
    |--------------------------------------------------------------------------
    |
    | The service is deliberately fail-safe: chat, rooms and friendship actions
    | never fail merely because Firebase credentials are not configured.
    |
    */
    'enabled' => filter_var(env('PUSH_NOTIFICATIONS_ENABLED', true), FILTER_VALIDATE_BOOL),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'service_account_b64' => env('FIREBASE_SERVICE_ACCOUNT_B64'),
    'service_account_json' => env('FIREBASE_SERVICE_ACCOUNT_JSON'),
    'service_account_path' => env('FIREBASE_SERVICE_ACCOUNT_PATH'),
    'timeout_seconds' => (int) env('FIREBASE_HTTP_TIMEOUT', 12),
    'default_channel_id' => env('FIREBASE_ANDROID_CHANNEL_ID', 'warqna_messages'),
];

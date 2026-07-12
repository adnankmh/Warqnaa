<?php
return [
    'version' => env('WARQNA_VERSION', '1.70.0'),
    'build' => (int) env('WARQNA_BUILD', 170),
    'frontend_url' => env('FRONTEND_URL', 'http://127.0.0.1:8088'),
    'support_email' => env('SUPPORT_EMAIL', 'support@warqna.example'),
    'support_url' => env('SUPPORT_URL', '/legal/support'),
    'privacy_url' => env('PRIVACY_URL', '/legal/privacy'),
    'terms_url' => env('TERMS_URL', '/legal/terms'),
    'account_deletion_grace_days' => (int) env('ACCOUNT_DELETION_GRACE_DAYS', 30),
    'allowed_local_demo' => filter_var(env('ALLOW_LOCAL_DEMO_MODE', true), FILTER_VALIDATE_BOOL),
    'token_transfer_fee_percent' => (int) env('TOKEN_TRANSFER_FEE_PERCENT', 10),
    'health_show_counts' => filter_var(env('HEALTH_SHOW_COUNTS', false), FILTER_VALIDATE_BOOL),
];

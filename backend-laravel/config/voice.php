<?php

return [
    'stun_urls' => array_values(array_filter(array_map('trim', explode(',', (string) env('VOICE_STUN_URLS', 'stun:stun.l.google.com:19302'))))),
    'turn_urls' => array_values(array_filter(array_map('trim', explode(',', (string) env('VOICE_TURN_URLS', env('VOICE_TURN_URL', '')))))),
    'turn_username' => env('VOICE_TURN_USERNAME', ''),
    'turn_credential' => env('VOICE_TURN_CREDENTIAL', ''),
];

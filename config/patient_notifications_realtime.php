<?php

return [
    // Reverb is published as localhost:8082 in the current NORHNAIM Docker setup.
    // For a remote HTTPS website, configure an HTTPS/WSS reverse proxy separately.
    'host' => env('PATIENT_NOTIFICATIONS_WS_HOST'),
    'port' => (int) env('PATIENT_NOTIFICATIONS_WS_PORT', env('REVERB_PUBLIC_PORT', 8082)),
    // null = match the browser-facing HTTP/HTTPS scheme.
    'secure' => env('PATIENT_NOTIFICATIONS_WS_SECURE'),
];

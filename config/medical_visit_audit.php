<?php

return [
    // A dedicated shared secret, different from APP_KEY and WebRTC invitation secrets.
    // Set identical TELEMEDICINE_AUDIT_SECRET in Laravel and the signaling service.
    'secret' => env('TELEMEDICINE_AUDIT_SECRET', ''),

    // Reject requests signed with stale timestamps.
    'max_clock_skew_seconds' => 300,
];

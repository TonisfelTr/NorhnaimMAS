<?php

return [
    // Complete HTTPS URL of the existing WebRTC page, e.g. https://video.example/demo.
    // This package does not deploy or reconfigure the signaling/TURN containers.
    'webrtc_url' => env('TELEMEDICINE_WEBRTC_URL', ''),
    // The SAME hex secret used by signaling's bytes.fromhex(...), at least 32 bytes.
    'signaling_secret' => env('TELEMEDICINE_SIGNALING_SECRET', ''),

    // A connection window, not a clinical duration or a substitute for the work schedule.
    'join_before_minutes' => (int) env('TELEMEDICINE_JOIN_BEFORE_MINUTES', 15),
    'join_after_minutes' => (int) env('TELEMEDICINE_JOIN_AFTER_MINUTES', 120),
    'api_timeout_seconds' => 20,
];

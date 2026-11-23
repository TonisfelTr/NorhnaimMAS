<?php

return [
    // No real SMS is sent until a provider is explicitly configured.
    'sms_driver' => env('TELEMEDICINE_SMS_DRIVER', 'disabled'), // disabled|smsru
    'smsru_api_id' => env('SMSRU_API_ID', ''),
    'smsru_sender' => env('SMSRU_SENDER', ''), // optional approved sender
    'sms_minutes_before' => (int) env('TELEMEDICINE_SMS_MINUTES_BEFORE', 15),
    'doctor_notice_minutes_before' => (int) env('TELEMEDICINE_DOCTOR_NOTICE_MINUTES_BEFORE', 30),
];

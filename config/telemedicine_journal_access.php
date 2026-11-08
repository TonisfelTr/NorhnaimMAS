<?php

// Explicit server-side bindings for clinic_admin users without Doctor profiles.
// .env: TELEMEDICINE_CLINIC_ADMIN_MAP='{"USER_ID":[CLINIC_ID]}'
// Replace placeholders with VERIFIED numeric IDs. The clinic_admin role is still required.
$map = json_decode((string) env('TELEMEDICINE_CLINIC_ADMIN_MAP', '{}'), true);

return ['clinic_admin_user_clinics' => is_array($map) ? $map : []];

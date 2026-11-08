<?php

return [
    'enabled' => true,

    // Key = authenticated doctor's actual clinic_id. Never obtained from a form field.
    // Configure each clinic separately before issuing; drafts work without a profile.
    // Copy the example below, replace every placeholder, and remove the comment delimiters.
    'clinics' => [
        /*
        123 => [
            'prescribing_enabled' => false, // Enable after confirming the clinic's participation/authority.
            'name' => 'Полное наименование медицинской организации',
            'address' => 'Адрес медицинской организации',
            'phone' => '',
            'kind' => 'organization', // organization or individual
            'ogrn' => '',             // 13 digits (ОГРН) or 15 digits (ОГРНИП)
            'code' => '',             // Organization code, if separately assigned by the program.
            'license_number' => '',   // Required for individual entrepreneurs.
            'license_date' => '',     // YYYY-MM-DD, required for individual entrepreneurs.
            'license_authority' => '',
            'okato_prefix' => '',     // First TWO digits of ОКАТО, not a vehicle region code.
            'series' => '',           // Optional default, from the clinic's allocated numbering scheme.
            'numbering_source' => '', // e.g. reference to the regional scheme/allocated range.
        ],
        */
    ],
];

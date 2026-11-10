<?php

return [
    /*
     * На этом этапе кабинет работает без внешней интеграции.
     *
     * Когда подключим официальный шлюз N3/ЕГИСЗ/ФРМР/ФРДО,
     * заполняем endpoint/token. До этого система честно показывает
     * «Источник не подключён», а не «Подтверждено».
     */
    'providers' => [
        'frdo' => [
            'endpoint' => env('DOCTOR_VERIFY_FRDO_URL'),
            'token' => env('DOCTOR_VERIFY_FRDO_TOKEN'),
            'connect_timeout' => 5,
            'timeout' => 20,
        ],

        'frmr' => [
            'endpoint' => env('DOCTOR_VERIFY_FRMR_URL'),
            'token' => env('DOCTOR_VERIFY_FRMR_TOKEN'),
            'connect_timeout' => 5,
            'timeout' => 20,
        ],
    ],
];

<?php

return [
    // Поддерживаем оба распространённых имени переменной окружения.
    // Приоритет оставляем за уже используемым в проекте DADATA_API_KEY.
    'dadata_api' => env('DADATA_API_KEY', env('DADATA_TOKEN')),
    'token' => env('DADATA_TOKEN', env('DADATA_API_KEY')),
];

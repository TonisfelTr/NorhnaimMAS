<?php

return [
    'recent_anamneses_limit' => (int) env('HOSPITALIZATION_AI_ANAMNESES_LIMIT', 5),
    'recent_prescriptions_limit' => (int) env('HOSPITALIZATION_AI_PRESCRIPTIONS_LIMIT', 12),
    'max_clinical_text_chars' => (int) env('HOSPITALIZATION_AI_MAX_TEXT_CHARS', 24000),

    'notice' => 'ИИ формирует только проект и не заменяет клиническое решение врача.',

    'recommendation_labels' => [
        'supports_planned' => 'Данные поддерживают плановую госпитализацию',
        'supports_emergency' => 'Данные поддерживают экстренную госпитализацию',
        'consider_planned' => 'Рассмотреть плановую госпитализацию',
        'consider_emergency' => 'Рассмотреть экстренную госпитализацию',
        'outpatient_or_observation' => 'Рассмотреть амбулаторное ведение или наблюдение',
        'insufficient_data' => 'Недостаточно данных',
    ],
];

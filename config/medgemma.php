<?php

return [
    'queue_connection' => env('MEDGEMMA_QUEUE_CONNECTION', 'rabbitmq'),
    'queue' => env('MEDGEMMA_QUEUE', 'medgemma'),

    'status_store' => env('MEDGEMMA_STATUS_STORE', 'redis'),
    'status_ttl_minutes' => (int) env('MEDGEMMA_STATUS_TTL', 30),

    'think' => filter_var(
        env('MEDGEMMA_THINK', false),
        FILTER_VALIDATE_BOOLEAN
    ),
    'num_ctx' => (int) env('MEDGEMMA_NUM_CTX', 4096),
    'num_predict' => (int) env('MEDGEMMA_NUM_PREDICT', 220),
    'repeat_penalty' => (float) env('MEDGEMMA_REPEAT_PENALTY', 1.18),
    'repeat_last_n' => (int) env('MEDGEMMA_REPEAT_LAST_N', 128),
    'keep_alive' => env('MEDGEMMA_KEEP_ALIVE', '10m'),
    'debug_log_clean_max_chars' => (int) env(
        'MEDGEMMA_DEBUG_LOG_CLEAN_MAX_CHARS',
        3000
    ),

    // MedGemma diagnostics are isolated in a separate rotating log channel.
    'debug_log_responses' => filter_var(
        env('MEDGEMMA_DEBUG_LOG_RESPONSES', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    'debug_log_channel' => env(
        'MEDGEMMA_DEBUG_LOG_CHANNEL',
        'medgemma_debug'
    ),

    /*
     * Raw model reasoning can be very large. For diagnostics we keep
     * both the beginning and the end (where <unused95> final answer usually
     * appears) instead of flooding the log with the entire response.
     */
    'debug_log_preview_head' => (int) env(
        'MEDGEMMA_DEBUG_LOG_PREVIEW_HEAD',
        1500
    ),

    'debug_log_preview_tail' => (int) env(
        'MEDGEMMA_DEBUG_LOG_PREVIEW_TAIL',
        1500
    ),

    'field_num_predict' => [
        'doctor_instruction' => 160,
        'indication' => 180,
        'contraindications' => 240,
        'adverse_reactions' => 200,
        'missed_dose' => 140,
        'overdose_actions' => 260,
        'urgent_symptoms' => 200,
    ],

    'model' => env(
        'MEDGEMMA_MODEL',
        env('OLLAMA_REVIEW_MODEL', 'medgemma1.5:4b')
    ),

    'job_timeout_seconds' => (int) env('MEDGEMMA_JOB_TIMEOUT', 1200),
    'field_http_timeout_seconds' => (int) env(
        'MEDGEMMA_FIELD_HTTP_TIMEOUT',
        120
    ),
    'connect_timeout_seconds' => (int) env(
        'MEDGEMMA_CONNECT_TIMEOUT',
        15
    ),
    'stale_after_seconds' => (int) env(
        'MEDGEMMA_STALE_AFTER',
        240
    ),

    'context' => [
        'active_medications_limit' => (int) env(
            'MEDGEMMA_ACTIVE_MEDICATIONS_LIMIT',
            30
        ),

        'anamneses_limit' => (int) env('MEDGEMMA_ANAMNESES_LIMIT', 5),
        'epicrises_limit' => (int) env('MEDGEMMA_EPICRISES_LIMIT', 5),
        'item_max_chars' => (int) env('MEDGEMMA_CONTEXT_ITEM_MAX_CHARS', 2200),
        'section_max_chars' => (int) env('MEDGEMMA_CONTEXT_SECTION_MAX_CHARS', 5000),
    ],
];

<?php

// PATIENT_TREATMENT_WIZARD_V12_RABBITMQ_CONTEXT

return [
    /*
     * Jobs go through RabbitMQ.
     * Redis is used only for short-lived task status/progress.
     */
    'queue_connection' => env(
        'MEDGEMMA_QUEUE_CONNECTION',
        'rabbitmq'
    ),
    'queue' => env(
        'MEDGEMMA_QUEUE',
        'medgemma'
    ),

    'status_store' => env(
        'MEDGEMMA_STATUS_STORE',
        'redis'
    ),
    'status_ttl_minutes' => (int) env(
        'MEDGEMMA_STATUS_TTL',
        30
    ),

    'model' => env(
        'MEDGEMMA_MODEL',
        env(
            'OLLAMA_REVIEW_MODEL',
            'medgemma1.5:4b'
        )
    ),

    'job_timeout_seconds' => (int) env(
        'MEDGEMMA_JOB_TIMEOUT',
        600
    ),
    'http_timeout_seconds' => (int) env(
        'MEDGEMMA_HTTP_TIMEOUT',
        540
    ),
    'connect_timeout_seconds' => (int) env(
        'MEDGEMMA_CONNECT_TIMEOUT',
        15
    ),
    'stale_after_seconds' => (int) env(
        'MEDGEMMA_STALE_AFTER',
        180
    ),

    /*
     * Clinical context limits.
     * Keep enough recent history to catch important contraindications,
     * reactions, diagnoses and treatment details without overflowing num_ctx.
     */
    'context' => [
        'anamneses_limit' => (int) env(
            'MEDGEMMA_ANAMNESES_LIMIT',
            5
        ),
        'epicrises_limit' => (int) env(
            'MEDGEMMA_EPICRISES_LIMIT',
            5
        ),
        'item_max_chars' => (int) env(
            'MEDGEMMA_CONTEXT_ITEM_MAX_CHARS',
            3500
        ),
        'section_max_chars' => (int) env(
            'MEDGEMMA_CONTEXT_SECTION_MAX_CHARS',
            12000
        ),
    ],
];

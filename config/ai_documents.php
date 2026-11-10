<?php

return [
    'queue' => env('AI_DOCUMENT_QUEUE', 'ai-documents'),
    'poll_ms' => (int) env('AI_DOCUMENT_POLL_MS', 900),
    'max_anamneses' => (int) env('AI_DOCUMENT_MAX_ANAMNESES', 20),
];

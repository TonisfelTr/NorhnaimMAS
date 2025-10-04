<?php

return [
    'llm' => [
        'always_on' => env('NDX_LLM_ALWAYS_ON', true),
        'model'     => env('OLLAMA_MODEL', 'qwen2:7b-instruct-q4_K_M'),
        'timeout'   => (int) env('OLLAMA_TIMEOUT', 45),
        'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.0),
        'max_json_chars' => 20000,
    ],
    'cache' => [
        'ttl_sec' => (int) env('NDX_CACHE_TTL', 600),
        'store'   => 'redis',
        'prefix'  => 'ndx:extract:',
    ],
];

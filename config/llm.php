<?php

return [
    'provider' => env('LLM_PROVIDER', 'ollama'),

    'ndx' => [
        // Принудительно вызывать LLM, даже если словарь уже что-то нашёл.
        'always_on' => (bool) env('NDX_LLM_ALWAYS_ON', true),

        // Порог для отключения LLM по словарному результату.
        'min_positive_for_no_llm' => (int) env(
            'NDX_MIN_POSITIVE_FOR_NO_LLM',
            5
        ),
        'max_raw_before_llm' => (int) env('NDX_MAX_RAW_BEFORE_LLM', 140),

        // Если словарь не дал кандидатов — размер fallback-набора симптомов.
        'fallback_prompt_symptom_limit' => (int) env(
            'NDX_FALLBACK_PROMPT_LIMIT',
            120
        ),

        'min_sentences' => (int) env('NDX_MIN_SENTENCES', 15),
    ],

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://ollama:11434'),
        'primary_model' => env('OLLAMA_PRIMARY_MODEL', env('OLLAMA_MODEL', 'qwen3:14b')        ),
        'model' => env('OLLAMA_PRIMARY_MODEL', env('OLLAMA_MODEL', 'qwen3:14b')),
        'review_enabled' => (bool) env('OLLAMA_REVIEW_ENABLED', true),
        'review_model' => env('OLLAMA_REVIEW_MODEL','medgemma1.5:4b'),
        'connect_timeout' => (int) env('OLLAMA_CONNECT_TIMEOUT', 10),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 300),
        'review_timeout' => (int) env('OLLAMA_REVIEW_TIMEOUT', 300),
        'num_ctx' => (int) env('OLLAMA_NUM_CTX', 12288),
        'review_num_ctx' => (int) env('OLLAMA_REVIEW_NUM_CTX', 16384),
        'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.0),
        'review_temperature' => (float) env('OLLAMA_REVIEW_TEMPERATURE',0.0),
        'think' => (bool) env('OLLAMA_THINK', false),
        'review_think' => (bool) env('OLLAMA_REVIEW_THINK', false),
        'keep_alive' => env('OLLAMA_KEEP_ALIVE', '5m'),
        'review_keep_alive' => env('OLLAMA_REVIEW_KEEP_ALIVE', 0),
        'review_max_tokens' => (int) env('OLLAMA_REVIEW_MAX_TOKENS',1800),
    ],

    'openai' => [
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'review_model' => env('OPENAI_REVIEW_MODEL', env(
            'OPENAI_MODEL',
            'gpt-4o-mini'
        )),
        'connect_timeout' => (int) env('OPENAI_CONNECT_TIMEOUT', 10),
        'timeout' => (int) env('OPENAI_TIMEOUT', 45),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.0),
    ],
];

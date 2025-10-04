<?php

return [
    'provider' => env('LLM_PROVIDER', 'ollama'),

    'ndx' => [
        // Принудительно дергать LLM (даже если словарь что-то нашёл)
        'always_on' => (bool) env('NDX_LLM_ALWAYS_ON', true),

        // Порог для отключения LLM по «словарику»
        'min_positive_for_no_llm' => (int) env('NDX_MIN_POSITIVE_FOR_NO_LLM', 5),
        'max_raw_before_llm'      => (int) env('NDX_MAX_RAW_BEFORE_LLM', 140),

        // Если словарь не дал кандидатов — сколько симптомов берём в фолбэк-промпт
        'fallback_prompt_symptom_limit' => (int) env('NDX_FALLBACK_PROMPT_LIMIT', 120),

        // Валидация входного текста
        'min_sentences' => (int) env('NDX_MIN_SENTENCES', 15),
    ],

    // Local provider for Ollama
    'ollama' => [
        'base_url'    => env('OLLAMA_BASE_URL', 'http://ollama:11434'),
        'model'       => env('OLLAMA_MODEL', 'qwen2:7b-instruct-q4_K_M'),
        'timeout'     => (int) env('OLLAMA_TIMEOUT', 45),
        'num_ctx'     => (int) env('OLLAMA_NUM_CTX', 4096),
        'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.0),
    ],

    // For OpenAI API
    'openai' => [
        'base_url'    => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'api_key'     => env('OPENAI_API_KEY'),
        'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout'     => (int) env('OPENAI_TIMEOUT', 45),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.0),
    ],
];

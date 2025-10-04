<?php

namespace App\Services;

use App\Models\Diagnose;
use App\Models\Symptom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use IntlBreakIterator;
use RuntimeException;

class AnamnesisAnalyzeService
{
    // ===== Настройки =====
    private const MAX_PROMPT_SYMPTOMS = 120;
    private const MAX_ALIASES_PER_SYMPTOM = 20;
    private const MIN_TOTAL_SCORE = 0.33;

    private const NEGATION_WINDOW_TOKENS = 7;
    private const BATCH_REGEX_SIZE = 400;

    private const CACHE_TTL_INDEX_MIN = 60;
    private const CACHE_TTL_BITSETS_MIN = 1440;

    private const CACHE_KEY_INDEX = 'nn:symptom_index:v1';
    private const CACHE_KEY_BITSETS = 'nn:diag_bitsets:v1';

    // Набор кодов, для которых считаем, что основной источник логики — meta_criteria
    private const META_COVERAGE_REGEX = [
        '/^F21(\.|$)/u',
        '/^F22(\.|$)/u',
        '/^F23\./u',
        '/^F24(\.|$)/u',
        '/^F25\./u',
        '/^F30\./u',
        '/^F31\./u',
        '/^F32\./u',
        '/^F33\./u',
        '/^F00\./u',
        '/^F01\./u',
        '/^F03$/u',
        '/^G30\./u',
        '/^F60\./u'
    ];

    // Негаторы (рус/eng) — (зарезервировано, сейчас основной детектор в isNegatedAround)
    private const NEGATORS = [
        'не', 'без', 'отрицает', 'отрицает наличие', 'данных за', 'исключает',
        'no', 'not', 'denies', 'without', 'free of'
    ];

    private const PD_CLUSTERS = [
        'A' => ['F60.0','F60.1','F21'],                         // параноидное, шизоидное, (добавлен F21 как близкий)
        'B' => ['F60.2','F60.3','F60.4','F60.8'],               // диссоциальное, ЭУРЛ, истерическое, нарциссическое
        'C' => ['F60.5','F60.6','F60.7'],                       // ананкастное, тревожное(избегающее), зависимое
    ];

    /** Главная точка входа */
    public function process(string $text): array
    {
        $t0 = microtime(true);
        $warnings = [];

        // 0) Валидация длины
        try {
            $sentCount = $this->countSentencesRobust($text, 'ru_RU');
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'INTL_EXTENSION_REQUIRED') {
                return [
                    'error'   => 'INTL_EXTENSION_REQUIRED',
                    'message' => 'Для анализа требуется PHP ext-intl (IntlBreakIterator). Установите и включите ext-intl.',
                ];
            }
            throw $e;
        }

        $minSentences = (int) config('llm.ndx.min_sentences', 15);
        if ($sentCount < $minSentences) {
            return [
                'error'              => 'ANAMNESIS_TOO_SHORT',
                'message'            => 'Анамнез слишком короткий: минимум 15 предложений.',
                'sentences_detected' => $sentCount,
                'min_sentences'      => $minSentences,
                'text'               => $text
            ];
        }

        // 1) Индексы/карты (кэш)
        $tIndex = microtime(true);
        $index   = $this->getSymptomIndex();
        $diagMap = $this->getDiagnosisMap();

        if (empty($index['aliases']) || empty($index['symptom_map'])) {
            return ['error' => 'SYMPTOM_INDEX_EMPTY', 'message' => 'Индекс симптомов пуст (перегрейте кэш).'];
        }
        if (empty($diagMap)) {
            return ['error' => 'DIAG_MAP_EMPTY', 'message' => 'Карта диагнозов пуста (перегрейте кэш сидера диагнозов).'];
        }

        // 2) Словарный матч
        $tMatch = microtime(true);
        [$rawItems, $foundPositiveIds] = $this->matchByIndex($text, $index);

        // 2.1) Флаги эпизода
        $episodeFlags = $this->detectEpisodeMeta($text);

        // 3) Условный LLM
        $tLLM = microtime(true);
        $usedLLM = false;

        if ($this->needsLLM(count($foundPositiveIds), count($rawItems))) {
            $usedLLM = true;
            try {
                $candidateIds = $this->narrowCandidates($rawItems, $index, self::MAX_PROMPT_SYMPTOMS);
                $candidateSymptoms = $this->loadSymptoms()->whereIn('id', $candidateIds)->values();

                if ($candidateSymptoms->isEmpty()) {
                    // Фолбэк: берём просто первые N симптомов из БД (с алиасами)
                    $fallbackN = (int) config('llm.ndx.fallback_prompt_symptom_limit', 120);
                    $candidateSymptoms = $this->loadSymptoms()->take($fallbackN)->values();
                }

                if ($candidateSymptoms->isNotEmpty()) {
                    $symptomList = $this->buildSymptomListForPrompt($candidateSymptoms);
                    $llmItems    = $this->askLLM($text, $symptomList);

                    if (is_array($llmItems)) {
                        $validIds = array_flip($candidateSymptoms->pluck('id')->all());
                        foreach ($llmItems as $row) {
                            if (!is_array($row) || !isset($row['symptom_id'])) continue;
                            $sid = (int) $row['symptom_id'];
                            if (!isset($validIds[$sid])) continue;

                            $neg = (bool)($row['negated'] ?? false);
                            $rawExisting = $rawItems[$sid] ?? null;
                            $rawItems[$sid] = [
                                'symptom_id' => $sid,
                                'title'      => $row['title'] ?? ($index['symptom_map'][$sid]['title'] ?? null),
                                'sentence'   => $row['sentence'] ?? ($rawExisting['sentence'] ?? null),
                                'negated'    => $neg || (bool)($rawExisting['negated'] ?? false),
                                'confidence' => max($rawExisting['confidence'] ?? 0.5, 0.6),
                            ];
                        }
                        // Пересчёт позитивных ID после LLM
                        $foundPositiveIds = collect($rawItems)
                            ->filter(fn($r) => empty($r['negated']))
                            ->keys()
                            ->values()
                            ->all();
                    } else {
                        $warnings[] = 'LLM_RETURNED_NULL';
                    }
                } else {
                    $warnings[] = 'LLM_NO_CANDIDATES';
                }
            } catch (\Throwable $e) {
                $warnings[] = 'LLM_ERROR: ' . substr($e->getMessage(), 0, 200);
            }
        }

        // 4) Скоринг
        $tScore = microtime(true);
        $scored = $this->scoreFast($foundPositiveIds, $diagMap);

        // 5) Пост-фильтр / META
        $tPost = microtime(true);
        $clusters = $this->buildClustersAdvanced($foundPositiveIds);
        $clusters['flags'] = $episodeFlags;

        $result = $this->postFilter($scored, $clusters);
        $result['meta']['using_llm']  = $usedLLM;
        $result['meta']['warnings']   = $warnings;
        $result['meta']['timings_ms'] = [
            'index' => (int) round(1000 * ($tMatch - $tIndex)),
            'match' => (int) round(1000 * ($tLLM  - $tMatch)),
            'llm'   => (int) round(1000 * ($tScore - $tLLM)),
            'score' => (int) round(1000 * ($tPost  - $tScore)),
            'post'  => (int) round(1000 * (microtime(true) - $tPost)),
            'total' => (int) round(1000 * (microtime(true) - $t0)),
        ];

        // 6) matched symptoms (для UI/объяснимости)
        $allSymptoms = $this->loadSymptoms();
        $matchedSymptoms = collect($foundPositiveIds)->map(function ($sid) use ($allSymptoms, $rawItems) {
            $s = $allSymptoms->firstWhere('id', $sid);
            $r = $rawItems[$sid] ?? [];
            return [
                'id'       => $sid,
                'title'    => $s?->title,
                'sentence' => $r['sentence'] ?? null,
                'negated'  => (bool)($r['negated'] ?? false),
            ];
        })->values();

        return [
            'text'               => $text,
            'raw_symptoms'       => collect($rawItems)->values(),
            'matched_symptoms'   => $matchedSymptoms,
            'scored'             => $result['ranked'],
            'primary_diagnosis'  => $result['primary'],
            'differential'       => $result['diff'],
            'comorbid'           => $result['comorbid'],
            'prompt_meta'        => $result['meta'],
        ];
    }

    // ===================== Индексы/кэш =====================

    private function askLLM(string $originalText, string $symptomList)
    {
        $systemPrompt = <<<PROMPT
Ты — опытный клинический психиатр. Проанализируй анамнез и выбери ВСЕ симптомы из списка ниже, которые явно присутствуют в тексте.
Правила:
- Используй ТОЛЬКО симптомы из списка (по ID). Новые не придумывать.
- Если симптом в тексте явно ОТРИЦАЕТСЯ ("не", "отрицает", "без", "данных за"), укажи "negated": true.
- Один симптом указывай максимум один раз. Верни краткое исходное предложение в "sentence", если возможно.
- Верни ТОЛЬКО JSON строго по схеме: {"items":[{"symptom_id":int,"title":string,"sentence":string,"negated":boolean}]}. Никакого текста вне JSON.

Список симптомов (ID. Название; aliases: варианты):
{$symptomList}
PROMPT;

        $provider = config('llm.provider', 'ollama');

        try {
            if ($provider === 'ollama') {
                $responseJson = $this->ollamaChatJson($systemPrompt, $originalText);
            } else {
                $responseJson = $this->openaiChatJson($systemPrompt, $originalText);
            }

            if (!is_string($responseJson) || $responseJson === '') {
                return null;
            }

            $data = $this->decodeJsonSafe($responseJson);
            // Если декод не прошёл, попробуем ещё два упрощённых кейса
            if (!is_array($data)) {
                // Кейс: ответ — массив items без обёртки {"items": ...}
                $try = json_decode($responseJson, true);
                if (is_array($try) && isset($try[0]['symptom_id'])) {
                    $data = ['items' => $try];
                }
            }

            // Кейс: модель вернула ключ items строкой — декодируем второй раз
            if (is_array($data) && isset($data['items']) && is_string($data['items'])) {
                $maybe = json_decode($data['items'], true);
                if (is_array($maybe)) {
                    $data['items'] = $maybe;
                }
            }

            // На случай, если модель добавила лишний текст вокруг JSON — выдёрнем последнюю JSON-скобу.
            if (!is_array($data) && preg_match('/\{.*\}\s*$/s', $responseJson, $m)) {
                $data = $this->decodeJsonSafe($m[0]);
            }

            if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
                return null;
            }

            $out = [];
            foreach ($data['items'] as $row) {
                if (!is_array($row) || !isset($row['symptom_id'])) continue;
                $out[] = [
                    'symptom_id' => (int)$row['symptom_id'],
                    'title'      => isset($row['title']) ? (string)$row['title'] : null,
                    'sentence'   => isset($row['sentence']) ? (string)$row['sentence'] : null,
                    'negated'    => (bool)($row['negated'] ?? false),
                ];
            }

            return $out ?: [];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Вызов локального Ollama (/api/chat, формат JSON) */
    private function ollamaChatJson(string $systemPrompt, string $userText): ?string
    {
        $payload = [
            'model'   => config('llm.ollama.model'),
            'stream'  => false,
            'format'  => 'json',
            'options' => [
                'temperature' => config('llm.ollama.temperature', 0.0),
                'num_ctx'     => config('llm.ollama.num_ctx', 4096),
            ],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userText],
            ],
        ];

        $resp = Http::timeout(config('llm.ollama.timeout', 45))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post(rtrim(config('llm.ollama.base_url'), '/').'/api/chat', $payload);

        if (!$resp->successful()) {
            return null;
        }

        // Ответ вида: { "message": { "role": "assistant", "content": " {...json...} " }, ... }
        return data_get($resp->json(), 'message.content');
    }

    /** OpenAI-совместимый путь */
    private function openaiChatJson(string $systemPrompt, string $userText): ?string
    {
        $payload = [
            'model'       => config('llm.openai.model'),
            'temperature' => (float) config('llm.openai.temperature', 0.0),
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userText],
            ],
            // при наличии совместимого API можно добавить response_format => ['type'=>'json_object']
        ];

        $resp = Http::timeout(config('llm.openai.timeout', 45))
            ->withToken(config('llm.openai.api_key'))
            ->post(rtrim(config('llm.openai.base_url'), '/').'/chat/completions', $payload);

        if (!$resp->successful()) {
            return null;
        }

        $content = data_get($resp->json(), 'choices.0.message.content');
        return is_string($content) ? $content : null;
    }

    /** Безопасный JSON-decode (устойчив к экранированным слешам/кавычкам, к кодовым блокам и «двойному JSON») */
    private function decodeJsonSafe(string $s): ?array
    {
        // Снимем возможные ```json ... ``` и BOM/контрольные символы
        $clean = preg_replace('/^\s*```(?:json)?\s*|\s*```\s*$/u', '', $s) ?? $s;
        $clean = preg_replace('/^\xEF\xBB\xBF|\p{C}+/u', '', $clean) ?? $clean;
        $clean = trim($clean);

        // 1) Обычный JSON
        try {
            $decoded = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) return $decoded;
        } catch (\Throwable $e) {
            // no-op
        }

        // 2) Частый кейс: JSON отдан как строка с экранированием (\" ... \", \/ ...)
        // Попробуем: если это валидная JSON-строка, сначала декодируем её в строку, потом ещё раз декодируем как JSON-объект.
        try {
            $first = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
            if (is_string($first)) {
                $second = json_decode($first, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($second)) return $second;
            }
        } catch (\Throwable $e) {
            // no-op
        }

        // 3) Хард-фоллбек: если строка в кавычках — снимем экранирование вручную
        if (preg_match('/^"(.*)"$/s', $clean, $m)) {
            $un = stripcslashes($m[1]); // снимает \" \\ \/
            try {
                $maybe = json_decode($un, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($maybe)) return $maybe;
            } catch (\Throwable $e) {}
        }

        return null;
    }

    private function loadSymptoms(): Collection
    {
        return Cache::remember('nn:symptoms:with_aliases', now()->addMinutes(30), function () {
            return Symptom::with('aliases')->get();
        });
    }

    private function getSymptomIndex(): array
    {
        return Cache::remember(self::CACHE_KEY_INDEX, now()->addMinutes(self::CACHE_TTL_INDEX_MIN), function () {
            $all = $this->loadSymptoms();
            $aliases = [];
            $map = [];
            foreach ($all as $s) {
                $map[$s->id] = ['title' => $s->title, 'aliases' => []];

                $als = [];
                if ($s->relationLoaded('aliases')) {
                    foreach ($s->aliases as $al) {
                        if (!empty($al->alias)) $als[] = $al->alias;
                    }
                }
                if (!empty($s->title)) {
                    $als[] = $s->title;
                }
                $als = array_slice(array_values(array_unique($als)), 0, self::MAX_ALIASES_PER_SYMPTOM);

                foreach ($als as $a) {
                    $aliases[] = [
                        'regex'      => $this->aliasToRegex($a),
                        'symptom_id' => (int)$s->id,
                        'weight'     => max(1, mb_strlen($a)),
                        'raw'        => $a,
                    ];
                }

                $map[$s->id]['aliases'] = $als;
            }
            usort($aliases, fn($x, $y) => $y['weight'] <=> $x['weight']);
            return ['aliases' => $aliases, 'symptom_map' => $map];
        });
    }

    private function getDiagnosisMap(): array
    {
        return Cache::remember(self::CACHE_KEY_BITSETS, now()->addMinutes(self::CACHE_TTL_BITSETS_MIN), function () {
            $rows = Diagnose::with(['requiredSymptoms:id', 'relativeSymptoms:id'])
                ->get(['id', 'code', 'exceptions', 'required_criteria', 'relative_criteria', 'meta_criteria']);
            $out = [];
            foreach ($rows as $d) {
                $out[$d->id] = [
                    'id'       => (int)$d->id,
                    'code'     => $d->code,
                    'exceptions' => (array)($d->exceptions ?? []),
                    'req'      => $d->requiredSymptoms->pluck('id')->map(fn($v) => (int)$v)->values()->all(),
                    'rel'      => $d->relativeSymptoms->pluck('id')->map(fn($v) => (int)$v)->values()->all(),
                    'reqCrit'  => (int)($d->required_criteria ?? 0),
                    'relCrit'  => (int)($d->relative_criteria ?? 0),
                ];
            }
            return $out;
        });
    }

    // ===================== Извлечение симптомов =====================

    private function normalizeText(string $text): string
    {
        $t = mb_strtolower($text, 'UTF-8');
        $t = str_replace(['t°', 'tº'], ' температура ', $t);
        $t = preg_replace('~[\r\n\t]+~u', ' ', $t);
        $t = preg_replace('~[\s]+~u', ' ', $t);
        return trim($t);
    }

    private function aliasToRegex(string $alias): string
    {
        $a = $this->normalizeText($alias);
        if ($a === '') return '';
        $tokens = preg_split('/\s+/u', $a, -1, PREG_SPLIT_NO_EMPTY);

        $parts = [];
        foreach ($tokens as $w) {
            $w = preg_quote($w, '~');
            $w = str_replace('ё', '[её]', $w);
            if (preg_match('/^\p{L}{5,}$/u', $w)) {
                $stem = mb_substr($w, 0, mb_strlen($w, 'UTF-8') - 2, 'UTF-8');
                $parts[] = $stem . '\p{L}*';
            } else {
                $parts[] = $w;
            }
        }
        $pattern = implode('\s+', $parts);
        return '(?:(?<=^)|(?<=[^\p{L}\p{N}]))' . $pattern . '(?:(?=$)|(?=[^\p{L}\p{N}]))';
    }

    private function matchByIndex(string $text, array $index): array
    {
        $aliases = $index['aliases'];
        $raw = [];
        $positive = [];

        $sentencesRaw = $this->splitSentences($text, 'ru_RU');
        if (!$sentencesRaw) { $sentencesRaw = [$text]; }
        $sentences = array_map(fn($s) => $this->normalizeText($s), $sentencesRaw);

        for ($i = 0; $i < count($aliases); $i += self::BATCH_REGEX_SIZE) {
            $batch = array_slice($aliases, $i, self::BATCH_REGEX_SIZE);
            $batchRegexes = array_filter(array_column($batch, 'regex'), fn($r) => $r !== '');
            if (!$batchRegexes) continue;

            $pattern = '~(?:' . implode('|', $batchRegexes) . ')~u';

            foreach ($sentences as $idx => $sent) {
                if ($sent === '') continue;
                if (!preg_match_all($pattern, $sent, $m, PREG_OFFSET_CAPTURE)) continue;

                foreach ($m[0] as $hit) {
                    $hitText = $hit[0];
                    $sid = null; $weight = 1;
                    foreach ($batch as $row) {
                        // проверяем совпадение ровно с одной из alias-масок батча
                        if (preg_match('~^' . $row['regex'] . '$~u', $hitText)) {
                            $sid = (int)$row['symptom_id'];
                            $weight = (int)$row['weight'];
                            break;
                        }
                    }
                    if ($sid === null) continue;

                    $neg = $this->isNegatedAround($sentencesRaw[$idx] ?? $sent, $hitText, self::NEGATION_WINDOW_TOKENS);

                    if (!isset($raw[$sid])) {
                        $raw[$sid] = [
                            'symptom_id' => $sid,
                            'title'      => $index['symptom_map'][$sid]['title'] ?? null,
                            'sentence'   => $sentencesRaw[$idx] ?? $sent,
                            'negated'    => $neg,
                            'confidence' => 0.5,
                        ];
                    }
                    $raw[$sid]['negated']    = $raw[$sid]['negated'] || $neg;
                    $raw[$sid]['confidence'] = max($raw[$sid]['confidence'], min(0.95, 0.4 + $weight/40));
                }
            }
        }

        foreach ($raw as $sid => $row) {
            if (empty($row['negated'])) $positive[] = (int)$sid;
        }

        $this->injectPsychoticHeuristics($raw, $positive, $sentencesRaw);
        $this->injectCognitiveHeuristics($raw, $positive, $sentencesRaw);
        $this->injectSchizotypalHeuristics($raw, $positive, $sentencesRaw); // ← НОВОЕ
        $this->injectAnankasticHeuristics($raw, $positive, $sentencesRaw);

        return [$raw, array_values(array_unique($positive))];
    }

    private function isNegatedAround(string $sentence, string $hit, int $windowTokens): bool
    {
        // унификация
        $s = mb_strtolower(' ' . $sentence . ' ', 'UTF-8');
        $h = mb_strtolower($hit, 'UTF-8');

        $pos = mb_strpos($s, $h, 0, 'UTF-8');
        if ($pos === false) return false;

        $left = trim(mb_substr($s, 0, max(0, $pos), 'UTF-8'));
        $ltoks = preg_split('~\s+~u', $left, -1, PREG_SPLIT_NO_EMPTY);
        $lwnd  = implode(' ', array_slice($ltoks, max(0, count($ltoks) - $windowTokens)));

        $right = trim(mb_substr($s, $pos + mb_strlen($h, 'UTF-8'), null, 'UTF-8'));
        $rtoks = preg_split('~\s+~u', $right, -1, PREG_SPLIT_NO_EMPTY);
        $rwnd  = implode(' ', array_slice($rtoks, 0, $windowTokens));

        // 1) явные левые негаторы
        $negLeftNeedles = [
            ' не ', ' нет ', ' без ', ' отрицает ', ' исключает ',
            ' данных за ', ' отсутствует ', ' не было ',
        ];
        foreach ($negLeftNeedles as $n) {
            if (mb_strpos(' '.$lwnd.' ', $n) !== false) return true;
        }

        // 2) правые формы отрицания (морфология и конструкции)
        $negRightPatterns = [
            '/\bне\s+(отмечается|выявлено|обнаружено|наблюдается)\b/u',
            '/\bотсутств(ует|уют)\b/u',
            // ВАЖНО: «галлюцинации ОТРИЦАЕТ», «бредовые идеи ОТРИЦАЛ» и т.п.
            '/\bотриц\w*\b/u', // отрицает, отрицал(а/и/ось), отрицают, отрицательно заявляет (редко)
            // универсальные конструкции типа «не сообщается/не отмечал(а)»
            '/\bне\s+(сообща\w*|отмеча\w*|наблюда\w*|замеча\w*|выявля\w*)\b/u',
        ];
        foreach ($negRightPatterns as $re) {
            if (preg_match($re, $rwnd)) return true;
        }

        // 3) страховка: если в пределах окна (слева или справа) есть «отриц*», считаем negated
        if (preg_match('/\bотриц\w*\b/u', $lwnd.' '.$rwnd)) return true;

        return false;
    }


    // ===================== Условный LLM =====================

    private function needsLLM(int $positiveCount, int $rawCount): bool
    {
        if (config('llm.ndx.always_on', false)) {
            return true;
        }
        $minPos = (int) config('llm.ndx.min_positive_for_no_llm', 5);
        $maxRaw = (int) config('llm.ndx.max_raw_before_llm', 140);
        return ($positiveCount < $minPos) || ($rawCount > $maxRaw);
    }

    private function narrowCandidates(array $rawItemsById, array $index, int $limit): array
    {
        $rows = [];
        foreach ($rawItemsById as $sid => $row) {
            $rows[] = ['id' => (int)$sid, 'confidence' => (float)($row['confidence'] ?? 0.5)];
        }
        usort($rows, fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        return array_column(array_slice($rows, 0, $limit), 'id');
    }

    private function buildSymptomListForPrompt(Collection $symptoms): string
    {
        $lines = [];
        foreach ($symptoms as $s) {
            $aliases = [];
            if ($s->relationLoaded('aliases')) {
                foreach ($s->aliases as $al) if (!empty($al->alias)) $aliases[] = $al->alias;
            }
            $aliases = array_slice(array_values(array_unique($aliases)), 0, self::MAX_ALIASES_PER_SYMPTOM);
            $lines[] = $aliases
                ? sprintf('%d. %s; aliases: %s', $s->id, $s->title, implode(', ', $aliases))
                : sprintf('%d. %s', $s->id, $s->title);
        }
        return implode("\n", $lines);
    }

    // ===================== Скоринг =====================

    private function scoreFast(array $foundIds, array $diagMap): Collection
    {
        $foundSet = array_fill_keys($foundIds, true);
        $diagnoses = Diagnose::whereIn('id', array_keys($diagMap))->get()->keyBy('id');

        return collect($diagMap)->map(function ($row) use ($foundSet, $diagnoses) {
            $reqHit = 0;
            foreach ($row['req'] as $sid) if (isset($foundSet[$sid])) $reqHit++;
            $relHit = 0;
            foreach ($row['rel'] as $sid) if (isset($foundSet[$sid])) $relHit++;

            $reqCrit = max(0, (int)$row['reqCrit']);
            $relCrit = max(0, (int)$row['relCrit']);

            $reqScore = $reqCrit ? min($reqHit, $reqCrit) / $reqCrit : 0.0;
            $relScore = $relCrit ? min($relHit, $relCrit) / $relCrit : 0.0;
            $total = ($reqScore * 2 + $relScore) / 3.0;

            return [
                'diagnosis'        => $diagnoses[$row['id']] ?? null,
                'requiredMatched'  => $reqHit,
                'relativeMatched'  => $relHit,
                'requiredCount'    => $reqHit,
                'relativeCount'    => $relHit,
                'totalScore'       => round($total, 4),
            ];
        })->values();
    }

    // ===================== META-движок (HYBRID) =====================

    private function coveredByMeta(string $code): bool
    {
        foreach (self::META_COVERAGE_REGEX as $re) {
            if (preg_match($re, $code)) return true;
        }
        return false;
    }

    /** Приводим meta_criteria к массиву */
    private function readMeta(?Diagnose $d): array
    {
        if (!$d) return [];
        $raw = $d->meta_criteria ?? null;
        if (is_array($raw)) return $raw;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }
        return [];
    }

    /** Проверка жёстких условий диагноза */
    private function metaAllows(?Diagnose $d, array $clusters): bool
    {
        $meta = $this->readMeta($d);
        if (!$meta) return true;

        $flags = $this->buildMetaSignalSet($clusters);

        // needs_all
        if (!empty($meta['needs_all']) && is_array($meta['needs_all'])) {
            foreach ($meta['needs_all'] as $k) {
                if (empty($flags[$k])) return false;
            }
        }
        // needs_any
        if (!empty($meta['needs_any']) && is_array($meta['needs_any'])) {
            $ok = false;
            foreach ($meta['needs_any'] as $k) {
                if (!empty($flags[$k])) { $ok = true; break; }
            }
            if (!$ok) return false;
        }
        // forbid_any
        if (!empty($meta['forbid_any']) && is_array($meta['forbid_any'])) {
            foreach ($meta['forbid_any'] as $k) {
                if (!empty($flags[$k])) return false;
            }
        }
        // forbid_all (если ВСЕ перечисленные есть — запрещаем)
        if (!empty($meta['forbid_all']) && is_array($meta['forbid_all'])) {
            $allPresent = true;
            foreach ($meta['forbid_all'] as $k) {
                if (empty($flags[$k])) { $allPresent = false; break; }
            }
            if ($allPresent) return false;
        }

        return true;
    }

    /** Мягкие эффекты meta: boosts/penalties/caps */
    private function metaAdjustScore(?Diagnose $d, array $clusters, float $score): float
    {
        $meta = $this->readMeta($d);
        if (!$meta) return $score;
        $flags = $this->buildMetaSignalSet($clusters);

        $applyWhen = function ($when) use ($flags): bool {
            if (!$when) return true;
            if (is_string($when)) return !empty($flags[$when]);
            if (is_array($when)) {
                if (isset($when['all']) && is_array($when['all'])) {
                    foreach ($when['all'] as $k) if (empty($flags[$k])) return false;
                }
                if (isset($when['any']) && is_array($when['any'])) {
                    $ok = false;
                    foreach ($when['any'] as $k) if (!empty($flags[$k])) { $ok = true; break; }
                    if (!$ok) return false;
                }
                return true;
            }
            return true;
        };

        // boosts / penalties
        foreach (['boosts','penalties','score_boosts'] as $key) {
            if (empty($meta[$key]) || !is_array($meta[$key])) continue;
            foreach ($meta[$key] as $rule) {
                $delta = (float)($rule['delta'] ?? 0.0);
                if ($key === 'penalties') $delta = -abs($delta);
                if (!$delta) continue;
                if ($applyWhen($rule['when'] ?? null)) {
                    $score = max(0.0, min(1.0, $score + $delta));
                }
            }
        }

        // caps
        if (!empty($meta['caps']) && is_array($meta['caps'])) {
            foreach ($meta['caps'] as $cap) {
                $max = isset($cap['max']) ? (float)$cap['max'] : null;
                if ($max === null) continue;
                if ($applyWhen($cap['when'] ?? null)) {
                    $score = min($score, $max);
                }
            }
        }

        return $score;
    }

    /** Приводим признаки в единый словарь флагов для meta */
    private function buildMetaSignalSet(array $clusters): array
    {
        $C = $clusters['counts'] ?? [];
        $F = $clusters['flags'] ?? [];

        $hasPsych    = (bool)($clusters['hasPsychotic'] ?? false);
        $maniaHits   = (int)($clusters['maniaHits'] ?? 0);
        $deprHits    = (int)($clusters['deprHits'] ?? 0);
        $bothPoles   = ($maniaHits >= 2 && $deprHits >= 2);
        $moodAny     = ($maniaHits >= 2) || ($deprHits >= 3);

        // базовые флаги из detectEpisodeMeta
        $base = [
            'acute_onset'        => (bool)($F['acute_onset'] ?? false),
            'duration_short'     => (bool)($F['duration_short'] ?? false),
            'duration_long'      => (bool)($F['duration_long'] ?? false),
            'stressor'           => (bool)($F['stressor'] ?? false),
            'no_past_psychosis'  => (bool)($F['no_past_psychosis'] ?? false),
            'polymorph'          => (bool)($F['polymorph'] ?? false),
            'disorganization'    => (bool)($F['disorganization'] ?? false),
            'etiology_substance' => (bool)($F['etiology_substance'] ?? false),
            'insidious_onset'    => (bool)($F['insidious_onset'] ?? false),
            'cooccurrence'       => (bool)($F['cooccurrence'] ?? false),
            'two_weeks'          => (bool)($F['two_weeks'] ?? false),
            'bizarre_delusion'   => (bool)($F['bizarre_delusion'] ?? false),
            'past_mania'         => (bool)($F['past_mania'] ?? false),
            'no_past_mania'      => (bool)($F['no_past_mania'] ?? false),
        ];

        // сводные по аффекту/психотике
        $der = [
            'has_psychosis'            => $hasPsych,
            'mood_manic_major'         => $maniaHits >= 2,
            'mood_depressive_major'    => $deprHits >= 3,
            'mood_any'                 => $moodAny,
            'mood_both_poles'          => $bothPoles,
            'cooccur_psych_mood'       => ($hasPsych && $moodAny && !empty($base['cooccurrence'])),
            'cooccur_psych_mood_2w'    => ($hasPsych && $moodAny && !empty($base['cooccurrence']) && !empty($base['two_weeks'])),
        ];

        return array_merge($base, $der, [
            'schizotypal_signature' => ($C['schizotypal_signature'] ?? 0) >= 3,
            'paranoid'              => ($C['paranoid'] ?? 0) >= 1,
            'acute_psychosis'       => ($C['acute_psychosis'] ?? 0) >= 2,
            'cognitive'             => ($C['cognitive'] ?? 0) >= 2,
            'dementia'              => ($C['dementia'] ?? 0) >= 3,
        ]);
    }

    // ===================== Пост-фильтр/ранжирование (HYBRID) =====================

    private function postFilter(Collection $scored, array $clusters = []): array
    {
        $C = $clusters['counts'] ?? [];
        $hasPsych   = (bool)($clusters['hasPsychotic'] ?? false);
        $maniaHits  = (int)($clusters['maniaHits']    ?? 0);
        $deprHits   = (int)($clusters['deprHits']     ?? 0);

        $bothPoles       = ($maniaHits >= 2 && $deprHits >= 2);
        $bothPolesPsych  = ($hasPsych && $bothPoles);
        $psychOnlyDep    = ($hasPsych && $deprHits >= 3 && $maniaHits < 2);
        $psychOnlyMan    = ($hasPsych && $maniaHits >= 3 && $deprHits < 2);

        $dementiaStrong = (($C['dementia'] ?? 0) >= 3)
            || ((($C['cognitive'] ?? 0) >= 2) && ( ($C['orientation'] ?? 0) >= 1 || ($C['speech_thought'] ?? 0) >= 1 ));

        $disorgStrong = (($C['disorganization'] ?? 0) + ($C['speech_thought'] ?? 0) >= 3)
            && (($C['negative'] ?? 0) >= 1);

        $strong = [
            'ocd'         => ($C['ocd'] ?? 0) >= 2,
            'anxiety'     => ($C['anxiety'] ?? 0) >= 3 && !$hasPsych,
            'ptsd'        => ($C['ptsd'] ?? 0) >= 2,
            'dissociation'=> ($C['dissociation'] ?? 0) >= 2,
            'somatoform'  => ($C['somatoform'] ?? 0) >= 2,
            'sleep'       => ($C['sleep'] ?? 0) >= 3,
            'eating'      => ($C['eating'] ?? 0) >= 2,
            'adhd'        => ($C['adhd'] ?? 0) >= 2,
            'autism'      => ($C['autism'] ?? 0) >= 2,
            'substance'   => ($C['substance'] ?? 0) >= 2,
        ];

        $catatoniaStrong = ($C['catatonia'] ?? 0) >= 2;
        $paranoidStrong  = ($C['paranoid']  ?? 0) >= 2;
        $residualStrong  = ($C['residual']  ?? 0) >= 3 && ($C['psychotic'] ?? 0) == 0;
        $simpleStrong    = ($C['simple']    ?? 0) >= 3 && ($C['psychotic'] ?? 0) == 0;

        $schizotypalStrong        = ($C['schizotypal_signature'] ?? 0) >= 3 && ($C['psychotic'] ?? 0) == 0;
        $persistentDelusionStrong = ($C['persistent_delusion'] ?? 0) >= 2 && ($C['psychotic'] ?? 0) <= 1;
        $acutePsychosisStrong     = ($C['acute_psychosis'] ?? 0) >= 2;
        $inducedDelusionStrong    = ($C['induced_delusion'] ?? 0) >= 1;

        // 1) фильтрация семейств
        $filtered = $scored->filter(function ($item) use (
            $C, $clusters,
            $hasPsych, $bothPoles, $bothPolesPsych, $psychOnlyDep, $psychOnlyMan,
            $strong, $dementiaStrong, $disorgStrong,
            $catatoniaStrong, $paranoidStrong, $residualStrong, $simpleStrong,
            $schizotypalStrong, $persistentDelusionStrong, $acutePsychosisStrong, $inducedDelusionStrong, $maniaHits
        ) {
            $d = $item['diagnosis']; if (!$d || empty($d->code)) return false;
            $code = $d->code;

            // Общие запреты
            if (preg_match('/^F66(\.|$)/u', $code)) return false;
            if (preg_match('/^F31\./u', $code)) {
                $F = $clusters['flags'] ?? [];
                $explicitNoHistory = !empty($F['no_past_mania']);
                $hasHistory        = !empty($F['past_mania']);
                // НЕТ анамнеза мании/гипомании И нет явных маникальных хитов сейчас → отсекаем
                if ($explicitNoHistory || (!$hasHistory && $maniaHits < 2)) {
                    return false;
                }
            }

            // Если диагноз покрыт meta — проверяем только metaAllows на основе текущих clusters
            if ($this->coveredByMeta($code)) {
                // Если meta реально описана — полагаемся на неё.
                if (!empty($this->readMeta($d))) {
                    return $this->metaAllows($d, $clusters);
                }
                // Иначе не режем на этом шаге — пусть решают общие эвристики/скоринг.
                // (так мы избегаем «немого» допуска F23 без острых признаков)
            }

            // ---- Старая логика для НЕ покрытых meta ----

            if ($dementiaStrong && !$hasPsych) {
                return preg_match('/^F00\./u', $code)
                    || preg_match('/^F01\./u', $code)
                    || $code === 'F03'
                    || preg_match('/^G30\./u', $code);
            }

            if ($hasPsych) {
                if ($bothPolesPsych) {
                    if (str_starts_with($code, 'F25.')) return true;
                    if (preg_match('/^F1[0-9]\.5/u', $code)) return true;
                    return false;
                }
                if ($psychOnlyDep) {
                    return (preg_match('/^F32\.3/u', $code) || preg_match('/^F31\.5/u', $code) || str_starts_with($code, 'F25.'));
                }
                if ($psychOnlyMan) {
                    return (preg_match('/^F30\.2/u', $code) || preg_match('/^F31\.2/u', $code) || str_starts_with($code, 'F25.'));
                }
                if (preg_match('/^F2[0-9]\./u', $code)) return true;
                if (preg_match('/^F1[0-9]\.[45]/u', $code)) return true;
                return false;
            }

            if ($strong['ocd'])          return preg_match('/^F42(\.|$)/u', $code);
            if ($strong['anxiety'])      return preg_match('/^F4[01]\./u', $code);
            if ($strong['ptsd'])         return preg_match('/^F43(\.|$)/u', $code);
            if ($strong['dissociation']) return preg_match('/^F44(\.|$)/u', $code);
            if ($strong['somatoform'])   return preg_match('/^F45(\.|$)/u', $code);
            if ($strong['sleep'])        return preg_match('/^F51(\.|$)/u', $code);
            if ($strong['eating'])       return preg_match('/^F50(\.|$)/u', $code);
            if ($strong['adhd'])         return preg_match('/^F90(\.|$)/u', $code);
            if ($strong['autism'])       return preg_match('/^F84(\.|$)/u', $code);
            if ($strong['substance'])    return preg_match('/^F1[0-9]\./u', $code);

            if ($catatoniaStrong) {
                return preg_match('/^F20\.2/u', $code)
                    || (preg_match('/^F2[0-9]\./u', $code) && !preg_match('/^F25\./u', $code));
            }
            if ($residualStrong) {
                return preg_match('/^F20\.5/u', $code) || preg_match('/^F20\./u', $code);
            }
            if ($simpleStrong) {
                return preg_match('/^F20\.6/u', $code) || preg_match('/^F20\./u', $code);
            }
            if ($schizotypalStrong) {
                return preg_match('/^F21(\.|$)/u', $code);
            }
            if ($persistentDelusionStrong) {
                return preg_match('/^F22(\.|$)/u', $code);
            }
            if ($acutePsychosisStrong) {
                return preg_match('/^F23\./u', $code) || preg_match('/^F2[0-9]\./u', $code);
            }
            if ($inducedDelusionStrong) {
                return preg_match('/^F24(\.|$)/u', $code) || preg_match('/^F22(\.|$)/u', $code);
            }

            if ($bothPoles) {
                if (preg_match('/^F34\.9$/u', $code)) return false;
                return preg_match('/^F3[0-9]\./u', $code);
            }
            if ($disorgStrong) {
                return preg_match('/^F20\./u', $code);
            }

            return true;
        })->values();

        // 2) бусты
        $boosted = $filtered->map(function ($item) use (
            $C, $clusters,
            $hasPsych, $bothPoles, $bothPolesPsych, $psychOnlyDep, $psychOnlyMan,
            $strong, $dementiaStrong, $disorgStrong,
            $catatoniaStrong, $paranoidStrong, $residualStrong, $simpleStrong,
            $schizotypalStrong, $persistentDelusionStrong, $acutePsychosisStrong, $inducedDelusionStrong, $deprHits
        ) {
            $d = $item['diagnosis']; if (!$d) return $item;
            $code = $d->code ?? '';
            $coveredByRegex = $this->coveredByMeta($code);
            $hasMeta = !empty($this->readMeta($d));
            $covered = $coveredByRegex && $hasMeta; // только если реально есть meta_criteria

            // --- Общие эффекты, которые НЕ завязаны на покрытые коды ---
            if ($dementiaStrong) {
                if (!$covered && (preg_match('/^F00\./u', $code) || preg_match('/^G30\./u', $code))) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.18);
                } elseif (!$covered && (preg_match('/^F01\./u', $code) || $code === 'F03')) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.12);
                } elseif (preg_match('/^F34\./u', $code)) {
                    $item['totalScore'] = max(0.0, $item['totalScore'] - 0.10);
                }
            }

            if (!$covered) {
                if ($bothPolesPsych && str_starts_with($code, 'F25.')) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.15);
                }
                if (!$hasPsych && $bothPoles && preg_match('/^F31\./u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.12);
                }
                if ($psychOnlyDep && (preg_match('/^F32\.3/u', $code) || preg_match('/^F31\.5/u', $code))) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.10);
                }
                if ($psychOnlyMan && (preg_match('/^F30\.2/u', $code) || preg_match('/^F31\.2/u', $code))) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.10);
                }
                if ($disorgStrong && preg_match('/^F20\.1/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.10);
                }
                if (($C['ocd'] ?? 0) >= 2 && preg_match('/^F42(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.10);
                }
                if (($C['anxiety'] ?? 0) >= 3 && !$hasPsych && preg_match('/^F4[01]\./u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.08);
                }
                if (($C['ptsd'] ?? 0) >= 2 && preg_match('/^F43(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.08);
                }
                if (($C['dissociation'] ?? 0) >= 2 && preg_match('/^F44(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.10);
                }
                if (($C['somatoform'] ?? 0) >= 2 && preg_match('/^F45(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.07);
                }
                if (($C['sleep'] ?? 0) >= 3 && preg_match('/^F51(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.06);
                }
                if (($C['eating'] ?? 0) >= 2 && preg_match('/^F50(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.08);
                }
                if (($C['substance'] ?? 0) >= 2 && preg_match('/^F1[0-9]\./u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.06);
                }

                if ($catatoniaStrong && preg_match('/^F20\.2/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.12);
                }
                if ($residualStrong && preg_match('/^F20\.5/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.10);
                }
                if ($simpleStrong && preg_match('/^F20\.6/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.10);
                }
                if ($paranoidStrong && preg_match('/^F20\.0/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.08);
                }
                if ($schizotypalStrong && preg_match('/^F21(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.08);
                }
                if ($persistentDelusionStrong && preg_match('/^F22(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.10);
                }
                if ($acutePsychosisStrong && preg_match('/^F23\./u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.08);
                }
                if ($inducedDelusionStrong && preg_match('/^F24(\.|$)/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.08);
                }
            }

            // ЭТИОЛОГИЯ ПАВ: если НЕТ указаний на ПАВ → штрафуем F1x.*
            $F = $clusters['flags'] ?? [];
            $etiolSubst = (bool)($F['etiology_substance'] ?? false);
            if (!$etiolSubst && preg_match('/^F1[0-9]\./u', $code)) {
                $item['totalScore'] = max(0.0, $item['totalScore'] - 0.50);
            }
            if (!$etiolSubst && preg_match('/^F1[0-9]\.(4|5)$/u', $code)) {
                $item['totalScore'] = max(0.0, $item['totalScore'] - 0.20);
            }

            // META мягкие эффекты
            $item['totalScore'] = $this->metaAdjustScore($d, $clusters, (float)$item['totalScore']);

            // F23.* острый паттерн — только если не покрыт meta
            if (!$covered) {
                $episodeAcute   = (bool)($F['acute_onset'] ?? false);
                $episodeShort   = (bool)($F['duration_short'] ?? false);
                $episodeLong    = (bool)($F['duration_long'] ?? false);
                $episodeStress  = (bool)($F['stressor'] ?? false);
                $poly           = (bool)($F['polymorph'] ?? false);
                $disorgTxt      = (bool)($F['disorganization'] ?? false);
                $acutePattern = ($episodeAcute && ($episodeShort || $poly) && ($episodeStress || $poly || $disorgTxt) && !$episodeLong);

                if ($acutePattern && $poly && !preg_match('/^F2[0-9]\./u', $code) && preg_match('/^F23\.0/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.15);
                }
                if ($acutePattern && (($C['psychotic'] ?? 0) >= 1) && (($C['disorganization'] ?? 0) >= 1 || $disorgTxt) && preg_match('/^F23\.1/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.15);
                }
                if ($acutePattern && (($C['psychotic'] ?? 0) >= 1) && !$episodeLong && preg_match('/^F23\.2/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.12);
                }
                if ($acutePattern && (($C['paranoid'] ?? 0) >= 1) && preg_match('/^F23\.3/u', $code)) {
                    $item['totalScore'] = min(1.0, $item['totalScore'] + 0.12);
                }
                if ($acutePattern && preg_match('/^F20\./u', $code) && !$episodeLong) {
                    $item['totalScore'] = max(0.0, $item['totalScore'] - 0.12);
                }
                // ----- Антишкала для F23 при хроническом/инсидиозном течении без психоза -----
                if (preg_match('/^F23\./u', $code)) {
                    $noAcute = !$episodeAcute;
                    $noPoly  = !$poly;
                    $noPsych = !$hasPsych;
                    $chronicish = (bool)(($F['duration_long'] ?? false) || ($F['insidious_onset'] ?? false));

                    if ($chronicish && $noAcute && $noPoly && $noPsych) {
                        // Сильно режем F23, чтобы не перетягивал хронические F21/F20.* кейсы
                        $item['totalScore'] = max(0.0, $item['totalScore'] - 0.30);
                        // Ещё и «потолок» на всякий
                        $item['totalScore'] = min($item['totalScore'], 0.40);
                    }
                }
                if (preg_match('/^F21(\.|$)/u', $code)) {
                    $chronicish = (bool)(($F['duration_long'] ?? false) || ($F['insidious_onset'] ?? false));
                    $noAcute    = !(bool)($F['acute_onset'] ?? false);
                    $noPsych    = !$hasPsych;
                    $hasSchizotypalSig = (($C['schizotypal_signature'] ?? 0) >= 3);

                    if ($chronicish && $noAcute && $noPsych && $hasSchizotypalSig) {
                        $item['totalScore'] = min(1.0, $item['totalScore'] + 0.22);
                        // Немного «страховочной» поддержки, если нет жёстких критериев
                        if ($item['requiredCount'] === 0 && $item['relativeCount'] > 0) {
                            $item['totalScore'] = min(1.0, $item['totalScore'] + 0.06);
                        }
                    }
                }
                if (preg_match('/^F23\./u', $code)) {
                    $noAcutePattern = !( ($F['acute_onset'] ?? false) || ($F['polymorph'] ?? false) || ($F['stressor'] ?? false) );
                    if ($noAcutePattern) {
                        $item['totalScore'] = max(0.0, $item['totalScore'] - 0.10);
                    }
                }
            }

            if (!$hasPsych && $deprHits >= 3 && preg_match('/^F32\.(1|2|3)/u', $code)) {
                $item['totalScore'] = min(1.0, $item['totalScore'] + 0.12);
            }

            return $item;
        });

        $mix = $this->detectF61Mix($boosted, $clusters);
        if ($mix['ok']) {
            $boosted = $this->applyF61MixBoost($boosted, $mix);
        }

        // 3) Порог/критерии/ранжирование
        $ranked = $boosted->filter(function ($item) {
            $d = $item['diagnosis']; if (!$d) return false;
            $hardOk = $item['requiredCount'] >= (int)($d->required_criteria ?? 0)
                && $item['relativeCount'] >= (int)($d->relative_criteria ?? 0);
            $softOk = $item['totalScore'] >= self::MIN_TOTAL_SCORE;
            return $hardOk || $softOk;
        })->sort(function ($a,$b) {
            return ($b['totalScore'] <=> $a['totalScore'])
                ?: (($b['requiredCount'] + $b['relativeCount']) <=> ($a['requiredCount'] + $a['relativeCount']))
                    ?: ($b['requiredCount'] <=> $a['requiredCount']);
        })->values();

        $primary = optional($ranked->first())['diagnosis'] ?? null;
        $primaryExceptions = $primary ? (array)($primary->exceptions ?? []) : [];

        $diff = $ranked->pluck('diagnosis')->filter(function ($d) use ($primary, $primaryExceptions) {
            if (!$primary) return false;
            $dExceptions = (array)($d->exceptions ?? []);
            return $d->id !== $primary->id
                && !in_array($d->code, $primaryExceptions, true)
                && !in_array($primary->code, $dExceptions, true);
        })->values();

        $comorbid = $ranked->pluck('diagnosis')->filter(function ($d) use ($primary, $diff, $primaryExceptions) {
            if (!$primary) return false;
            $dExceptions = (array)($d->exceptions ?? []);
            return $d->id !== $primary->id
                && !$diff->contains('id', $d->id)
                && !in_array($d->code, $primaryExceptions, true)
                && !in_array($primary->code, $dExceptions, true);
        })->values();

        return [
            'ranked'  => $ranked,
            'primary' => $primary,
            'diff'    => $diff,
            'comorbid'=> $comorbid,
            'meta'    => [
                'using_llm' => false,
                'clusters'  => [
                    'hasPsychotic' => $hasPsych,
                    'maniaHits'    => $maniaHits,
                    'deprHits'     => $deprHits,
                    'topCounts'    => array_slice(array_filter($C), 0, 8, true),
                ],
                'meta_engine' => ['enabled' => true, 'hybrid' => true, 'covered_by_meta_regex' => self::META_COVERAGE_REGEX],
            ],
        ];
    }

    private function countSentencesRobust(string $text, string $locale = 'ru_RU'): int
    {
        return count($this->splitSentences($text, $locale));
    }

    private function splitSentences(string $text, string $locale = 'ru_RU'): array
    {
        $text = (string) $text;
        if ($text === '') return [];

        $text = preg_replace('/\r\n?/', "\n", $text);
        $text = trim($text);

        $text = preg_replace('/([^\.\!\?…])\n+/u', '$1. ', $text);

        $abbr = [
            'т.д.', 'т.п.', 'и т.д.', 'и т.п.', 'т.е.', 'т.к.', 'т.о.', 'т.н.', 'им.',
            'г.', 'ул.', 'д.', 'стр.', 'см.', 'рис.', '№', 'н.э.', 'н.н.', 'н.г.', 'н.в.', 'т.ж.', 'т.ч.',
            'Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.'
        ];
        foreach ($abbr as $a) {
            $text = str_replace($a, str_replace('.', '∯DOT∯', $a), $text);
        }
        $text = preg_replace('/(\d+)\.(\d+)/u', '$1∯DOT∯$2', $text);

        $parts = preg_split('/(?<=[\.\!\?…])(?:[»”"\'\)\]]+)?\s+/u', $text);
        if ($parts === false) $parts = [$text];

        $parts = array_map(function ($s) {
            $s = str_replace('∯DOT∯', '.', $s);
            return trim($s);
        }, $parts);

        return array_values(array_filter($parts, fn($s) => $s !== ''));
    }

    private function clusterDictionary(): array
    {
        return [
            // БАЗОВЫЕ АФФЕКТИВНЫЕ
            'depressive'   => ['подавлен', 'депрессив', 'потеря интерес', 'ангедони', 'устал', 'утомля', 'нарушение сна', 'бессонниц', 'ранние пробужд', 'чувство вины', 'мысли о смерти', 'суицид'],
            'manic'        => ['повышенное настроение', 'приподнятое настроение', 'гиперактив', 'болтлив', 'ускоренная речь', 'сниженная потребност', 'импульсив', 'рискован', 'повышенная самооцен'],

            // ПСИХОТИЧЕСКИЙ СПЕКТР
            'psychotic'    => ['галлюцинац', 'бред', 'кататони', 'дезорганиз', 'неологизм', 'расстройство мышления', 'параной', 'внедрение мысл', 'комментирующие голоса'],

            // ТРЕВОГА/ОКР/СТРЕСС
            'anxiety'      => ['тревог', 'паник', 'фоб', 'избегание ситуаций', 'беспокойство'],
            'ocd'          => ['обсесс', 'компульс', 'навязчив'],
            'ptsd'         => ['птср', 'стресс', 'травм', 'навязчивые воспоминания', 'флэшбэк', 'кошмар'],

            // ДИСОЦИАЦИЯ/СОМАТОФОРМ
            'dissociation' => ['деперсонализац', 'дереализац', 'диссоциатив', 'фуга', 'транс'],
            'somatoform'   => ['соматоформ', 'конверсион', 'неврологич без', 'психогенн', 'телесные жалобы'],

            // СОН/АППЕТИТ/ВЕС
            'sleep'        => ['бессонниц', 'инсомн', 'гиперсомн', 'сонливост', 'кошмар'],
            'appetite'     => ['аппетит', 'потеря аппетита', 'повышение аппетита', 'обжорство'],
            'weight'       => ['потеря веса', 'снижение массы', 'похуден'],

            // КОГНИТИВНЫЕ
            'cognitive'    => ['память', 'концентраци', 'вниман', 'спутанность', 'дезориентац', 'когнитив', 'исполнительн', 'слабоум', 'деменц'],
            'orientation'  => ['дезориентац', 'ориентац наруш'],
            'consciousness'=> ['спутанное сознание', 'помрачение сознания', 'делири'],

            // НЕГАТИВНАЯ
            'negative'     => ['эмоциональн', 'уплощённый аффект', 'алоги', 'аволи', 'абули', 'социальная изоляц', 'бедность речи', 'анерг', 'апат'],

            // КАТАТОНИЯ/ДЕЗОРГАНИЗАЦИЯ/РЕЧЬ
            'catatonia'    => ['кататони', 'ступор', 'мутизм', 'восковая гибкость', 'негативизм', 'эхолал', 'эхопракс'],
            'disorganization' => ['дезорганизованн', 'нецеленаправленн', 'бессвязн'],
            'speech_thought'  => ['ускоренная речь', 'разорванная речь', 'неологизм', 'бредовое мышление', 'расстройство мышления'],

            // ПАВ
            'substance'    => ['алкогол', 'опиоид', 'каннаб', 'кокаин', 'амфетамин', 'никотин', 'летуч', 'галлюциноген', 'интоксикац', 'абстиненц', 'делирий'],

            // ДОП-ЦЕЛЕВЫЕ СИГНАТУРЫ
            'schizotypal_signature'   => ['магическ', 'эксцентрич', 'странная речь', 'ограниченный аффект', 'социальная изоляц'],
            'delusional_signature'    => ['систематизирован', 'небизарн', 'упорядочен.*поведен', 'логично связан', 'бред преследован', 'бред влияния'],
            'induced_signature'       => ['индуцирова', 'разделяемый бред', 'совместно прожив', 'заимствовал бред', 'исчезает при разобщ'],

            // F23: острый/полиморфный
            'acute_psychosis'         => ['острое', 'полиморф', 'быстро меняющ', 'флюктуирующ', 'эмоциональная лабильн'],

            // НПР/РАЗВИТИЕ
            'adhd'         => ['сдвг', 'дефицит вниман', 'гиперактивность', 'импульсивность детства'],
            'autism'       => ['аутизм', 'расстройство аутист', 'аспергер', 'коммуникац наруш', 'социальн коммуникац'],

            // ПРОЧЕЕ
            'pain'         => ['боль', 'мигрень'],
            'autonomic'    => ['тахикард', 'потоотдел', 'вегетатив'],
            'sexual'       => ['либидо', 'эрект', 'аноргазм'],
            'eating'       => ['анорекси', 'булими', 'переедан', 'пищевая'],

            'dementia'     => ['память', 'забыв', 'дезориентац', 'деменц', 'слабоум', 'нарушение памяти', 'когнитив', 'апракс', 'агноз', 'афаз'],
            'paranoid'     => ['бред преслед', 'бред воздейств', 'следят', 'наблюда', 'прослушк', 'идеи преслед', 'комментирующ.*голос', 'императивн.*голос'],
            'residual'     => ['остаточн', 'резидуальн', 'уплощённый аффект', 'эмоциональн.*притуп', 'бедность речи', 'аутизм.*поведен', 'социальн.*изоляц'],
            'simple'       => ['простая шизофрени', 'постепенн.*обеднение', 'личностн.*измен', 'апат', 'аволи', 'абули', 'утрата инициатив', 'социальн.*дезадапт'],

            'etiology_substance' => [],
        ];
    }

    private function anyContains(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) { if ($n !== '' && str_contains($haystack, $n)) return true; }
        return false;
    }

    private function buildClustersAdvanced(array $foundIds): array
    {
        $dict = $this->clusterDictionary();
        $all  = $this->loadSymptoms()->keyBy('id');

        $counts = array_fill_keys(array_keys($dict), 0);
        $ids    = array_fill_keys(array_keys($dict), []);

        foreach ($foundIds as $id) {
            $t = mb_strtolower((string)($all[$id]->title ?? ''), 'UTF-8');
            foreach ($dict as $cluster => $needles) {
                if ($this->anyContains($t, $needles)) {
                    $counts[$cluster]++; $ids[$cluster][] = $id;
                }
            }
        }

        $deprHits    = $counts['depressive'] ?? 0;
        $maniaHits   = $counts['manic'] ?? 0;
        $hasPsych    = ($counts['psychotic'] ?? 0) > 0;

        return [
            'counts'       => $counts,
            'ids'          => $ids,
            'deprHits'     => $deprHits,
            'maniaHits'    => $maniaHits,
            'hasPsychotic' => $hasPsych,
        ];
    }

    private function injectPsychoticHeuristics(array &$rawItems, array &$foundPositiveIds, array $sentencesRaw): void
    {
        $NEG = [
            ' не ', ' нет ', ' отрицает ', ' без ', ' исключает ', ' отсутствует ',
            ' данных за ', ' не подтвержден', ' не подтверждён', ' исключена ', ' исключены ', ' исключен '
        ];
        $UNCERT = [
            ' кажется ', ' как будто ', ' возможно ', ' вероятно ', ' предположительно ',
            ' со слов ', ' по словам ', ' считает ',
            ' наблюдалось ', ' наблюдается ', ' наблюдался ', ' наблюдалась ',
            ' описано ', ' сообщалось '
        ];
        $REAL = [
            ' по факту ', ' реально ', ' объективно ', ' подтвержден', ' подтверждён ',
            ' была ', ' был ', ' были ', ' на видео ', ' запись ', ' свидетел'
        ];
        $MISLABEL = '/принял[аи]?[^\.]{0,80}за\s+галлюцинац/u';

        $POS_VOICES = [
            '/слыш(ит|ал|ала)[^\.]{0,60}голос/iu',
            '/голос[а-яё]*[^\.]{0,40}(коммент|осужд|приказ)/iu',
            '/комментирующ[а-яё]*\s+голос/iu',
            '/императивн[а-яё]*\s+голос/iu'
        ];
        $POS_DELUSION = [
            '/\bследят\b/iu',
            '/\bслежк[а-яё]*/iu',
            '/\bнаблюда[а-яё]*\s+за\s+(мной|нами|ним|ней|ними)\b/iu',
            '/\bза\s+мной\s+наблюда[а-яё]*/iu',
            '/\bпрослушк[а-яё]*/iu',
            '/\bчерез\s+стен[а-яё]*/iu',
            '/\b(внедрени[ея]|контрол[яю]т?)\s+мыс/iu'
        ];

        $hasPosVoices = 0; $hasPosDelusion = 0;
        $pickedSentVoice = null; $pickedSentDel = null;

        $hasCounter = function (string $s) use ($NEG,$UNCERT,$REAL,$MISLABEL): bool {
            $sl = ' ' . mb_strtolower($s, 'UTF-8') . ' ';
            foreach ($NEG as $n)     if (mb_strpos($sl, $n) !== false) return true;
            foreach ($UNCERT as $u)  if (mb_strpos($sl, $u) !== false) return true;
            foreach ($REAL as $r)    if (mb_strpos($sl, $r) !== false) return true;
            if (preg_match($MISLABEL, $sl)) return true;
            return false;
        };

        foreach ($sentencesRaw as $sr) {
            $srLow = mb_strtolower(' ' . $sr . ' ', 'UTF-8');

            foreach ($POS_VOICES as $re) {
                if (preg_match($re, $srLow)) {
                    if (!$hasCounter($sr)) {
                        $hasPosVoices++; if (!$pickedSentVoice) $pickedSentVoice = $sr;
                    }
                    break;
                }
            }
            foreach ($POS_DELUSION as $re) {
                if (preg_match($re, $srLow)) {
                    if (!$hasCounter($sr)) {
                        $hasPosDelusion++; if (!$pickedSentDel) $pickedSentDel = $sr;
                    }
                    break;
                }
            }
        }

        $need = [];
        if ($hasPosVoices >= 1)  $need[] = ['title' => 'Слуховые галлюцинации', 'sent' => $pickedSentVoice, 'conf' => 0.62];
        if ($hasPosDelusion >= 1)$need[] = ['title' => 'Бред', 'sent' => $pickedSentDel,   'conf' => 0.60];

        if (!$need) return;

        $found = Symptom::whereIn('title', array_column($need,'title'))->get()->keyBy('title');

        foreach ($need as $n) {
            $sym = $found[$n['title']] ?? null;
            if (!$sym) continue;
            if (!in_array($sym->id, $foundPositiveIds, true)) {
                $rawItems[$sym->id] = [
                    'symptom_id' => (int)$sym->id,
                    'title'      => $n['title'],
                    'sentence'   => $n['sent'],
                    'negated'    => false,
                    'confidence' => $n['conf'],
                ];
                $foundPositiveIds[] = (int)$sym->id;
            }
        }
        $foundPositiveIds = array_values(array_unique($foundPositiveIds));
    }

    private function injectCognitiveHeuristics(array &$rawItems, array &$foundPositiveIds, array $sentencesRaw): void
    {
        $want = [
            'Нарушение памяти'                 => ['/повторя(ет|ла).*один и тот же вопрос/u','/забыва(ет|ла)/u','/теря(ет|ла)\s+предмет/u'],
            'Дезориентация'                    => ['/пута(ет|ла)\s+(день|число|дат)/u','/заблудил(ся|ась)/u','/не узна(ет|ла)/u'],
            'Нарушение речи'                   => ['/трудност(и|ь)\s+подбора\s+слов/u','/застревани[ея]\s+при\s+разговоре/u'],
            'Исполнительные функции нарушены'  => ['/трудност(и|ь)\s+планирован/u','/неправильно\s+пользует/u','/путает\s+пульт\s+и\s+телефон/u'],
            'Апатия'                           => ['/пассивн|безразлич/i'],
            'Раздражительность'                => ['/раздражител/i'],
            'Социальная изоляция'              => ['/перестал.*встречаться|избегает\s+общения/u'],
        ];

        $symMap = Symptom::whereIn('title', array_keys($want))->get()->keyBy('title');

        foreach ($want as $title => $res) {
            $hit = false; $picked = null;
            foreach ($sentencesRaw as $sr) {
                $srLow = mb_strtolower($sr, 'UTF-8');
                foreach ((array)$res as $re) {
                    if (preg_match($re, $srLow)) { $hit = true; $picked = $sr; break; }
                }
                if ($hit) break;
            }
            if ($hit && isset($symMap[$title])) {
                $id = (int)$symMap[$title]->id;
                if (!in_array($id, $foundPositiveIds, true)) {
                    $rawItems[$id] = [
                        'symptom_id' => $id,
                        'title'      => $title,
                        'sentence'   => $picked,
                        'negated'    => false,
                        'confidence' => 0.6,
                    ];
                    $foundPositiveIds[] = $id;
                }
            }
        }

        $foundPositiveIds = array_values(array_unique($foundPositiveIds));
    }

    private function injectSchizotypalHeuristics(array &$rawItems, array &$foundPositiveIds, array $sentencesRaw): void
    {
        // Карта: "Заголовок симптома" => [массив regex-маркеров в тексте]
        // ВАЖНО: заголовки должны совпадать с твоими Symptom::title.
        $want = [
            'Социальная изоляция' => [
                '/предпочита(е|ю)т\s+проводить\s+время\s+в\s+одиночеств/iu',
                '/замкнут(ый|ый\s+образ\s+жизни)/iu',
                '/избегает\s+массовых\s+мероприяти/iu',
                '/трудно\s+заводить\s+друзей/iu',
                '/сложност[ьи]\s+в\s+общени/iu',
            ],
            'Магическое мышление' => [
                '/события\s+складываются\s+по\s+особым\s+знакам/iu',
                '/магическ\w*\s+мышлен/iu',
                '/особ(ое|ым)\s+чуть[её]\/?е?м/iu',
                '/способе[нна]\s+предугадывать\s+будущ/iu',
                '/суеверн/iu',
            ],
            'Странная речь' => [
                '/странн\w+\s+фраз/iu',
                '/речь\s+как\s+нелогичн/iu',
                '/непонятн\w+\s+выражени/iu',
            ],
            'Ограниченный аффект' => [
                '/эмоциональн\w*\s+холодн/iu',
                '/отстран[её]нн/iu',
                '/ограниченн\w*\s+аффект/iu',
                '/уплощ[её]нн\w*\s+аффект/iu',
            ],
            'Эксцентричное поведение' => [
                '/эксцентричн\w*\s+поведен/iu',
                '/необычны\w*\s+увлечени/iu',
                '/странн\w*\s+ритуал/iu',
                '/эксцентричност/iu',
            ],
            // Идеи отношения (часто «про знаки»/«всё связано со мной»), мягкий триггер:
            'Идеи отношения' => [
                '/события\s+вокруг\s+складываются\s+по\s+особым\s+знакам/iu',
                '/(все|всё)\s+вокруг\s+(касается|про)\s+меня/iu',
                '/нам[её]ки\s+в\s+окружени[ие]/iu',
            ],
            // Мягкие «парапсихологические» верования — можно тоже вести в магическое мышление
            // но оставим как отдельный симптом, если такой есть в БД:
            'Необычные убеждения' => [
                '/обладает\s+особым\s+чуть[её]м/iu',
                '/способе[нна]\s+предугадывать\s+будущ/iu',
            ],
        ];

        // Подтягиваем возможные цели из БД
        $symMap = \App\Models\Symptom::whereIn('title', array_keys($want))->get()->keyBy('title');

        // Проходим по каждому «желанному» симптому и ищем подтверждающее предложение
        foreach ($want as $title => $patterns) {
            $hit = false; $picked = null;
            foreach ($sentencesRaw as $sr) {
                $srLow = mb_strtolower($sr, 'UTF-8');
                foreach ((array)$patterns as $re) {
                    if (preg_match($re, $srLow)) { $hit = true; $picked = $sr; break; }
                }
                if ($hit) break;
            }

            if ($hit && isset($symMap[$title])) {
                $id = (int)$symMap[$title]->id;
                if (!in_array($id, $foundPositiveIds, true)) {
                    $rawItems[$id] = [
                        'symptom_id' => $id,
                        'title'      => $title,
                        'sentence'   => $picked,
                        'negated'    => false,
                        'confidence' => 0.62, // немного выше базового, чтобы попасть в top-N
                    ];
                    $foundPositiveIds[] = $id;
                }
            }
        }

        // Уберём дубликаты
        $foundPositiveIds = array_values(array_unique($foundPositiveIds));
    }

    private function injectAnankasticHeuristics(array &$rawItems, array &$foundPositiveIds, array $sentencesRaw): void
    {
        // ВАЖНО: заголовки должны совпадать с Symptom::title
        $want = [
            'Перфекционизм, мешающий завершать задачи' => [
                'перфекцион', 'безошибоч', 'довед[её]т[^.]{0,40}\s+до\s+идеал',
                'медленн[^.]{0,40}\s+провер', 'не\s+завершает[^.]{0,60}\s+пока[^.]{0,60}\s+идеал',
            ],
            'Чрезмерная занятость деталями, правилами и списками' => [
                'детал', 'правил', 'списков', 'планирован', 'составлени[ея]\s+подробн',
            ],
            'Чрезмерная преданность работе ценой досуга' => [
                'задержива[еется][^.]{0,40}\s+на\s+работ', 'редко\s+отдых', 'работать\s+по\s+инструкци',
            ],
            'Негибкие моральные стандарты/гиперсовестливость' => [
                'строг[а-яё]*\s+стандарт', 'совестлив', 'негибк[а-яё]*\s+морал', 'ж[её]стко\s+критику',
            ],
            'Ригидность/негибкость' => [
                'ригид', 'плохо\s+переносит\s+изменен', 'непредвиденн[а-яё]*\s+ситуаци',
            ],
            'Трудности делегирования' => [
                'трудно\s+делегир', 'предпочитает\s+делать\s+вс[её]\s+сам', 'так\s+над[её]жн',
            ],
            'Скупость/бережливость' => [
                'тратит\s+скупо', 'бережлив', 'предпочитает\s+копить', 'лишних\s+расход',
            ],
            'Собирательство мелочей' => [
                'хранит\s+стар[а-яё]*\s+чек', 'хранит\s+упаковк', 'на\s+всякий\s+случай',
            ],
            'Поддержание строгого порядка' => [
                'строгий\s+порядок', 'по\s+заранее\s+заданн[а-яё]*\s+схем', 'до\s+миллиметр',
            ],
            'Категоричность/упрямство' => [
                'категорич', 'наставнич',
            ],
            'Трудность принятия решений/сомнения' => [
                'долго\s+обдумывает', 'сомневает', 'откладывает\s+завершен',
            ],
        ];

        $symMap = \App\Models\Symptom::whereIn('title', array_keys($want))->get()->keyBy('title');

        foreach ($want as $title => $patterns) {
            $hit = false; $picked = null;
            foreach ($sentencesRaw as $sr) {
                $srLow = mb_strtolower($sr, 'UTF-8');
                foreach ((array)$patterns as $reBody) {
                    // Оборачиваем тело паттерна; делимитер ~, флаги iu
                    $pattern = '~' . $reBody . '~iu';
                    if (@preg_match($pattern, '') === false) {
                        \Log::error('Bad regex in OCPD injector', ['pattern' => $pattern]);
                        continue;
                    }
                    if (preg_match($pattern, $srLow)) { $hit = true; $picked = $sr; break; }
                }
                if ($hit) break;
            }

            if ($hit && isset($symMap[$title])) {
                $id = (int)$symMap[$title]->id;
                if (!in_array($id, $foundPositiveIds, true)) {
                    $rawItems[$id] = [
                        'symptom_id' => $id,
                        'title'      => $title,
                        'sentence'   => $picked,
                        'negated'    => false,
                        'confidence' => 0.62,
                    ];
                    $foundPositiveIds[] = $id;
                }
            }
        }

        $foundPositiveIds = array_values(array_unique($foundPositiveIds));
    }

    private function detectEpisodeMeta(string $text): array
    {
        $t = ' ' . mb_strtolower($text, 'UTF-8') . ' ';

        $acute_onset = (bool) preg_match('/\b(остр(о|ый)|внезапн|за\s+несколько\s+час|за\s+несколько\s+дн|в\s+течени[ие]\s+тр[её]х?\s+дн|в\s+течени[ие]\s+недел)\b/u', $t);
        $duration_short = (bool) preg_match('/\b(дн(я|ей)|недел[яьи])\b/u', $t);
        $duration_long  = (bool) preg_match('/\b(нескольк[оа]\s+лет|много\s+лет|годами|хронич|долгое\s+время|с\s+(подростков(ого)?|юношеск(ого)?)\s+возраст[а-я]*)\b/u', $t);
        $stressor       = (bool) preg_match('/\b(после\s+стресс|на\s+фоне\s+стресс|после\s+(ссоры|развода|увольнения|потери|конфликт|травм))\b/u', $t);
        $no_past_psych  = (bool) preg_match('/\b(впервые|раньше\s+не\s+было|ранее\s+не\s+наблюдалось|психотическ\w*\s+эпизод\w*\s+не\s+было)\b/u', $t);

        // ПАВ
        $substance_markers = [
            '/\b(алкогол|спирт|выпива|запо[йя]|опьянен|опьянени)\b/u',
            '/\b(опиоид|героин|морфин|метадон|кодеин)\b/u',
            '/\b(каннаб|марихуан|гашиш|конопл)\b/u',
            '/\b(кокаин|амфетамин|метамфетамин|мдма|экстази|стимулятор)\b/u',
            '/\b(лсд|псилоцибин|галлюциноген)\b/u',
            '/\b(никотин|сигарет|табак)\b/u',
            '/\b(бензодиазепин|феназепам|золпидем|снотворн|седативн)\b/u',
            '/\b(интоксикац|передоз|отмена|абстиненц|воздержан)\b/u',
        ];
        $etiology_substance = false;
        foreach ($substance_markers as $re) {
            if (preg_match($re, $t)) { $etiology_substance = true; break; }
        }

        $polymorph = (bool) preg_match('/\b(полиморфн|быстро\s+меняющ|пестрая\s+симптоматик|флюктуирующ)\b/u', $t);
        $disorg    = (bool) preg_match('/\b(дезорганиз|бессвязн|поведен[иия]\s+хаотич)\b/u', $t);

        // Доп. флаги
        $insidious_onset = (bool) preg_match('/\b(постепенн|незаметн|вялотекущ|с\s+детств|с\s+подростков)\b/u', $t);
        $two_weeks = (bool) preg_match('/\b(две\s+недел|2\s*недел|(?:≥|>=)\s*14\s*дн)\b/u', $t);
        $cooccurrence = (bool) preg_match('/\b(одновремен|на\s+фоне\s+депресс|на\s+фоне\s+мани|вместе\s+с\s+(аффект|депресс|мани))\b/u', $t);
        $bizarre_delusion = (bool) preg_match('/\b(бизарн|вычурн|магическ|бред\s+воздейств|бред\s+влияния)\b/u', $t);

        // НОВОЕ: анамнез мании/гипомании
        $no_past_mania = (bool) preg_match('/\b(не\s+было|отрицает)\s+(периодов\s+)?(повышенного\s+настроения|маниакал\w*|гипомани\w*)\b/u', $t);
        $past_mania    = (bool) preg_match('/\b(в\s+анамнезе|ранее|в\s+прошл\w+)\s+(были|отмечались)\s+(период\w*\s+)?(повышенного\s+настроения|маниакал\w*|гипомани\w*)\b/u', $t);

        return [
            'acute_onset'        => $acute_onset,
            'duration_short'     => $duration_short,
            'duration_long'      => $duration_long,
            'stressor'           => $stressor,
            'no_past_psychosis'  => $no_past_psych,
            'etiology_substance' => $etiology_substance,
            'polymorph'          => $polymorph,
            'disorganization'    => $disorg,
            'insidious_onset'    => $insidious_onset,
            'two_weeks'          => $two_weeks,
            'cooccurrence'       => $cooccurrence,
            'bizarre_delusion'   => $bizarre_delusion,
            // NEW:
            'no_past_mania'      => $no_past_mania,
            'past_mania'         => $past_mania,
        ];
    }

    /**
     * Обнаружение "смешанного" паттерна РЛ (для F61):
     * - минимум два сильных F60-кандидата
     * - малый разрыв лидера
     * - кросс-кластерность (разные кластеры A/B/C)
     * - хроничность/первазивность (duration_long|insidious_onset)
     * - отсутствие острой реактивности (нет acute_onset/stressor)
     */
    private function detectF61Mix(\Illuminate\Support\Collection $items, array $clusters): array
    {
        // 1) Соберём F60.* и близкий F21
        $pdRows = $items->filter(function ($it) {
            $d = $it['diagnosis']; if (!$d) return false;
            $code = $d->code ?? '';
            return preg_match('/^F60\./u', $code) || $code === 'F21';
        })->values();

        if ($pdRows->count() < 2) {
            return ['ok' => false];
        }

        // 2) Нормальная "сила" кандидата: берём totalScore (у вас уже с бустами)
        // Отфильтруем слабые по порогу
        $strong = $pdRows->filter(fn($r) => ($r['totalScore'] ?? 0.0) >= 0.60)
            ->sortByDesc('totalScore')
            ->values();

        if ($strong->count() < 2) {
            return ['ok' => false];
        }

        // 3) Топ-2 + разрыв
        $top1 = $strong[0]; $top2 = $strong[1];
        $s1 = (float)($top1['totalScore'] ?? 0.0);
        $s2 = (float)($top2['totalScore'] ?? 0.0);
        if ($s2 <= 0.0) {
            return ['ok' => false];
        }
        $gap = $s1 / $s2; // хотим небольшой разрыв
        if ($gap > 1.25) { // допустимый максимум (настраиваемо)
            return ['ok' => false];
        }

        // 4) Кросс-кластерность A/B/C
        $clustersHit = $this->clustersOfCodes([$top1['diagnosis']->code, $top2['diagnosis']->code]);
        if (count($clustersHit) < 2) {
            // попробуем захватить третьего, чтобы добрать второй кластер
            if ($strong->count() >= 3) {
                $top3 = $strong[2];
                $clustersHit = $this->clustersOfCodes([$top1['diagnosis']->code, $top2['diagnosis']->code, $top3['diagnosis']->code]);
            }
            if (count($clustersHit) < 2) {
                return ['ok' => false];
            }
        }

        // 5) Хроничность/первазивность и отсутствие "реактивности"
        $F = $clusters['flags'] ?? [];
        $chronic = !empty($F['duration_long']) || !empty($F['insidious_onset']);
        $nonReactive = empty($F['acute_onset']) && empty($F['stressor']);

        if (!$chronic || !$nonReactive) {
            return ['ok' => false];
        }

        // 6) Сила микса (для шкалы буста)
        // Чем ближе баллы и больше кластеров — тем сильнее
        $close = min($s1, $s2) / max($s1, $s2); // 0..1
        $k = count($clustersHit);                // 2 или 3
        $strength = min(1.0, 0.55 + 0.25 * $close + 0.10 * ($k - 1)); // типичная 0.7–0.9

        return [
            'ok'          => true,
            'strength'    => round($strength, 3),
            'gap'         => round($gap, 3),
            'kclusters'   => array_keys($clustersHit),
            'top_codes'   => [
                $top1['diagnosis']->code,
                $top2['diagnosis']->code,
                isset($top3) ? $top3['diagnosis']->code : null
            ],
        ];
    }

    private function clustersOfCodes(array $codes): array
    {
        $hit = [];
        foreach (self::PD_CLUSTERS as $cl => $list) {
            foreach ($codes as $c) {
                if ($c && in_array($c, $list, true)) {
                    $hit[$cl] = true;
                }
            }
        }
        return $hit;
    }

    /**
     * Применение буста к F61.* + мягкая "шапочка" на F60.*,
     * чтобы F61 мог занять лидирующее место при смешанном паттерне.
     */
    private function applyF61MixBoost(\Illuminate\Support\Collection $items, array $mix): \Illuminate\Support\Collection
    {
        $strength = (float)$mix['strength']; // ~0.7–0.9
        // Дельта — от силы микса: 0.10..0.20
        $delta = max(0.10, min(0.20, ($strength - 0.50)));

        $hasF61 = false;

        $items = $items->map(function ($it) use ($delta, &$hasF61) {
            $d = $it['diagnosis']; if (!$d) return $it;
            $code = $d->code ?? '';

            // Поднимаем F61.0/F61.1
            if ($code === 'F61.0' || $code === 'F61.1') {
                $it['totalScore'] = min(1.0, (float)$it['totalScore'] + $delta);
                $hasF61 = true;
            }
            return $it;
        });

        // Если F61 отсутствует в выборке (например, нет записи в БД
        // или не прошёл порог), попробуем "мягко опустить" F60-лидера — это
        // повысит шансы F61 пройти порог при следующем пересчёте/вызове.
        if (!$hasF61) {
            $items = $items->map(function ($it) use ($mix) {
                $d = $it['diagnosis']; if (!$d) return $it;
                $code = $d->code ?? '';
                // Чуть уменьшаем очки у явного лидера среди F60.* (и F21)
                if ($code && (preg_match('/^F60\./u', $code) || $code === 'F21')) {
                    $it['totalScore'] = max(0.0, (float)$it['totalScore'] - 0.04);
                }
                return $it;
            });
        }

        // На случай гипердиагностики F43.2 в хронических кейсах — чуть снизим её, если она есть.
        $items = $items->map(function ($it) {
            $d = $it['diagnosis']; if (!$d) return $it;
            if ($d->code === 'F43.2') {
                $it['totalScore'] = max(0.0, (float)$it['totalScore'] - 0.08);
            }
            return $it;
        });

        return $items;
    }
}

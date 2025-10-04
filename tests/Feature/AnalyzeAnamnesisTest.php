<?php

namespace Tests\Feature;

use App\Models\Symptom;
use App\Models\Diagnose;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnalyzeAnamnesisTest extends TestCase
{
    use RefreshDatabase;

    private function getMatchedSymptomsFromNeural(string $text)
    {
        $response = Http::withToken(config('services.openai.key'))->post(config('services.openai.url'), [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Ты — медицинский ассистент. Извлеки из текста пары {"symptom": "...", "sentence": "..."} в формате JSON-массива. Используй только стандартные термины.'
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ]
        ]);

        $raw = $response->json('choices.0.message.content') ?? '';
        $normalized = trim($raw, "\" \n\r");

        if (str_starts_with($normalized, '{') && str_ends_with($normalized, '}')) {
            $normalized = "[$normalized]";
        }

        $normalized = preg_replace("/\n/", '', $normalized);
        $parsed = json_decode($normalized, true);

        $this->assertIsArray($parsed, 'Нейросеть вернула не массив');
        $this->assertNotEmpty($parsed, 'Массив от нейросети пуст');

        $allSymptoms = Symptom::all();

        return collect($parsed)->map(function ($pair) use ($allSymptoms) {
            if (!isset($pair['symptom'])) return null;

            return $allSymptoms->first(function ($s) use ($pair) {
                similar_text(mb_strtolower($pair['symptom']), mb_strtolower($s->title), $percent);
                return $percent >= 80;
            });
        })->filter()->unique('id');
    }

    public function test_neural_api_returns_symptoms()
    {
        $text = 'Пациент жалуется на бессонницу и тревогу по ночам.';

        $response = Http::withToken(config('services.openai.key'))->post(config('services.openai.url'), [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Ты — медицинский ассистент. Извлеки из текста пары вида: {"symptom": "...", "sentence": "..."}'
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ]
        ]);

        $data = $response->json();

        $this->assertArrayHasKey('choices', $data);
        $this->assertIsArray($data['choices']);

        $contentRaw = $data['choices'][0]['message']['content'] ?? null;
        $this->assertNotNull($contentRaw, 'GPT не вернул content');

        $normalized = '[' . trim($contentRaw, "\" \n\r") . ']';
        $normalized = preg_replace("/\n/", '', $normalized);

        $content = json_decode($normalized, true);

        $this->assertIsArray($content, 'Результат не является массивом');
        $this->assertNotEmpty($content, 'Массив пар "symptom" => "sentence" пуст');

        $firstSymptom = strtolower($content[0]['symptom'] ?? '');

        $this->assertStringContainsString('бессон', $firstSymptom);
    }

    public function test_analyze_anamnesis_logic_matches_diagnosis()
    {
        $symptom = Symptom::create(['title' => 'бессонница']);
        $diagnose = Diagnose::create(['title' => 'Невроз', 'code' => 'F48']);

        $diagnose->requiredSymptoms()->attach($symptom->id, ['required' => true]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        // ← Заменили на строку JSON, как будто от OpenAI
                        'content' => '[{"symptom": "бессонница", "sentence": "Пациент не может уснуть."}]'
                    ]
                ]]
            ])
        ]);

        $response = $this->postJson('/analyze-anamnesis', [
            'text' => 'Пациент не может уснуть.'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'text' => 'пациент не может уснуть.',
        ]);
        $response->assertJsonFragment([
            'found_symptoms' => ['бессонница'],
        ]);
    }

    public function test_neural_extraction_from_long_anamnesis()
    {
        // Загружаем в базу заранее определённые симптомы
        Symptom::insert([
            ['title' => 'головные боли'],
            ['title' => 'снижение аппетита'],
            ['title' => 'потеря веса'],
            ['title' => 'нарушение сна'],
            ['title' => 'тревожность'],
            ['title' => 'раздражительность'],
            ['title' => 'усталость'],
            ['title' => 'плаксивость'],
            ['title' => 'навязчивые мысли'],
            ['title' => 'снижение настроения'],
            ['title' => 'агрессивность']
        ]);

        $anamnesis = <<<TEXT
Пациент жалуется на головные боли, усиливающиеся к вечеру. Отмечает снижение аппетита и потерю веса. Сон нарушен: засыпает с трудом, часто просыпается среди ночи. Появилась раздражительность, тревожность и плаксивость. Стал избегать общения с друзьями, замкнулся в себе. Жалуется на общую усталость и быструю утомляемость. Иногда возникают приступы учащённого сердцебиения. Бывают трудности с концентрацией внимания. Сообщает о навязчивых мыслях в течение дня. Наблюдаются эпизоды снижения настроения по утрам.
TEXT;

        // вызываем и сохраняем результат
        $matched = $this->getMatchedSymptomsFromNeural($anamnesis);

        // отладочный вывод
        dump('Найденные симптомы:', $matched->pluck('title')->toArray());

        // ожидаем минимум 3 совпадения
        $this->assertGreaterThanOrEqual(
            3,
            $matched->count(),
            "Ожидалось минимум 3 совпадений симптомов, найдено: {$matched->count()}"
        );
    }
}

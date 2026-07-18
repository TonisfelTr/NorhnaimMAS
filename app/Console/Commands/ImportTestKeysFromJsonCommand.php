<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Test;
use App\Models\TestCardInterpretation;
use App\Models\TestKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ImportTestKeysFromJsonCommand extends Command
{
    protected $signature = 'tests:keys:import
                            {code : Код теста (tests.code)}
                            {file : Путь к JSON с ключами}
                            {--truncate : Стереть существующие ключи/интерпретации перед импортом}';

    protected $description = 'Импорт ключей (шкал) и интерпретаций теста из JSON.';

    public function handle(): int
    {
        $code = (string) $this->argument('code');
        $file = (string) $this->argument('file');

        /** @var Test|null $test */
        $test = Test::query()->where('code', $code)->first();
        if (! $test) {
            $this->error("Тест с code='{$code}' не найден.");
            return self::FAILURE;
        }

        if (! is_file($file)) {
            $this->error("Файл '{$file}' не найден.");
            return self::FAILURE;
        }

        $json = \file_get_contents($file);
        $data = \json_decode($json, true);

        if (! \is_array($data) || ! \is_array($data['keys'] ?? null)) {
            $this->error('Некорректный JSON: ожидался корневой объект с массивом "keys".');
            return self::FAILURE;
        }

        DB::transaction(function () use ($test, $data): void {
            if ($this->option('truncate')) {
                // Удаляем интерпретации и ключи теста
                DB::table('test_interpretations')->where('test_id', $test->id)->delete();
                DB::table('test_keys')->where('test_id', $test->id)->delete();
            }

            $hasCodeColumn = Schema::hasColumn('test_keys', 'code');
            $created = 0;

            foreach ($data['keys'] as $idx => $k) {
                // Валидация «на лету»
                $title = (string) ($k['title'] ?? '');
                $items = $k['items'] ?? null;

                if ($title === '' || ! \is_array($items) || \count($items) === 0) {
                    throw new \RuntimeException(
                        "Ключ #{$idx}: отсутствуют обязательные поля 'title' и/или 'items'."
                    );
                }

                // Создаём ключ
                /** @var TestKey $key */
                $key = new TestKey();
                $key->test_id  = $test->id;
                $key->title    = $title;
                $key->item_ids = \json_encode(\array_values(\array_map('intval', $items)), JSON_UNESCAPED_UNICODE);

                // Не у всех схем есть колонка code — ставим только если есть
                if ($hasCodeColumn && isset($k['code'])) {
                    $key->code = (string) $k['code'];
                }

                // Доп. поля, если у тебя есть (необязательно):
                // $key->method = $k['method'] ?? 'sum';   // например, 'sum', 'avg', 'sum_x_factor'
                // $key->factor = $k['factor'] ?? null;    // множитель для индексов

                $key->save();

                // Интерпретации (опционально)
                if (! empty($k['ranges']) && \is_array($k['ranges'])) {
                    foreach ($k['ranges'] as $rIdx => $range) {
                        if (! \is_array($range) || \count($range) < 3) {
                            throw new \RuntimeException("Ключ '{$title}': некорректный диапазон #{$rIdx}.");
                        }
                        [$min, $max, $text] = $range;

                        TestCardInterpretation::create([
                            'test_id'   => $test->id,
                            'key_id'    => $key->id,
                            'min_score' => (int) $min,
                            'max_score' => (int) $max,
                            'text'      => (string) $text,
                        ]);
                    }
                }

                $created++;
            }

            $this->info("Импортировано ключей: {$created}");
        });

        return self::SUCCESS;
    }
}

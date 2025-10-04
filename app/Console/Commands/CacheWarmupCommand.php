<?php

namespace App\Console\Commands;

use App\Models\Diagnose;
use App\Models\Symptom;
use App\Services\AnamnesisAnalyzeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CacheWarmupCommand extends Command
{
    protected $signature = 'norhnaim:warmup
                            {--only= : aliases|diagnoses}
                            {--rebuild : очистить и перестроить кэши NN}
                            {--debug : показать вывод сидеров}';

    protected $description = 'Rebuild cache for symptom aliases and diagnosis maps; optionally reseed data.';

    public function handle(AnamnesisAnalyzeService $nn): int
    {
        $only    = strtolower((string)$this->option('only'));
        $debug   = (bool)$this->option('debug');
        $rebuild = (bool)$this->option('rebuild');

        if ($only && !in_array($only, ['aliases', 'diagnoses'], true)) {
            $this->error('Опция --only поддерживает только: aliases | diagnoses');
            return self::FAILURE;
        }

        $doAliases   = !$only || $only === 'aliases';
        $doDiagnoses = !$only || $only === 'diagnoses';

        // базовые таблицы
        $missingCore = $this->missingTables(['symptoms']);
        if (!empty($missingCore)) {
            $this->warn('⛔ Не найдены базовые таблицы: ' . implode(', ', $missingCore));
            $this->line('👉 Сначала выполните установку: php artisan norhnaim:install');
            return self::FAILURE;
        }

        if ($doAliases) {
            $need = $this->missingTables(['symptom_aliases']);
            if (!empty($need)) {
                $this->warn('⛔ Для прогрева алиасов не хватает таблиц: ' . implode(', ', $need));
                $this->line('👉 Выполните: php artisan norhnaim:install');
                return self::FAILURE;
            }
        }

        if ($doDiagnoses) {
            $need = $this->missingTables(['diagnoses', 'diagnose_symptoms']);
            if (!empty($need)) {
                $this->warn('⛔ Для прогрева диагнозов не хватает таблиц: ' . implode(', ', $need));
                $this->line('👉 Выполните: php artisan norhnaim:install');
                return self::FAILURE;
            }
        }

        DB::connection()->disableQueryLog();

        try {
            $diagTotals  = null;
            $aliasTotals = null;

            if ($doDiagnoses) {
                $this->info('🔧 Прогрев: наполнение диагнозов (сидер)…');

                $beforeDiag  = (int) DB::table('diagnoses')->count();
                $beforeLinks = (int) DB::table('diagnose_symptoms')->count();

                $this->runSeederQuiet(\Database\Seeders\DiagnosesFillSeeder::class, $debug);

                $afterDiag  = (int) DB::table('diagnoses')->count();
                $afterLinks = (int) DB::table('diagnose_symptoms')->count();

                $diagTotals = [
                    'diag'      => $afterDiag,
                    'diag_new'  => max(0, $afterDiag - $beforeDiag),
                    'links'     => $afterLinks,
                    'links_new' => max(0, $afterLinks - $beforeLinks),
                ];

                $this->line(sprintf(
                    "   ↳ Диагнозов: %d %s",
                    $diagTotals['diag'],
                    $diagTotals['diag_new'] ? "(новых: +{$diagTotals['diag_new']})" : '(без новых)'
                ));
                $this->line(sprintf(
                    "   ↳ Связей диагноз–симптом: %d %s",
                    $diagTotals['links'],
                    $diagTotals['links_new'] ? "(новых: +{$diagTotals['links_new']})" : '(без новых)'
                ));
            }

            if ($doAliases) {
                $this->info('🔧 Прогрев: алиасы симптомов (сидер)…');

                $beforeAliases = (int) DB::table('symptom_aliases')->count();
                $this->runSeederQuiet(\Database\Seeders\SymptomAliasSeeder::class, $debug);
                $afterAliases = (int) DB::table('symptom_aliases')->count();

                $aliasTotals = [
                    'aliases'      => $afterAliases,
                    'aliases_new'  => max(0, $afterAliases - $beforeAliases),
                ];

                $this->line(sprintf(
                    "   ↳ Всего алиасов: %d %s",
                    $aliasTotals['aliases'],
                    $aliasTotals['aliases_new'] ? "(новых: +{$aliasTotals['aliases_new']})" : '(без новых)'
                ));
            }

            // ---------- кэши анализатора ----------
            $this->info('🔧 Кэши анализатора…');
            if ($rebuild) {
                Cache::forget('nn:symptoms:with_aliases');
                Cache::forget('nn:symptom_index:v1');
                Cache::forget('nn:diag_bitsets:v1');
            }

            $built = method_exists($nn, 'warmup')
                ? $nn->warmup($rebuild)
                : $this->warmupFallback();

            $this->line(sprintf(
                "   ↳ symptoms: %d, aliases: %d, diagnoses: %d",
                (int)($built['symptoms'] ?? 0),
                (int)($built['aliases'] ?? 0),
                (int)($built['diagnoses'] ?? 0)
            ));

            // итоговая рамка
            $this->printSummaryBox($diagTotals, $aliasTotals);
            $this->info('✅ Прогрев завершён.');
            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error('Ошибка при прогреве: ' . $e->getMessage());

            if ($debug && ($out = trim(Artisan::output())) !== '') {
                $this->line('');
                $this->line('┈ Вывод Artisan:');
                $this->line($out);
            }

            return self::FAILURE;
        }
    }

    protected function runSeederQuiet(string $class, bool $debug): void
    {
        $code = Artisan::call('db:seed', [
            '--class'          => $class,
            '--no-interaction' => true,
            '--force'          => true,
            '--quiet'          => !$debug,
        ]);

        if ($code !== 0) {
            $out = trim(Artisan::output());
            throw new \RuntimeException("db:seed завершился с кодом {$code} для {$class}" . ($out ? ":\n{$out}" : ''));
        }

        if ($debug) {
            $out = trim(Artisan::output());
            if ($out !== '') {
                $this->line("┈ Вывод сидера {$class}:");
                $this->line($out);
            }
        }
    }

    protected function printSummaryBox(?array $diagTotals, ?array $aliasTotals): void
    {
        $lines = [];

        if ($diagTotals) {
            $lines[] = sprintf("Диагнозов: %d (новых: +%d)", $diagTotals['diag'], $diagTotals['diag_new']);
            $lines[] = sprintf("Связей диагноз–симптом: %d (новых: +%d)", $diagTotals['links'], $diagTotals['links_new']);
        }
        if ($aliasTotals) {
            $lines[] = sprintf("Алиасов: %d (новых: +%d)", $aliasTotals['aliases'], $aliasTotals['aliases_new']);
        }

        if (!$lines) return;

        $width  = max(array_map('mb_strlen', $lines)) + 2;
        $border = '┌' . str_repeat('─', $width) . '┐';

        $this->line($border);
        foreach ($lines as $l) {
            $this->line('│ ' . str_pad($l, $width - 1) . '│');
        }
        $this->line('└' . str_repeat('─', $width) . '┘');
    }

    protected function missingTables(array $tables): array
    {
        $missing = [];
        foreach ($tables as $t) {
            if (!Schema::hasTable($t)) {
                $missing[] = $t;
            }
        }
        return $missing;
    }

    /**
     * Локальный прогрев кэшей, если у сервиса нет метода warmup().
     */
    protected function warmupFallback(): array
    {
        $idx = Cache::remember('nn:symptom_index:v1', now()->addMinutes(60), function () {
            $all = Symptom::with('aliases')->get();
            $aliases = [];
            $map = [];
            foreach ($all as $s) {
                $map[$s->id] = ['title' => $s->title, 'aliases' => []];
                $als = [];
                foreach ($s->aliases ?? [] as $al) {
                    if (!empty($al->alias)) $als[] = $al->alias;
                }
                $als = array_slice(array_values(array_unique($als)), 0, 20);
                foreach ($als as $a) {
                    $norm  = mb_strtolower($a, 'UTF-8');
                    $norm  = preg_replace('~\s+~u', ' ', $norm);
                    $normQ = preg_quote($norm, '~');
                    $normQ = preg_replace('~\s+~u', '\\s+', $normQ);
                    $regex = '(?:(?<=^)|(?<=[^\p{L}\p{N}]))' . $normQ . '(?:(?=$)|(?=[^\p{L}\p{N}]))';

                    $aliases[] = [
                        'regex'      => $regex,
                        'symptom_id' => (int)$s->id,
                        'weight'     => max(1, mb_strlen($a)),
                        'raw'        => $a,
                    ];
                }
                $map[$s->id]['aliases'] = $als;
            }
            usort($aliases, fn($x, $y) => $y['weight'] <=> $x['weight']);

            // связанный кэш — список симптомов с алиасами
            Cache::remember('nn:symptoms:with_aliases', now()->addMinutes(30), fn() => Symptom::with('aliases')->get());

            return ['aliases' => $aliases, 'symptom_map' => $map];
        });

        $dmap = Cache::remember('nn:diag_bitsets:v1', now()->addMinutes(1440), function () {
            $rows = Diagnose::with(['requiredSymptoms:id', 'relativeSymptoms:id'])
                ->get(['id','code','exceptions','required_criteria','relative_criteria']);
            $out = [];
            foreach ($rows as $d) {
                $out[$d->id] = [
                    'id'        => (int)$d->id,
                    'code'      => $d->code,
                    'exceptions'=> (array)($d->exceptions ?? []),
                    'req'       => $d->requiredSymptoms->pluck('id')->map(fn($v)=>(int)$v)->values()->all(),
                    'rel'       => $d->relativeSymptoms->pluck('id')->map(fn($v)=>(int)$v)->values()->all(),
                    'reqCrit'   => (int)($d->required_criteria ?? 0),
                    'relCrit'   => (int)($d->relative_criteria ?? 0),
                ];
            }
            return $out;
        });

        return [
            'aliases'   => isset($idx['aliases']) ? count($idx['aliases']) : 0,
            'symptoms'  => isset($idx['symptom_map']) ? count($idx['symptom_map']) : 0,
            'diagnoses' => is_array($dmap) ? count($dmap) : 0,
        ];
    }
}

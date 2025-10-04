<?php

namespace App\Console\Commands;

use App\Models\Diagnose;
use App\Models\Symptom;
use App\Services\AnamnesisAnalyzeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class InstallCommand extends Command
{
    protected $signature = 'norhnaim:install
        {--fresh : Полностью пересобрать БД (migrate:fresh)}
        {--force : Принудительный запуск в production}
        {--skip-seed : Пропустить все сидеры}
        {--with-nn : Прогреть кэши NeuralNetworkService}
        {--rebuild-nn : Пересобрать кэши NeuralNetworkService с нуля}
        {--debug : подробный вывод сидеров}';

    protected $description = 'Run migrations, seeders, cache table and optional NeuralNetwork warmup, with a nice output.';

    public function handle(AnamnesisAnalyzeService $nn): int
    {
        $force      = (bool) $this->option('force');
        $fresh      = (bool) $this->option('fresh');
        $skipSeed   = (bool) $this->option('skip-seed');
        $withNN     = (bool) ($this->option('with-nn') || $this->option('rebuild-nn'));
        $rebuildNN  = (bool) $this->option('rebuild-nn');
        $debug      = (bool) $this->option('debug');

        $this->title('Norhnaim Installer');
        $startedAt = microtime(true);

        // 1) MIGRATIONS
        if (! $this->runMigrations($fresh, $force)) {
            return self::FAILURE;
        }

        // 2) SEEDERS
        if (! $skipSeed) {
            if (! $this->runSeedersSilent($force, $debug)) {
                return self::FAILURE;
            }
        } else {
            $this->note('Сидеры пропущены (--skip-seed).');
        }

        // 3) CACHE TABLE + MIGRATE
        if (! $this->runCacheTableMigration($force)) {
            return self::FAILURE;
        }

        // 4) NN WARMUP
        if ($withNN) {
            $this->section('NeuralNetwork Warmup');
            try {
                $this->line($rebuildNN ? 'Пересборка кэшей NN…' : 'Прогрев кэшей NN…');
                $t0 = microtime(true);
                $res = $this->warmupNN($nn, $rebuildNN);
                $this->table(['Показатель','Значение'], [
                    ['aliases',   $res['aliases']   ?? 0],
                    ['symptoms',  $res['symptoms']  ?? 0],
                    ['diagnoses', $res['diagnoses'] ?? 0],
                ]);
                $this->ok('NN warmup завершён', $t0);
            } catch (\Throwable $e) {
                $this->errorBox('NN warmup failed', $e->getMessage());
            }
        } else {
            $this->note('Прогрев NN пропущен (добавьте --with-nn или --rebuild-nn).');
        }

        $this->success('Install completed', $startedAt);
        return self::SUCCESS;
    }

    /* =========================== Seeders (тихо) =========================== */

    private function seeders(): array
    {
        return [
            'DiagnosesFillSeeder'        => 'Диагнозы',
            'SymptomAliasSeeder'         => 'Алиасы симптомов',
            'ClinicFillByFactorySeeder'  => 'Клиники (factory)',
            'DoctorFillByFactorySeeder'  => 'Врачи (factory)',
            'CreateNullClinicSeeder'     => 'Нулевая клиника (ID=0)',
            'SideEffectsFillSeeder'      => 'Побочные эффекты',
            'ContraindicationsSeeder'    => 'Противопоказания',
            'DrugSeeder'                 => 'Препараты',
            'LawyersFillSeeder'          => 'Юристы',
            'RolePermissionSeeder'       => 'Роли и права',
            'AdminUserSeeder'            => 'Администратор',
            'SettingsItemsSeeder'        => 'Настройки',
            'LabsSeeder'                 => 'Исследования'
        ];
    }

    private function runSeedersSilent(bool $force, bool $debug = false): bool
    {
        $this->section('Seeders');
        $seeders = $this->seeders();

        $bar = $this->output->createProgressBar(count($seeders));
        $bar->setFormat(' [%bar%] %percent:3s%%  %current%/%max%  %message%');
        $bar->setMessage('Старт…');
        $bar->start();

        foreach ($seeders as $class => $label) {
            $bar->setMessage($label);
            try {
                if ($debug) {
                    // показать предупреждения сидеров (например, "Симптом не найден")
                    $this->call('db:seed', ['--class' => $class, '--force' => $force]);
                } else {
                    $this->callSilent('db:seed', ['--class' => $class, '--force' => $force]);
                }
            } catch (\Throwable $e) {
                $bar->clear();
                $this->errorBox("Seeder {$class} ({$label}) failed", $e->getMessage());
                return false;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);
        $this->ok('Сидеры применены');

        return true;
    }

    /* =========================== Migrations & Cache Table =========================== */

    private function runMigrations(bool $fresh, bool $force): bool
    {
        $this->section('Migrations');
        $t0 = microtime(true);

        try {
            if ($fresh) {
                $this->line('migrate:fresh …');
                $this->call('migrate:fresh', ['--force' => $force]);
                $this->ok('Migrate fresh завершён', $t0);
            } else {
                $this->line('migrate …');
                $this->call('migrate', ['--force' => $force]);
                $this->ok('Migrate завершён', $t0);
            }
            return true;
        } catch (\Throwable $e) {
            $this->errorBox('Migration failed', $e->getMessage());
            return false;
        }
    }

    private function runCacheTableMigration(bool $force): bool
    {
        $this->section('Cache Table');
        try {
            $this->line('cache:table … (создаёт миграцию для таблицы кэша)');
            $this->callSilent('cache:table');
            $this->line('migrate … (применяем миграцию таблицы кэша)');
            $this->call('migrate', ['--force' => $force]);
            $this->ok('Таблица кэша готова');
            return true;
        } catch (\Throwable $e) {
            $this->errorBox('Cache table migration failed', $e->getMessage());
            return false;
        }
    }

    /* =========================== NN Warmup (с fallback) =========================== */

    private function warmupNN(AnamnesisAnalyzeService $nn, bool $rebuild): array
    {
        if (method_exists($nn, 'warmup')) {
            return $nn->warmup($rebuild);
        }

        // Fallback: строим те же кэши, что и сервис.
        $KEY_INDEX   = 'nn:symptom_index:v1';
        $KEY_BITSETS = 'nn:diag_bitsets:v1';
        if ($rebuild) {
            Cache::forget($KEY_INDEX);
            Cache::forget($KEY_BITSETS);
            Cache::forget('nn:symptoms:with_aliases');
        }

        $idx = Cache::remember($KEY_INDEX, now()->addMinutes(60), function () {
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
            usort($aliases, fn($x,$y) => $y['weight'] <=> $x['weight']);

            Cache::remember('nn:symptoms:with_aliases', now()->addMinutes(30), fn() => Symptom::with('aliases')->get());
            return ['aliases' => $aliases, 'symptom_map' => $map];
        });

        $dmap = Cache::remember($KEY_BITSETS, now()->addMinutes(1440), function () {
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

    /* =========================== Pretty Output =========================== */

    private function title(string $text): void
    {
        $this->newLine();
        $this->line(str_repeat('═', 64));
        $this->line('  '.mb_strtoupper($text));
        $this->line(str_repeat('═', 64));
        $this->newLine();
    }

    private function section(string $text): void
    {
        $this->newLine();
        $this->line("— {$text} —");
        $this->line(str_repeat('─', max(10, mb_strlen($text) + 4)));
    }

    private function ok(string $text, float $since = null): void
    {
        $suffix = $since ? sprintf(' (%.2fs)', microtime(true) - $since) : '';
        $this->info("✔ {$text}{$suffix}");
    }

    private function note(string $text): void
    {
        $this->comment("• {$text}");
    }

    private function success(string $text, float $startedAt): void
    {
        $this->newLine();
        $this->info("🎉 {$text} in ".sprintf('%.2fs', microtime(true) - $startedAt));
        $this->newLine();
    }

    private function errorBox(string $title, string $message): void
    {
        $this->newLine();
        $this->error("✖ {$title}");
        $this->line('<options=reverse> '.$message.' </>');
        $this->newLine();
    }
}

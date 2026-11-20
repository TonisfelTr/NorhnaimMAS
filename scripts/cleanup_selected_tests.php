<?php

declare(strict_types=1);

/**
 * Одноразовое безопасное удаление трёх ошибочно добавленных тестов:
 *
 * 90 / RORSHAH
 * 81 / ZUNG_SDS
 * 84 / ZUNG_SDS2
 *
 * По умолчанию только показывает план.
 *
 * Запуск:
 *   php scripts/cleanup_selected_tests.php
 *
 * Фактическое удаление:
 *   php scripts/cleanup_selected_tests.php --apply
 *
 * Если у тестов есть назначения или сеансы, скрипт остановится.
 * Удаление истории прохождений допускается только явно:
 *   php scripts/cleanup_selected_tests.php --apply --purge-usage
 *
 * Удаление выполняется существующим TestDeletionService, который
 * удаляет test_questionnaires, clinic_tests, вопросы, карточки,
 * интерпретации и остальные связанные записи.
 */

use App\Models\Test;
use App\Services\Tests\TestDeletionService;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

$app = require_once $root . '/bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

$expectedTests = [
    90 => 'RORSHAH',
    81 => 'ZUNG_SDS',
    84 => 'ZUNG_SDS2',
];

$apply = in_array('--apply', $argv, true);
$purgeUsage = in_array('--purge-usage', $argv, true);

if ($purgeUsage && !$apply) {
    fwrite(STDERR, "--purge-usage можно использовать только вместе с --apply.\n");
    exit(2);
}

$tests = Test::query()
    ->whereIn('id', array_keys($expectedTests))
    ->withCount(['assignments', 'sessions'])
    ->orderBy('id')
    ->get()
    ->keyBy(static fn (Test $test): int => (int) $test->getKey());

$errors = [];

foreach ($expectedTests as $id => $expectedCode) {
    $test = $tests->get($id);

    if (!$test) {
        $errors[] = "tests.id={$id}: запись не найдена.";
        continue;
    }

    if (mb_strtoupper((string) $test->code) !== $expectedCode) {
        $errors[] = sprintf(
            'tests.id=%d: ожидался code=%s, фактически code=%s. Удаление запрещено.',
            $id,
            $expectedCode,
            (string) $test->code
        );
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Проверка идентификаторов не пройдена:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, " - {$error}\n");
    }
    exit(3);
}

echo "Будут обработаны следующие тесты:\n";

$hasUsage = false;

foreach ($expectedTests as $id => $expectedCode) {
    /** @var Test $test */
    $test = $tests->get($id);

    $assignmentCount = (int) $test->assignments_count;
    $sessionCount = (int) $test->sessions_count;

    if ($assignmentCount > 0 || $sessionCount > 0) {
        $hasUsage = true;
    }

    printf(
        "ID: %d | %s | code=%s | owner_doctor_id=%s | назначений=%d | сеансов=%d\n",
        $id,
        (string) $test->name,
        (string) $test->code,
        $test->owner_doctor_id === null
            ? 'NULL'
            : (string) $test->owner_doctor_id,
        $assignmentCount,
        $sessionCount
    );
}

if (!$apply) {
    echo "\nDRY-RUN: база данных не изменялась.\n";
    echo "Для удаления без истории прохождений выполните команду с --apply.\n";

    if ($hasUsage) {
        echo "Внимание: у части тестов есть назначения/сеансы. "
            . "Для удаления вместе с историей потребуется отдельное "
            . "--apply --purge-usage.\n";
    }

    exit(0);
}

if ($hasUsage && !$purgeUsage) {
    fwrite(
        STDERR,
        "\nУдаление остановлено: у одного или нескольких тестов есть назначения/сеансы.\n"
        . "История не будет удалена без явного флага --purge-usage.\n"
    );
    exit(4);
}

$deletionService = app(TestDeletionService::class);

DB::transaction(static function () use (
    $expectedTests,
    $deletionService,
    $purgeUsage
): void {
    foreach ($expectedTests as $id => $expectedCode) {
        $test = Test::query()->lockForUpdate()->findOrFail($id);

        if (mb_strtoupper((string) $test->code) !== $expectedCode) {
            throw new RuntimeException(
                "tests.id={$id}: code изменился во время удаления."
            );
        }

        $deletionService->delete(
            $test,
            purgeUsage: $purgeUsage
        );

        echo "Удалён tests.id={$id}, code={$expectedCode}\n";
    }
});

echo "\nГотово. Все три теста и связанные записи удалены.\n";

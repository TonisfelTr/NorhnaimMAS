<?php

namespace App\Services\Tests;

use App\Models\Test;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class TestDeletionService
{
    /**
     * Полностью удаляет один тест и только его дочерние данные.
     *
     * Врачи, пациенты, пользователи и клиники не удаляются.
     */
    public function delete(
        Test $test,
        bool $purgeUsage
    ): void {
        $testId = (int) $test->getKey();

        DB::transaction(function () use (
            $test,
            $testId,
            $purgeUsage
        ): void {
            $sessionIds = $this->ids(
                'test_sessions',
                'test_id',
                $testId
            );

            $assignmentIds = $this->ids(
                'test_assignments',
                'test_id',
                $testId
            );

            $itemIds = $this->ids(
                'test_items',
                'test_id',
                $testId
            );

            $sectionIds = $this->ids(
                'test_sections',
                'test_id',
                $testId
            );

            $keyIds = $this->ids(
                'test_keys',
                'test_id',
                $testId
            );

            $stimulusIds = $this->ids(
                'test_stimuli',
                'test_id',
                $testId
            );

            $rubricIds = $this->ids(
                'test_rubrics',
                'test_id',
                $testId
            );

            $openResponseIds = collect()
                ->merge(
                    $this->ids(
                        'test_open_responses',
                        'session_id',
                        $sessionIds
                    )
                )
                ->merge(
                    $this->ids(
                        'test_open_responses',
                        'stimulus_id',
                        $stimulusIds
                    )
                )
                ->unique()
                ->values();

            if ($purgeUsage) {
                /*
                 * Самые зависимые runtime-записи.
                 */
                $this->remove([
                    ['test_response_codes', 'open_response_id', $openResponseIds],
                    ['test_response_codes', 'response_id', $openResponseIds],
                    ['test_rubric_scores', 'session_id', $sessionIds],
                    ['test_rubric_scores', 'open_response_id', $openResponseIds],
                    ['test_results', 'session_id', $sessionIds],
                    ['test_answers', 'session_id', $sessionIds],
                    ['test_session_protocols', 'session_id', $sessionIds],
                    ['patient_test_sessions', 'session_id', $sessionIds],
                    ['patient_test_sessions', 'test_session_id', $sessionIds],
                    ['patient_test_sessions', 'test_id', $testId],
                    ['test_open_responses', 'session_id', $sessionIds],
                ]);

                /*
                 * Возможные взаимные ссылки assignment/session.
                 */
                $this->setNull(
                    'test_sessions',
                    'id',
                    $sessionIds,
                    'assignment_id'
                );

                $this->setNull(
                    'test_assignments',
                    'id',
                    $assignmentIds,
                    'session_id'
                );

                $this->remove([
                    ['test_assignments', 'test_id', $testId],
                    ['test_sessions', 'test_id', $testId],
                ]);
            }

            /*
             * Медиа очищаем через модели, затем строки карточек
             * удаляем напрямую, не полагаясь на model events.
             */
            $test->loadMissing([
                'testCards.media',
                'sortCards.media',
            ]);

            foreach ($test->testCards as $card) {
                $card->clearMediaCollection(
                    'test_card_image'
                );
            }

            foreach ($test->sortCards as $card) {
                $card->clearMediaCollection(
                    'test_sort_card_image'
                );
            }

            $this->remove([
                ['test_cards', 'test_id', $testId],
                ['test_sort_cards', 'test_id', $testId],

                ['test_item_options', 'item_id', $itemIds],
                ['test_key_items', 'key_id', $keyIds],
                ['test_key_items', 'item_id', $itemIds],
                ['test_interpretations', 'key_id', $keyIds],
                ['test_interpretations', 'test_id', $testId],
                ['test_rubric_scores', 'rubric_id', $rubricIds],
                ['test_open_responses', 'stimulus_id', $stimulusIds],

                ['test_items', 'test_id', $testId],
                ['test_items', 'section_id', $sectionIds],
                ['test_sections', 'test_id', $testId],
                ['test_keys', 'test_id', $testId],
                ['test_stimuli', 'test_id', $testId],
                ['test_rubrics', 'test_id', $testId],

                ['test_card_interpretations', 'test_id', $testId],
                ['test_questionnaires', 'test_id', $testId],
                ['test_sort_interpretations', 'test_id', $testId],
                ['clinic_tests', 'test_id', $testId],
            ]);

            if (method_exists($test, 'clearMediaCollection')) {
                $test->clearMediaCollection();
            }

            /*
             * Удаляем строку напрямую и обязательно проверяем
             * результат. Это исключает ложное сообщение об успехе,
             * когда Eloquent deleting-event возвращает false.
             */
            $deleted = DB::table($test->getTable())
                ->where($test->getKeyName(), $testId)
                ->delete();

            if ($deleted !== 1) {
                throw new RuntimeException(
                    "Строка tests.id={$testId} не была удалена."
                );
            }

            if (
                DB::table($test->getTable())
                    ->where($test->getKeyName(), $testId)
                    ->exists()
            ) {
                throw new RuntimeException(
                    "После удаления tests.id={$testId} всё ещё существует."
                );
            }
        });
    }

    private function ids(
        string $table,
        string $column,
        mixed $value
    ): Collection {
        if (
            !Schema::hasTable($table)
            || !Schema::hasColumn($table, $column)
            || !Schema::hasColumn($table, 'id')
        ) {
            return collect();
        }

        $query = DB::table($table);

        $value instanceof Collection
            ? $query->whereIn($column, $value->all())
            : $query->where($column, $value);

        return $query
            ->pluck('id')
            ->filter()
            ->values();
    }

    /**
     * @param array<int, array{0:string,1:string,2:mixed}> $operations
     */
    private function remove(array $operations): void
    {
        foreach ($operations as [$table, $column, $value]) {
            if (
                !Schema::hasTable($table)
                || !Schema::hasColumn($table, $column)
            ) {
                continue;
            }

            if ($value instanceof Collection) {
                if ($value->isEmpty()) {
                    continue;
                }

                DB::table($table)
                    ->whereIn($column, $value->all())
                    ->delete();

                continue;
            }

            DB::table($table)
                ->where($column, $value)
                ->delete();
        }
    }

    private function setNull(
        string $table,
        string $idColumn,
        Collection $ids,
        string $targetColumn
    ): void {
        if (
            $ids->isEmpty()
            || !Schema::hasTable($table)
            || !Schema::hasColumn($table, $idColumn)
            || !Schema::hasColumn($table, $targetColumn)
        ) {
            return;
        }

        DB::table($table)
            ->whereIn($idColumn, $ids->all())
            ->update([
                $targetColumn => null,
            ]);
    }
}

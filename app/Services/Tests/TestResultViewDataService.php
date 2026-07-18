<?php

namespace App\Services\Tests;

use App\Models\Test;
use App\Models\TestSession;
use Illuminate\Support\Collection;

final class TestResultViewDataService
{
    private const TYPE_QUESTIONNAIRE = 'questionnaire';
    private const TYPE_IMAGE = 'image';
    private const TYPE_SORT = 'sort';

    public function make(
        TestSession $session,
        iterable $sections = [],
        array $stats = [],
        array $resultData = []
    ): array {
        $session->loadMissing([
            'test.testCards.media',
            'test.sortCards.media',
            'test.sortInterpretation',
            'patient',
        ]);

        $test = $session->test;

        abort_if(!$test, 404, 'Тест не найден.');

        $resultType = $this->resolveTestType(
            (string) ($test->type ?? self::TYPE_QUESTIONNAIRE)
        );

        $isQuestionnaire = $resultType === self::TYPE_QUESTIONNAIRE;
        $isImageTest = $resultType === self::TYPE_IMAGE;
        $isSortTest = $resultType === self::TYPE_SORT;

        $context = $this->normalizeArray($session->context);
        $contextAnswers = $this->normalizeArray(
            data_get($context, 'answers', [])
        );

        $questionSections = $this->normalizeQuestionSections($sections);

        $normalizedCards = match ($resultType) {
            self::TYPE_IMAGE => $this->normalizeCards(
                $test->testCards ?? [],
                $contextAnswers,
                'test_card_image'
            ),

            self::TYPE_SORT => $this->normalizeCards(
                $test->sortCards ?? [],
                [],
                'test_sort_card_image'
            ),

            default => collect(),
        };

        $sortOrder = $isSortTest
            ? $this->resolveSortOrder($context)
            : collect();

        $orderedCards = $this->orderCards(
            $normalizedCards,
            $sortOrder
        );

        $questionItems = $questionSections->flatMap(
            static fn (array $section): Collection => collect($section['items'])
        );

        $questionAnswered = $questionItems
            ->filter(
                static fn (array $item): bool => (bool) $item['has_answer']
            )
            ->count();

        [$displayTotal, $displayAnswered] = match ($resultType) {
            self::TYPE_IMAGE => [
                $normalizedCards->count(),
                $normalizedCards
                    ->filter(
                        static fn (array $card): bool => (bool) $card['has_answer']
                    )
                    ->count(),
            ],

            self::TYPE_SORT => [
                $normalizedCards->count(),
                min($normalizedCards->count(), $sortOrder->count()),
            ],

            default => [
                array_key_exists('total', $stats) && $stats['total'] !== null
                    ? (int) $stats['total']
                    : $questionItems->count(),

                array_key_exists('answered', $stats) && $stats['answered'] !== null
                    ? (int) $stats['answered']
                    : $questionAnswered,
            ],
        };

        $displayMissed = $isQuestionnaire
        && array_key_exists('missed', $stats)
        && $stats['missed'] !== null
            ? (int) $stats['missed']
            : max(0, $displayTotal - $displayAnswered);

        $resourceProfile = $this->normalizeArray(
            $test->resource_profile ?? []
        );

        $sortLabels = $this->resolveSortLabels(
            $test,
            $resourceProfile
        );

        $interpretation = $this->normalizeInterpretation(
            $resultData['interpretation'] ?? []
        );

        $overview = $this->normalizeArray(
            $resultData['overview'] ?? []
        );

        $chart = collect($resultData['chart'] ?? [])
            ->values()
            ->all();

        $resultCards = collect($resultData['resultCards'] ?? [])
            ->values();

        $showChart = $isQuestionnaire
            && (bool) ($resultData['showChart'] ?? false)
            && count($chart) > 0;

        $manualInterpretation = (string) (
            data_get($resourceProfile, 'interpretation_text')
            ?? data_get($interpretation, 'text')
            ?? ''
        );

        $patientId = $session->patient_id
            ?? $session->patient?->id;

        return [
            'resultType' => $resultType,

            'isQuestionnaire' => $isQuestionnaire,
            'isImageTest' => $isImageTest,

            /*
             * Оставляем старое имя для совместимости с Blade.
             */
            'isCardSort' => $isSortTest,
            'isSortTest' => $isSortTest,

            'typeLabel' => $this->resolveTypeLabel($resultType),
            'statusMeta' => $this->resolveStatusMeta(
                (string) $session->status
            ),
            'statLabels' => $this->resolveStatLabels($resultType),

            'questionSections' => $questionSections,
            'normalizedCards' => $normalizedCards,
            'orderedCards' => $orderedCards,
            'hasSortOrder' => $sortOrder->isNotEmpty(),

            'leftLabel' => $sortLabels['left'],
            'rightLabel' => $sortLabels['right'],

            'displayTotal' => $displayTotal,
            'displayAnswered' => $displayAnswered,
            'displayMissed' => $displayMissed,

            'overview' => $overview,
            'overviewScore' => data_get($overview, 'score', 0),
            'overviewMax' => data_get($overview, 'max'),
            'overviewPercent' => data_get($overview, 'percent'),

            'resultCards' => $resultCards,
            'interpretation' => $interpretation,
            'manualInterpretation' => $manualInterpretation,
            'chart' => $chart,
            'showChart' => $showChart,

            'backUrl' => $patientId
                ? route('doctors.patients.medical_card', $patientId)
                : url()->previous(),
        ];
    }

    private function resolveTestType(string $type): string
    {
        return match (mb_strtolower(trim($type))) {
            self::TYPE_IMAGE => self::TYPE_IMAGE,
            self::TYPE_SORT => self::TYPE_SORT,
            default => self::TYPE_QUESTIONNAIRE,
        };
    }

    private function resolveTypeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_IMAGE => 'Тест с изображениями',
            self::TYPE_SORT => 'Сортировка карточек',
            default => 'Опросник',
        };
    }

    private function resolveStatLabels(string $type): array
    {
        return match ($type) {
            self::TYPE_IMAGE => [
                'total' => 'Всего карточек',
                'answered' => 'Отвечено',
                'missed' => 'Пропущено',
            ],

            self::TYPE_SORT => [
                'total' => 'Всего карточек',
                'answered' => 'Расположено',
                'missed' => 'Не расположено',
            ],

            default => [
                'total' => 'Всего вопросов',
                'answered' => 'Отвечено',
                'missed' => 'Пропущено',
            ],
        };
    }

    private function resolveStatusMeta(string $status): array
    {
        return match ($status) {
            'finished',
            'complete',
            'completed',
            'coding_done' => [
                'label' => 'Завершён',
                'class' => 'success',
            ],

            'submitted',
            'in_review' => [
                'label' => 'Ожидает завершения',
                'class' => 'warning',
            ],

            'in_progress',
            'started' => [
                'label' => 'В процессе',
                'class' => 'info',
            ],

            'timeout' => [
                'label' => 'Истёк по времени',
                'class' => 'danger',
            ],

            'cancelled' => [
                'label' => 'Отменён',
                'class' => 'danger',
            ],

            default => [
                'label' => $status !== '' ? $status : 'Не указан',
                'class' => 'neutral',
            ],
        };
    }

    private function normalizeQuestionSections(iterable $sections): Collection
    {
        return collect($sections)
            ->map(function ($section): array {
                $items = collect(data_get($section, 'items', []))
                    ->map(function ($item): array {
                        $answer = data_get($item, 'answer_label')
                            ?? data_get($item, 'answer');

                        return [
                            'id' => data_get($item, 'id'),
                            'text' => (string) (
                                data_get($item, 'text')
                                ?? data_get($item, 'title')
                                ?? 'Вопрос'
                            ),
                            'answer' => $answer,
                            'has_answer' => $this->hasAnswer($answer),
                            'answer_text' => $this->answerToString($answer),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'title' => (string) (
                        data_get($section, 'title')
                        ?? 'Раздел'
                    ),
                    'items' => $items,
                    'items_count' => count($items),
                ];
            })
            ->values();
    }

    private function normalizeCards(
        iterable $cards,
        array $answers,
        string $mediaCollection
    ): Collection {
        return collect($cards)
            ->sortBy(
                static fn ($card): int => (int) data_get($card, 'sort', 0)
            )
            ->values()
            ->map(function ($card) use ($answers, $mediaCollection): array {
                $id = data_get($card, 'id');
                $answer = $this->resolveAnswer($answers, $id);

                return [
                    'id' => $id,
                    'title' => (string) (
                        data_get($card, 'title')
                        ?? 'Карточка'
                    ),
                    'question' => (string) (
                        data_get($card, 'question')
                        ?? ''
                    ),
                    'sort' => (int) data_get($card, 'sort', 0),
                    'type' => mb_strtolower((string) (
                        data_get($card, 'type')
                        ?? 'image'
                    )),
                    'text' => (string) (
                        data_get($card, 'text')
                        ?? ''
                    ),
                    'color' => (string) (
                        data_get($card, 'color')
                        ?? ''
                    ),
                    'image_url' => $this->resolveImageUrl(
                        $card,
                        $mediaCollection
                    ),
                    'answer' => $answer,
                    'has_answer' => $this->hasAnswer($answer),
                    'answer_text' => $this->answerToString($answer),
                ];
            });
    }

    private function resolveAnswer(array $answers, mixed $id): mixed
    {
        foreach ([$id, (string) $id] as $key) {
            if (array_key_exists($key, $answers)) {
                return $answers[$key];
            }
        }

        return null;
    }

    private function resolveImageUrl(
        mixed $card,
        string $mediaCollection
    ): string {
        $existingUrl = (string) (
            data_get($card, 'image_url')
            ?? data_get($card, 'image')
            ?? ''
        );

        if ($existingUrl !== '') {
            return $existingUrl;
        }

        if (
            !is_object($card)
            || !method_exists($card, 'getFirstMediaUrl')
        ) {
            return '';
        }

        $convertedUrl = (string) $card->getFirstMediaUrl(
            $mediaCollection,
            'front'
        );

        if ($convertedUrl !== '') {
            return $convertedUrl;
        }

        return (string) $card->getFirstMediaUrl(
            $mediaCollection
        );
    }

    private function resolveSortOrder(array $context): Collection
    {
        $sortOrder = data_get($context, 'sort_order', []);

        if (!is_array($sortOrder)) {
            return collect();
        }

        return collect($sortOrder)
            ->map(static fn ($id): string => (string) $id)
            ->filter(static fn (string $id): bool => $id !== '')
            ->unique()
            ->values();
    }

    private function orderCards(
        Collection $cards,
        Collection $sortOrder
    ): Collection {
        if ($sortOrder->isEmpty()) {
            return $cards;
        }

        $cardsById = $cards->keyBy(
            static fn (array $card): string => (string) $card['id']
        );

        $ordered = $sortOrder
            ->map(
                static fn (string $id): ?array => $cardsById->get($id)
            )
            ->filter()
            ->values();

        $remaining = $cards->reject(
            static fn (array $card): bool => $sortOrder->contains(
                (string) $card['id']
            )
        );

        return $ordered
            ->concat($remaining)
            ->values();
    }

    private function resolveSortLabels(
        Test $test,
        array $resourceProfile
    ): array {
        $interpretation = $test->sortInterpretation;

        return [
            'left' => (string) (
                $interpretation?->left_label
                ?? data_get($resourceProfile, 'left_label')
                ?? data_get($resourceProfile, 'sort.left_label')
                ?? 'Больше подходит'
            ),

            'right' => (string) (
                $interpretation?->right_label
                ?? data_get($resourceProfile, 'right_label')
                ?? data_get($resourceProfile, 'sort.right_label')
                ?? 'Меньше подходит'
            ),
        ];
    }

    private function normalizeInterpretation(mixed $interpretation): array
    {
        return array_merge([
            'title' => 'Интерпретация',
            'badge' => null,
            'text' => '',
            'items' => [],
            'meta' => [],
        ], $this->normalizeArray($interpretation));
    }

    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Collection) {
            return $value->all();
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function hasAnswer(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }

    private function answerToString(mixed $answer): string
    {
        if ($answer === null) {
            return '';
        }

        if (is_array($answer)) {
            return implode(', ', array_map(
                static fn ($value): string => is_scalar($value)
                    ? (string) $value
                    : json_encode(
                        $value,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                $answer
            ));
        }

        if (is_bool($answer)) {
            return $answer ? 'Да' : 'Нет';
        }

        return is_scalar($answer)
            ? (string) $answer
            : json_encode(
                $answer,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
    }
}

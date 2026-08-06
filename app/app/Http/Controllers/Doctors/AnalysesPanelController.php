<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\InstrumentalResearch;
use App\Models\LabResearch;
use App\Models\LabParameter;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalysesPanelController extends Controller
{
    /** Количество назначений на одной странице. */
    private const ASSIGNMENT_PER_PAGE = 30;

    /** Статус нового лабораторного назначения. */
    private const ASSIGNMENT_STATUS_ORDERED = 'ordered';

    /** Статус выполняемого лабораторного назначения. */
    private const ASSIGNMENT_STATUS_PROCESSING = 'processing';

    /** Статус завершённого лабораторного назначения. */
    private const ASSIGNMENT_STATUS_READY = 'ready';

    /** Обычный приоритет назначения. */
    private const ASSIGNMENT_PRIORITY_NORMAL = 'normal';

    /** Срочный приоритет назначения. */
    private const ASSIGNMENT_PRIORITY_URGENT = 'urgent';

    public function overview(): View
    {
        $doctor = auth()->user()->doctor()->firstOrFail();
        $doctorId = $doctor->id;

        $readyResearchesQuery = LabResearch::query()
            ->where('status', 'ready')
            ->whereNull('result_showed_at')
            ->whereHas('results');

        $this->applyDoctorScope($readyResearchesQuery, $doctorId);

        $newResearchesResultCount = (clone $readyResearchesQuery)->count();

        $researches = (clone $readyResearchesQuery)
            ->with($this->labResearchRelations())
            ->get();

        $researches->each(function (LabResearch $research): void {
            $this->decorateLabResearchAttention($research);
        });

        $isProgressResearchesResultsQuery = LabResearch::query()
            ->where('status', 'processing');

        $this->applyDoctorScope(
            $isProgressResearchesResultsQuery,
            $doctorId
        );

        $isProgressResearchesResultsCount =
            $isProgressResearchesResultsQuery->count();

        $outdatedResearchesQuery = LabResearch::query()
            ->whereIn('status', ['ordered', 'processing'])
            ->where('planned_at', '<=', now());

        $this->applyDoctorScope(
            $outdatedResearchesQuery,
            $doctorId
        );

        $outdatedResearchesCount = $outdatedResearchesQuery->count();

        $criticalResearches = $researches
            ->where('attention_level', 'critical')
            ->values();

        $criticalResearchesResultCount = $criticalResearches->count();

        $attentionItems = $researches
            ->filter(function (LabResearch $research): bool {
                return in_array(
                    $research->attention_level,
                    ['critical', 'warning'],
                    true
                );
            })
            ->sortByDesc(function (LabResearch $research) {
                return $research->research_date
                    ?? $research->updated_at
                    ?? $research->created_at;
            })
            ->values();

        $recentResults = $researches
            ->sortByDesc(function (LabResearch $research) {
                return $research->research_date
                    ?? $research->updated_at
                    ?? $research->created_at;
            })
            ->take(10)
            ->map(function (LabResearch $research): LabResearch {
                $resultsCount = $research->results->count();

                $research->setAttribute(
                    'result_url',
                    route('doctors.patients.medical_card', [
                        'patient' => $research->patient_id,
                        'tab' => 'labs',
                        'research' => $research->id,
                    ])
                );

                if ($research->attention_level === 'critical') {
                    $summaryValue = 'Критическое значение';

                    $summaryText = trim(
                        $research->attention_parameter_name
                        . ': '
                        . $research->attention_value
                        . (
                        $research->attention_unit
                            ? ' ' . $research->attention_unit
                            : ''
                        )
                    );

                    $valueClass = 'text-danger';
                    $statusLabel = 'Критично';
                    $statusClass = 'critical';
                } elseif ($research->attention_level === 'warning') {
                    $summaryValue = 'Есть отклонения';

                    $summaryText = trim(
                        $research->attention_parameter_name
                        . ': '
                        . $research->attention_value
                        . (
                        $research->attention_unit
                            ? ' ' . $research->attention_unit
                            : ''
                        )
                    );

                    $valueClass = 'text-warning';
                    $statusLabel = 'Отклонение';
                    $statusClass = 'warning';
                } else {
                    $summaryValue = $resultsCount . ' показателей';
                    $summaryText = 'Результаты готовы к просмотру';
                    $valueClass = 'text-success';
                    $statusLabel = 'Готово';
                    $statusClass = 'ready';
                }

                $resultDate = $research->research_date
                    ?? $research->updated_at
                    ?? $research->created_at;

                $research->setAttribute('summary_value', $summaryValue);
                $research->setAttribute('summary_text', $summaryText);
                $research->setAttribute('value_class', $valueClass);
                $research->setAttribute('status_label', $statusLabel);
                $research->setAttribute('status_class', $statusClass);

                $research->setAttribute(
                    'result_date',
                    $resultDate
                        ? Carbon::parse($resultDate)->format('d.m.Y')
                        : '—'
                );

                $research->setAttribute(
                    'result_time',
                    $resultDate
                        ? Carbon::parse($resultDate)->format('H:i')
                        : ''
                );

                return $research;
            })
            ->values();

        return view(
            'doctors.analyses.overview',
            compact(
                'newResearchesResultCount',
                'criticalResearchesResultCount',
                'isProgressResearchesResultsCount',
                'outdatedResearchesCount',
                'recentResults',
                'attentionItems'
            )
        );
    }

    public function journal(Request $request): View
    {
        $doctor = auth()->user()->doctor()->firstOrFail();
        $doctorId = $doctor->id;

        $instrumentalResearchesQuery = InstrumentalResearch::query()
            ->with([
                'patient',
                'media',
            ]);

        $this->applyDoctorScope(
            $instrumentalResearchesQuery,
            $doctorId
        );

        if ($request->filled('search')) {
            $search = trim((string)$request->search);
            $search = preg_replace('/[.,]+/u', ' ', $search) ?? '';

            $searchParts = preg_split(
                '/\s+/u',
                $search,
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: [];

            $instrumentalResearchesQuery->where(
                function (Builder $query) use ($searchParts): void {
                    foreach ($searchParts as $searchPart) {
                        $searchValue = '%' . $searchPart . '%';

                        $query->where(
                            function (Builder $query) use ($searchValue): void {
                                $query
                                    ->where('name', 'ilike', $searchValue)
                                    ->orWhere('body_area', 'ilike', $searchValue)
                                    ->orWhere('organization', 'ilike', $searchValue)
                                    ->orWhereHas(
                                        'patient',
                                        function (Builder $query) use ($searchValue): void {
                                            $query
                                                ->where('surname', 'ilike', $searchValue)
                                                ->orWhere('name', 'ilike', $searchValue)
                                                ->orWhere('patronym', 'ilike', $searchValue);
                                        }
                                    );
                            }
                        );
                    }
                }
            );
        }

        if ($request->filled('date_from')) {
            $instrumentalResearchesQuery->whereDate(
                'result_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $instrumentalResearchesQuery->whereDate(
                'result_at',
                '<=',
                $request->date_to
            );
        }

        if ($request->filled('status')) {
            $instrumentalResearchesQuery->where(
                'status',
                $request->status
            );
        }

        $instrumentalResearches = $instrumentalResearchesQuery
            ->orderByDesc('result_at')
            ->orderByDesc('created_at')
            ->get()
            ->each(function (InstrumentalResearch $research): void {
                $journalUrl = route(
                    'doctors.patients.medical_card',
                    [
                        'patient' => $research->patient_id,
                        'tab' => 'labs',
                        'section' => 'instrumental',
                        'instrumental_research' => $research->id,
                    ]
                );

                $research->setAttribute(
                    'result_url',
                    $journalUrl
                );

                $research->setAttribute(
                    'journal_url',
                    $journalUrl
                );

                $research->setAttribute(
                    'media_count',
                    $research->media
                        ->where(
                            'collection_name',
                            InstrumentalResearch::MEDIA_COLLECTION_RESULTS
                        )
                        ->count()
                );
            });

        return view(
            'doctors.analyses.journal',
            compact('instrumentalResearches')
        );
    }

    public function critical(): View
    {
        $doctor = auth()->user()
            ->doctor()
            ->firstOrFail();

        $doctorId = $doctor->id;

        /*
        * =====================================================
        * Лабораторные исследования
        * =====================================================
        */
        $labResearchesQuery = LabResearch::query()
            ->where('status', 'ready')
            ->whereHas('results')
            ->with($this->labResearchRelations());

        $this->applyDoctorScope(
            $labResearchesQuery,
            $doctorId
        );

        $criticalLabResearches = $labResearchesQuery
            ->get()
            ->map(function (LabResearch $research): ?LabResearch {
                /*
                * Только настоящие критические показатели.
                */
                $criticalResults = $this->getCriticalLabResults(
                    $research
                );

                /*
                * В критическую очередь анализ попадает только
                * при наличии хотя бы одного критического значения.
                */
                if ($criticalResults === []) {
                    return null;
                }

                /*
                * Исключаем критические параметры из обычных
                * отклонений, чтобы не выводить их дважды.
                */
                $criticalParameterIds = collect($criticalResults)
                    ->pluck('parameter_id')
                    ->map(function ($parameterId): int {
                        return (int)$parameterId;
                    })
                    ->all();

                /*
                * Остальные показатели, вышедшие за обычный
                * референсный интервал.
                */
                $deviationResults = $this->getLabDeviationResults(
                    $research,
                    $criticalParameterIds
                );

                /*
                * Общий список для карточки:
                *
                * 1. критические показатели;
                * 2. остальные отклонения.
                */
                $displayResults = collect($criticalResults)
                    ->map(function (array $result): array {
                        $result['severity'] = 'critical';

                        return $result;
                    })
                    ->concat($deviationResults)
                    ->values()
                    ->all();

                /*
                * Первый критический результат используется
                * только для краткой сводки.
                */
                $firstCriticalResult = $criticalResults[0];

                $criticalDate = $research->research_date
                    ?? $research->collected_at
                    ?? $research->updated_at
                    ?? $research->created_at;

                $research->setAttribute(
                    'research_type',
                    'lab'
                );

                $research->setAttribute(
                    'research_type_label',
                    'Лабораторный анализ'
                );

                $research->setAttribute(
                    'research_name',
                    $research->laboratory
                        ?: 'Лабораторный анализ'
                );

                $research->setAttribute(
                    'attention_level',
                    'critical'
                );

                /*
                * Краткая информация по первому
                * критическому показателю.
                */
                $research->setAttribute(
                    'attention_parameter_name',
                    $firstCriticalResult['parameter_name']
                );

                $research->setAttribute(
                    'attention_value',
                    $firstCriticalResult['value']
                );

                $research->setAttribute(
                    'attention_unit',
                    $firstCriticalResult['unit']
                );

                $research->setAttribute(
                    'attention_reference',
                    $firstCriticalResult['reference']
                );

                $research->setAttribute(
                    'critical_parameter_name',
                    $firstCriticalResult['parameter_name']
                );

                $research->setAttribute(
                    'critical_value',
                    $firstCriticalResult['value']
                );

                $research->setAttribute(
                    'critical_unit',
                    $firstCriticalResult['unit']
                );

                $research->setAttribute(
                    'critical_reference',
                    $firstCriticalResult['reference']
                );

                /*
                * Только критические показатели.
                * Используются для статистики.
                */
                $research->setAttribute(
                    'critical_results',
                    $criticalResults
                );

                $research->setAttribute(
                    'critical_results_count',
                    count($criticalResults)
                );

                $research->setAttribute(
                    'critical_count',
                    count($criticalResults)
                );

                /*
                * Все показатели, которые нужно показать
                * в карточке критического анализа.
                */
                $research->setAttribute(
                    'display_results',
                    $displayResults
                );

                $research->setAttribute(
                    'display_results_count',
                    count($displayResults)
                );

                $research->setAttribute(
                    'deviation_results_count',
                    count($deviationResults)
                );

                $research->setAttribute(
                    'critical_date',
                    $criticalDate
                );

                $research->setAttribute(
                    'sort_date',
                    $criticalDate
                );

                $research->setAttribute(
                    'critical_summary',
                    trim(
                        $firstCriticalResult['parameter_name']
                        . ': '
                        . $firstCriticalResult['value']
                        . (
                        $firstCriticalResult['unit']
                            ? ' ' . $firstCriticalResult['unit']
                            : ''
                        )
                    )
                );

                $journalUrl = route(
                    'doctors.patients.medical_card',
                    [
                        'patient' => $research->patient_id,
                        'tab' => 'labs',
                        'research' => $research->id,
                    ]
                );

                $research->setAttribute(
                    'result_url',
                    $journalUrl
                );

                $research->setAttribute(
                    'journal_url',
                    $journalUrl
                );

                return $research;
            })
            ->filter()
            ->values();

        /*
        * =====================================================
        * Инструментальные исследования
        * =====================================================
        */
        $instrumentalResearchesQuery =
            InstrumentalResearch::query()
                ->where('status', 'ready')
                ->where('is_critical', true)
                ->with([
                    'patient:id,birth_at,gender,name,surname,patronym',
                    'media',
                ]);

        $this->applyDoctorScope(
            $instrumentalResearchesQuery,
            $doctorId
        );

        $criticalInstrumentalResearches =
            $instrumentalResearchesQuery
                ->get()
                ->each(function (
                    InstrumentalResearch $research
                ): void {
                    $criticalDate = $research->result_at
                        ?? $research->performed_at
                        ?? $research->updated_at
                        ?? $research->created_at;

                    $criticalReason =
                        $research->critical_reason
                            ?: 'Исследование отмечено как критическое';

                    $instrumentalResults = [
                        [
                            'parameter_name' => $research->name,
                            'value' => $criticalReason,
                            'unit' => null,
                            'reference' => null,
                            'direction' => null,
                            'severity' => 'critical',
                        ],
                    ];

                    $research->setAttribute(
                        'research_type',
                        'instrumental'
                    );

                    $research->setAttribute(
                        'research_type_label',
                        'Инструментальное исследование'
                    );

                    $research->setAttribute(
                        'research_name',
                        $research->name
                            ?: 'Инструментальное исследование'
                    );

                    $research->setAttribute(
                        'attention_level',
                        'critical'
                    );

                    $research->setAttribute(
                        'critical_parameter_name',
                        $research->name
                    );

                    $research->setAttribute(
                        'critical_value',
                        $criticalReason
                    );

                    $research->setAttribute(
                        'critical_unit',
                        null
                    );

                    $research->setAttribute(
                        'critical_reference',
                        null
                    );

                    $research->setAttribute(
                        'critical_results',
                        $instrumentalResults
                    );

                    $research->setAttribute(
                        'display_results',
                        $instrumentalResults
                    );

                    $research->setAttribute(
                        'critical_results_count',
                        1
                    );

                    $research->setAttribute(
                        'critical_count',
                        1
                    );

                    $research->setAttribute(
                        'display_results_count',
                        1
                    );

                    $research->setAttribute(
                        'deviation_results_count',
                        0
                    );

                    $research->setAttribute(
                        'critical_date',
                        $criticalDate
                    );

                    $research->setAttribute(
                        'sort_date',
                        $criticalDate
                    );

                    $research->setAttribute(
                        'critical_summary',
                        $criticalReason
                    );

                    $research->setAttribute(
                        'media_count',
                        $research->media
                            ->where(
                                'collection_name',
                                InstrumentalResearch::MEDIA_COLLECTION_RESULTS
                            )
                            ->count()
                    );

                    $journalUrl = route(
                        'doctors.patients.medical_card',
                        [
                            'patient' => $research->patient_id,
                            'tab' => 'labs',
                            'section' => 'instrumental',
                            'instrumental_research' => $research->id,
                        ]
                    );

                    $research->setAttribute(
                        'result_url',
                        $journalUrl
                    );

                    $research->setAttribute(
                        'journal_url',
                        $journalUrl
                    );
                })
                ->values();

        /*
        * =====================================================
        * Единая очередь
        * =====================================================
        */
        $criticalResults = $criticalLabResearches
            ->concat($criticalInstrumentalResearches)
            ->sort(function ($left, $right): int {
                /*
                * Непросмотренные располагаются первыми.
                */
                $leftViewed =
                    $left->result_showed_at !== null;

                $rightViewed =
                    $right->result_showed_at !== null;

                if ($leftViewed !== $rightViewed) {
                    return $leftViewed <=> $rightViewed;
                }

                $leftTimestamp = $left->sort_date
                    ? Carbon::parse(
                        $left->sort_date
                    )->getTimestamp()
                    : 0;

                $rightTimestamp = $right->sort_date
                    ? Carbon::parse(
                        $right->sort_date
                    )->getTimestamp()
                    : 0;

                return $rightTimestamp <=> $leftTimestamp;
            })
            ->values();

        $criticalResearches = $criticalResults;

        /*
        * Верхняя кнопка критической очереди открывает
        * медицинскую карту первой приоритетной записи.
        * Коллекция уже отсортирована: сначала непросмотренные,
        * затем более новые результаты.
        */
        $criticalJournalUrl = $criticalResults
            ->first()?->journal_url
            ?? route('doctors.analyses.journal');

        /*
        * =====================================================
        * Статистика просмотра
        * =====================================================
        */
        $viewedCriticalResults = $criticalResults
            ->filter(function ($research): bool {
                return $research->result_showed_at !== null;
            })
            ->values();

        $totalCriticalCount = (int)$criticalResults
            ->sum(function ($research): int {
                return max(
                    1,
                    (int)$research->critical_count
                );
            });

        $viewedCriticalCount = (int)$viewedCriticalResults
            ->sum(function ($research): int {
                return max(
                    1,
                    (int)$research->critical_count
                );
            });

        $unviewedCriticalCount =
            $totalCriticalCount - $viewedCriticalCount;

        /*
        * =====================================================
        * Среднее время реакции
        * =====================================================
        */
        $reactionSeconds = $viewedCriticalResults
            ->map(function ($research): ?int {
                $resultDate = $research->critical_date
                    ?? $research->updated_at
                    ?? $research->created_at;

                $viewedDate = $research->result_showed_at;

                if (!$resultDate || !$viewedDate) {
                    return null;
                }

                $resultDate = Carbon::parse($resultDate);
                $viewedDate = Carbon::parse($viewedDate);

                if ($viewedDate->lessThan($resultDate)) {
                    return null;
                }

                return (int)$resultDate->diffInSeconds(
                    $viewedDate
                );
            })
            ->filter(function (?int $seconds): bool {
                return $seconds !== null;
            })
            ->values();

        $averageReactionSeconds =
            $reactionSeconds->isNotEmpty()
                ? (int)round(
                $reactionSeconds->average()
            )
                : null;

        $averageReactionValue = '—';
        $averageReactionUnit = null;

        if ($averageReactionSeconds !== null) {
            if ($averageReactionSeconds < 60) {
                $averageReactionValue =
                    $averageReactionSeconds;

                $averageReactionUnit = 'сек';
            } elseif ($averageReactionSeconds < 3600) {
                $averageReactionValue = round(
                    $averageReactionSeconds / 60,
                    1
                );

                $averageReactionUnit = 'мин';
            } elseif ($averageReactionSeconds < 86400) {
                $averageReactionValue = round(
                    $averageReactionSeconds / 3600,
                    1
                );

                $averageReactionUnit = 'ч';
            } else {
                $averageReactionValue = round(
                    $averageReactionSeconds / 86400,
                    1
                );

                $averageReactionUnit = 'дн';
            }
        }

        $criticalStatistics = [
            /*
            * Количество настоящих критических значений,
            * а не число обычных отклонений.
            */
            'total' => $totalCriticalCount,

            'laboratory' =>
                $criticalLabResearches->count(),

            'lab' =>
                $criticalLabResearches->count(),

            'instrumental' =>
                $criticalInstrumentalResearches->count(),

            'patients' => $criticalResults
                ->pluck('patient_id')
                ->filter()
                ->unique()
                ->count(),

            'unread' => $unviewedCriticalCount,
            'unviewed' => $unviewedCriticalCount,
            'viewed' => $viewedCriticalCount,

            'average_reaction_seconds' =>
                $averageReactionSeconds,

            'average_reaction_value' =>
                $averageReactionValue,

            'average_reaction_unit' =>
                $averageReactionUnit,
        ];

        return view(
            'doctors.analyses.critical',
            compact(
                'criticalResults',
                'criticalResearches',
                'criticalLabResearches',
                'criticalInstrumentalResearches',
                'criticalStatistics',
                'criticalJournalUrl'
            )
        );
    }

    public function dynamics(Request $request)
    {
        $authUser = auth()->user();

        $currentDoctor = $authUser
            ->doctor()
            ->firstOrFail();

        $currentDoctorId = (int)$currentDoctor->id;
        $canChooseDoctor = app()->hasDebugModeEnabled();

        $patientName = static function (?Patient $patient): string {
            if (!$patient) {
                return '';
            }

            return collect([
                $patient->surname,
                $patient->name,
                $patient->patronym,
            ])
                ->filter(static function ($value): bool {
                    return $value !== null
                        && trim((string)$value) !== '';
                })
                ->implode(' ');
        };

        $doctorName = static function (?Doctor $doctor): string {
            if (!$doctor) {
                return '';
            }

            $doctorUser = method_exists($doctor, 'user')
                ? $doctor->user
                : null;

            $readyName = trim((string)(
            data_get($doctor, 'full_name')
                ?: data_get($doctor, 'fullname')
                ?: data_get($doctor, 'fio')
                    ?: data_get($doctorUser, 'full_name')
                        ?: data_get($doctorUser, 'fullname')
                            ?: data_get($doctorUser, 'fio')
                                ?: ''
            ));

            if ($readyName !== '') {
                return $readyName;
            }

            return collect([
                data_get($doctor, 'surname')
                    ?: data_get($doctorUser, 'surname'),
                data_get($doctor, 'name')
                    ?: data_get($doctorUser, 'name'),
                data_get($doctor, 'patronym')
                    ?: data_get($doctorUser, 'patronym'),
            ])
                ->filter(static function ($value): bool {
                    return $value !== null
                        && trim((string)$value) !== '';
                })
                ->implode(' ');
        };

        $getSearchTerm = static function (Request $request): string {
            return trim((string)(
            $request->input('q')
                ?: $request->input('search')
                ?: $request->input('term')
                    ?: $request->input('query')
                        ?: ''
            ));
        };

        $lookup = (string)$request->query('lookup', '');

        /*
        * AJAX-поиск врачей. В обычном режиме не раскрываем список
        * всех врачей и возвращаем только текущего пользователя.
        */
        if ($lookup === 'doctors') {
            if (!$canChooseDoctor) {
                return response()->json([
                    'results' => [[
                        'id' => $currentDoctorId,
                        'text' => $doctorName($currentDoctor)
                            ?: 'Текущий врач',
                    ]],
                    'pagination' => ['more' => false],
                ]);
            }

            $term = mb_strtolower($getSearchTerm($request));
            $page = max(1, $request->integer('page', 1));
            $perPage = 20;

            $labPatientIdsForDoctors = LabResearch::query()
                ->select('patient_id')
                ->whereNotNull('patient_id');

            $instrumentalPatientIdsForDoctors = InstrumentalResearch::query()
                ->select('patient_id')
                ->whereNotNull('patient_id');

            $availableDoctorIds = Patient::query()
                ->whereNotNull('doctor_id')
                ->where(function (Builder $query) use (
                    $labPatientIdsForDoctors,
                    $instrumentalPatientIdsForDoctors
                ): void {
                    $query
                        ->whereIn('id', $labPatientIdsForDoctors)
                        ->orWhereIn('id', $instrumentalPatientIdsForDoctors);
                })
                ->pluck('doctor_id')
                ->map(static fn($doctorId): int => (int)$doctorId)
                ->unique()
                ->values();

            $doctors = Doctor::query()
                ->whereIn('id', $availableDoctorIds)
                ->limit(1000)
                ->get();

            if (
                $doctors->isNotEmpty()
                && method_exists($doctors->first(), 'user')
            ) {
                $doctors->loadMissing('user');
            }

            $items = $doctors
                ->map(static function (Doctor $doctor) use ($doctorName): array {
                    return [
                        'id' => (int)$doctor->id,
                        'text' => $doctorName($doctor),
                    ];
                })
                ->filter(static function (array $item) use ($term): bool {
                    if ($item['text'] === '') {
                        return false;
                    }

                    return $term === ''
                        || mb_stripos($item['text'], $term) !== false;
                })
                ->sortBy('text')
                ->values();

            $offset = ($page - 1) * $perPage;

            return response()->json([
                'results' => $items
                    ->slice($offset, $perPage)
                    ->values(),
                'pagination' => [
                    'more' => $items->count() > $offset + $perPage,
                ],
            ]);
        }

        /*
        * AJAX-поиск пациентов. Врач является необязательным фильтром.
        * В список попадают пациенты, у которых есть хотя бы один анализ
        * или инструментальное исследование в любом статусе.
        */
        if ($lookup === 'patients') {
            $term = $getSearchTerm($request);
            $page = max(1, $request->integer('page', 1));
            $perPage = 20;

            $lookupDoctorId = $canChooseDoctor
                ? ($request->integer('doctor_id') ?: null)
                : $currentDoctorId;

            if (
                $lookupDoctorId
                && !Doctor::query()->whereKey($lookupDoctorId)->exists()
            ) {
                return response()->json([
                    'results' => [],
                    'pagination' => ['more' => false],
                    'meta' => [
                        'reason' => 'doctor_not_found',
                        'message' => 'Выбранный врач не найден.',
                    ],
                ]);
            }

            /*
            * Пациента можно выбрать сразу, даже если врач ещё не выбран.
            * При выборе врача фильтрация выполняется по лечащему врачу
            * пациента (patients.doctor_id), а не по автору исследования.
            */
            $labPatientIds = LabResearch::query()
                ->select('patient_id')
                ->whereNotNull('patient_id');

            $instrumentalPatientIds = InstrumentalResearch::query()
                ->select('patient_id')
                ->whereNotNull('patient_id');

            $patientsQuery = Patient::query()
                ->select([
                    'id',
                    'doctor_id',
                    'surname',
                    'name',
                    'patronym',
                ])
                ->when(
                    $lookupDoctorId,
                    static function (Builder $query, int $doctorId): void {
                        $query->where('doctor_id', $doctorId);
                    }
                )
                ->where(
                    static function (Builder $query) use (
                        $labPatientIds,
                        $instrumentalPatientIds
                    ): void {
                        $query
                            ->whereIn('id', $labPatientIds)
                            ->orWhereIn('id', $instrumentalPatientIds);
                    }
                );

            $searchParts = preg_split(
                '/\s+/u',
                trim($term),
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: [];

            foreach ($searchParts as $searchPart) {
                $searchValue = '%' . $searchPart . '%';

                $patientsQuery->where(
                    static function (Builder $query) use ($searchValue): void {
                        $query
                            ->where('surname', 'ilike', $searchValue)
                            ->orWhere('name', 'ilike', $searchValue)
                            ->orWhere('patronym', 'ilike', $searchValue);
                    }
                );
            }

            $patients = $patientsQuery
                ->orderBy('surname')
                ->orderBy('name')
                ->orderBy('patronym')
                ->forPage($page, $perPage + 1)
                ->get();

            $hasMore = $patients->count() > $perPage;

            $resultItems = $patients
                ->take($perPage)
                ->map(
                    static function (Patient $patient) use ($patientName): array {
                        return [
                            'id' => (int)$patient->id,
                            'text' => $patientName($patient),
                        ];
                    }
                )
                ->values();

            return response()->json([
                'results' => $resultItems,
                'pagination' => ['more' => $hasMore],
                'meta' => [
                    'doctor_id' => (int)$lookupDoctorId,
                    'items_count' => $resultItems->count(),
                    'reason' => $resultItems->isEmpty()
                        ? 'no_ready_research_patients'
                        : null,
                    'message' => $resultItems->isEmpty()
                        ? ($lookupDoctorId
                            ? 'У выбранного врача нет пациентов с анализами или исследованиями.'
                            : 'Пациенты с анализами или исследованиями не найдены.')
                        : null,
                ],
            ]);
        }

        /*
        * В обычном врачебном кабинете всегда используется текущий врач.
        * В режиме, где разрешён выбор врача, ничего автоматически не
        * подставляем: это исключает появление случайного врача при первом
        * открытии страницы. Врач считается выбранным только после явного
        * выбора пользователем.
        */
        $selectedDoctorId = $canChooseDoctor
            ? ($request->filled('doctor_id')
                ? ($request->integer('doctor_id') ?: null)
                : null)
            : $currentDoctorId;

        $selectedDoctor = null;

        if ($selectedDoctorId) {
            $selectedDoctor = $selectedDoctorId === $currentDoctorId
                ? $currentDoctor
                : Doctor::query()->find($selectedDoctorId);

            if (!$selectedDoctor) {
                $selectedDoctorId = null;
            }
        }

        if (
            $selectedDoctor
            && method_exists($selectedDoctor, 'user')
        ) {
            $selectedDoctor->loadMissing('user');
        }

        $selectedDoctorName = $selectedDoctor
            ? $doctorName($selectedDoctor)
            : '';

        if (
            $selectedDoctorName === ''
            && $selectedDoctorId === $currentDoctorId
        ) {
            $selectedDoctorName = collect([
                data_get($authUser, 'surname'),
                data_get($authUser, 'name'),
                data_get($authUser, 'patronym'),
            ])
                ->filter(static function ($value): bool {
                    return $value !== null
                        && trim((string)$value) !== '';
                })
                ->implode(' ');
        }

        if ($selectedDoctorId && $selectedDoctorName === '') {
            $selectedDoctorName = 'Врач #' . $selectedDoctorId;
        }

        /*
        * Пациент проверяется по наличию анализов или исследований.
        * Выбранный врач, если он задан, фильтрует пациентов по
        * patients.doctor_id.
        */
        $selectedPatientId = $request->integer('patient_id') ?: null;
        $selectedPatient = null;

        if ($selectedPatientId) {
            $labPatientIds = LabResearch::query()
                ->select('patient_id')
                ->whereNotNull('patient_id');

            $instrumentalPatientIds = InstrumentalResearch::query()
                ->select('patient_id')
                ->whereNotNull('patient_id');

            $selectedPatient = Patient::query()
                ->select([
                    'id',
                    'doctor_id',
                    'surname',
                    'name',
                    'patronym',
                    'birth_at',
                    'gender',
                ])
                ->when(
                    $selectedDoctorId,
                    static function (Builder $query, int $doctorId): void {
                        $query->where('doctor_id', $doctorId);
                    }
                )
                ->where(
                    static function (Builder $query) use (
                        $labPatientIds,
                        $instrumentalPatientIds
                    ): void {
                        $query
                            ->whereIn('id', $labPatientIds)
                            ->orWhereIn('id', $instrumentalPatientIds);
                    }
                )
                ->find($selectedPatientId);
        }

        if (!$selectedPatient) {
            $selectedPatientId = null;
        }

        $selectedPatientName = $patientName($selectedPatient);

        $doctorSearchUrl = $request->url()
            . '?'
            . http_build_query(['lookup' => 'doctors']);

        $patientSearchUrl = $request->url()
            . '?'
            . http_build_query(['lookup' => 'patients']);

        $periods = [
            'year' => [
                'label' => '1 год',
                'from' => now()->copy()->subYear()->startOfDay(),
            ],
            'half_year' => [
                'label' => '6 месяцев',
                'from' => now()->copy()->subMonths(6)->startOfDay(),
            ],
            'quarter' => [
                'label' => '3 месяца',
                'from' => now()->copy()->subMonths(3)->startOfDay(),
            ],
            'all' => [
                'label' => 'Весь период',
                'from' => null,
            ],
        ];

        $selectedPeriod = (string)$request->query('period', 'year');

        if (!array_key_exists($selectedPeriod, $periods)) {
            $selectedPeriod = 'year';
        }

        $periodLabel = $periods[$selectedPeriod]['label'];
        $periodFrom = $periods[$selectedPeriod]['from'];

        /*
        * Используем те же исследования, которые отображаются в медицинской
        * карте пациента. Врач является необязательным фильтром.
        */
        $researches = collect();

        if ($selectedPatientId) {
            $researchesQuery = LabResearch::query()
                ->where('status', 'ready')
                ->where('patient_id', $selectedPatientId)
                ->whereHas('results')
                ->with([
                    'doctor:id,name,surname,patronym',
                    'results.parameter',
                ]);

            /*
            * После выбора пациента загружаются все его готовые результаты,
            * независимо от того, какой врач создал или заполнил исследование.
            */
            $researches = $researchesQuery->get();
        }

        /*
        * Общая история выполненных анализов и инструментальных исследований.
        * История загружается всегда. Врач и пациент являются фильтрами.
        */
        $historyRows = collect();
        $addLabUrl = null;
        $addInstrumentalUrl = null;

        if ($selectedPatientId) {
            $addLabUrl = route(
                'doctors.patients.medical_card',
                [
                    'patient' => $selectedPatientId,
                    'tab' => 'labs',
                ]
            );

            $addInstrumentalUrl = route(
                'doctors.patients.medical_card',
                [
                    'patient' => $selectedPatientId,
                    'tab' => 'labs',
                    'section' => 'instrumental',
                ]
            );
        }

        $labHistoryQuery = LabResearch::query()
            ->with([
                'patient:id,name,surname,patronym',
                'doctor:id,name,surname,patronym,status',
                'results.parameter',
            ])
            ->orderByDesc('updated_at')
            ->limit($selectedPatientId ? 500 : 250);

        if ($selectedDoctorId) {
            $labHistoryQuery->whereHas(
                'patient',
                static function (Builder $query) use ($selectedDoctorId): void {
                    $query->where('doctor_id', $selectedDoctorId);
                }
            );
        }

        if ($selectedPatientId) {
            $labHistoryQuery->where('patient_id', $selectedPatientId);
        }

        $labHistoryRows = $labHistoryQuery
            ->get()
            ->flatMap(function (LabResearch $research) use (
                $periodFrom,
                $patientName,
                $doctorName
            ): Collection {
                $dateValue = $research->research_date
                    ?? $research->collected_at
                    ?? $research->result_at
                    ?? $research->updated_at
                    ?? $research->created_at;

                if (!$dateValue) {
                    return collect();
                }

                $date = Carbon::parse($dateValue);

                if ($periodFrom && $date->lt($periodFrom)) {
                    return collect();
                }

                $researchName = $this->resolveLabResearchName($research)
                    ?: 'Лабораторный анализ';

                $materialName = $this->resolveLabMaterialName($research);

                $rowPatientName = $patientName(
                    $research->getRelationValue('patient')
                );

                if ($rowPatientName === '' && $research->patient_id) {
                    $rowPatientName = 'Пациент #' . $research->patient_id;
                }

                $rowDoctorName = $doctorName(
                    $research->getRelationValue('doctor')
                );

                if ($rowDoctorName === '' && $research->doctor_id) {
                    $rowDoctorName = 'Врач #' . $research->doctor_id;
                }

                $rowDoctorAccreditation = trim((string)data_get(
                    $research->getRelationValue('doctor'),
                    'status',
                    ''
                ));

                $statusLabel = $this->resolveResearchStatusLabel(
                    $research->status
                );

                $baseRow = [
                    'type' => 'lab',
                    'type_label' => 'Анализ',
                    'date' => $date->format('d.m.Y'),
                    'timestamp' => $date->getTimestamp(),
                    'status' => (string)($research->status ?? ''),
                    'status_label' => $statusLabel,
                    'doctor_id' => $research->doctor_id
                        ? (int)$research->doctor_id
                        : null,
                    'doctor' => $rowDoctorName !== ''
                        ? $rowDoctorName
                        : 'Не указан',
                    'doctor_accreditation' => $rowDoctorAccreditation !== ''
                        ? $rowDoctorAccreditation
                        : null,
                    'patient_id' => $research->patient_id
                        ? (int)$research->patient_id
                        : null,
                    'patient' => $rowPatientName !== ''
                        ? $rowPatientName
                        : 'Не указан',
                    'url' => route(
                        'doctors.patients.medical_card',
                        [
                            'patient' => $research->patient_id,
                            'tab' => 'labs',
                            'research' => $research->id,
                        ]
                    ),
                    'delete_url' => (
                        (string)($research->status ?? '') === 'ordered'
                    )
                        ? route(
                            'doctors.patients.researches.delete',
                            [
                                'labResearch' => $research->id,
                            ]
                        )
                        : null,
                ];

                if ($research->results->isEmpty()) {
                    return collect([array_merge($baseRow, [
                        'name' => $researchName,
                        'detail' => collect([
                            $materialName,
                            $statusLabel,
                        ])->filter()->unique()->implode(' · '),
                        'result' => $statusLabel,
                    ])]);
                }

                return $research->results
                    ->map(function ($result) use (
                        $researchName,
                        $materialName,
                        $baseRow,
                        $statusLabel
                    ): array {
                        $parameter = $result->parameter;
                        $parameterName = trim((string)($parameter?->name ?? ''));
                        $unit = trim((string)($parameter?->unit ?? ''));
                        $rawValue = trim((string)$result->value);

                        return array_merge($baseRow, [
                            'name' => $parameterName !== ''
                                ? $parameterName
                                : $researchName,
                            'detail' => collect([
                                $researchName,
                                $materialName,
                                $statusLabel,
                            ])->filter()->unique()->implode(' · '),
                            'result' => trim(
                                ($rawValue !== '' ? $rawValue : $statusLabel)
                                . ($rawValue !== '' && $unit !== ''
                                    ? ' ' . $unit
                                    : '')
                            ),
                        ]);
                    });
            });

        $instrumentalHistoryQuery = InstrumentalResearch::query()
            ->with([
                'patient:id,name,surname,patronym',
                'doctor:id,name,surname,patronym,status',
            ])
            ->orderByDesc('updated_at')
            ->limit($selectedPatientId ? 500 : 250);

        if ($selectedDoctorId) {
            $instrumentalHistoryQuery->whereHas(
                'patient',
                static function (Builder $query) use ($selectedDoctorId): void {
                    $query->where('doctor_id', $selectedDoctorId);
                }
            );
        }

        if ($selectedPatientId) {
            $instrumentalHistoryQuery->where(
                'patient_id',
                $selectedPatientId
            );
        }

        $instrumentalHistoryRows = $instrumentalHistoryQuery
            ->get()
            ->map(function (InstrumentalResearch $research) use (
                $periodFrom,
                $patientName,
                $doctorName
            ): ?array {
                $dateValue = $research->result_at
                    ?? $research->performed_at
                    ?? $research->updated_at
                    ?? $research->created_at;

                if (!$dateValue) {
                    return null;
                }

                $date = Carbon::parse($dateValue);

                if ($periodFrom && $date->lt($periodFrom)) {
                    return null;
                }

                $rowPatientName = $patientName(
                    $research->getRelationValue('patient')
                );

                if ($rowPatientName === '' && $research->patient_id) {
                    $rowPatientName = 'Пациент #' . $research->patient_id;
                }

                $rowDoctorName = $doctorName(
                    $research->getRelationValue('doctor')
                );

                if ($rowDoctorName === '' && $research->doctor_id) {
                    $rowDoctorName = 'Врач #' . $research->doctor_id;
                }

                $rowDoctorAccreditation = trim((string)data_get(
                    $research->getRelationValue('doctor'),
                    'status',
                    ''
                ));

                $name = trim((string)($research->name ?? ''));

                $detail = collect([
                    $research->body_area ?? null,
                    $research->organization ?? null,
                    $research->specialist_name ?? null,
                ])->filter()->implode(' · ');

                $result = trim((string)(
                    $research->conclusion
                    ?? $research->description
                    ?? $research->recommendations
                    ?? $research->critical_reason
                    ?? ''
                ));

                $statusLabel = $this->resolveResearchStatusLabel(
                    $research->status
                );

                return [
                    'type' => 'instrumental',
                    'type_label' => 'Исследование',
                    'date' => $date->format('d.m.Y'),
                    'timestamp' => $date->getTimestamp(),
                    'status' => (string)($research->status ?? ''),
                    'status_label' => $statusLabel,
                    'doctor_id' => $research->doctor_id
                        ? (int)$research->doctor_id
                        : null,
                    'doctor' => $rowDoctorName !== ''
                        ? $rowDoctorName
                        : 'Не указан',
                    'doctor_accreditation' => $rowDoctorAccreditation !== ''
                        ? $rowDoctorAccreditation
                        : null,
                    'patient_id' => $research->patient_id
                        ? (int)$research->patient_id
                        : null,
                    'patient' => $rowPatientName !== ''
                        ? $rowPatientName
                        : 'Не указан',
                    'name' => $name !== ''
                        ? $name
                        : 'Инструментальное исследование',
                    'detail' => $detail,
                    'result' => $result !== ''
                        ? $result
                        : $statusLabel,
                    'url' => route(
                        'doctors.patients.medical_card',
                        [
                            'patient' => $research->patient_id,
                            'tab' => 'labs',
                            'section' => 'instrumental',
                            'instrumental_research' => $research->id,
                        ]
                    ),
                    'delete_url' => (
                        (string)($research->status ?? '') === 'ordered'
                        && $research->patient_id
                    )
                        ? route(
                            'doctors.patients.instrumentals.delete',
                            [
                                'patient' => $research->patient_id,
                                'instrumentalResearch' => $research->id,
                            ]
                        )
                        : null,
                ];
            })
            ->filter();

        $historyRows = $labHistoryRows
            ->concat($instrumentalHistoryRows)
            ->sortByDesc('timestamp')
            ->take(300)
            ->values();

        /*
        * На странице динамики показываем только параметры, у которых
        * действительно есть хотя бы одно числовое значение.
        */
        $parameters = $researches
            ->flatMap(static function (LabResearch $research) {
                return $research->results;
            })
            ->filter(function ($result): bool {
                return $this->normalizeLabNumericValue($result->value) !== null
                    && $result->parameter !== null;
            })
            ->map(static function ($result) {
                return $result->parameter;
            })
            ->unique('id')
            ->sortBy(static function ($parameter): string {
                return mb_strtolower((string)$parameter->name);
            })
            ->values();

        /*
        * Краткая сводка по выбранному пациенту нужна ещё до выбора
        * конкретного показателя. Она заполняется реальными исследованиями
        * пациента за выбранный период.
        */
        $researchesInPeriod = $researches
            ->filter(function (LabResearch $research) use ($periodFrom): bool {
                $dateValue = $research->research_date
                    ?? $research->collected_at
                    ?? $research->result_at
                    ?? $research->updated_at
                    ?? $research->created_at;

                if (!$dateValue) {
                    return false;
                }

                $date = Carbon::parse($dateValue);

                return !$periodFrom || $date->gte($periodFrom);
            })
            ->values();

        $latestResearch = $researchesInPeriod
            ->sortByDesc(function (LabResearch $research): int {
                $dateValue = $research->research_date
                    ?? $research->collected_at
                    ?? $research->result_at
                    ?? $research->updated_at
                    ?? $research->created_at;

                return $dateValue
                    ? Carbon::parse($dateValue)->getTimestamp()
                    : 0;
            })
            ->first();

        $latestResearchDateValue = $latestResearch
            ? (
                $latestResearch->research_date
                ?? $latestResearch->collected_at
                ?? $latestResearch->result_at
                ?? $latestResearch->updated_at
                ?? $latestResearch->created_at
            )
            : null;

        $latestHistoryRow = $historyRows->first();

        $patientOverview = [
            'doctor' => $selectedDoctorName,
            'patient' => $selectedPatientName ?: null,
            'period' => $periodLabel,
            'researches_count' => $historyRows
                ->pluck('url')
                ->filter()
                ->unique()
                ->count(),
            'history_count' => $historyRows->count(),
            'lab_history_count' => $historyRows
                ->where('type', 'lab')
                ->count(),
            'instrumental_history_count' => $historyRows
                ->where('type', 'instrumental')
                ->count(),
            'parameters_count' => $parameters->count(),
            'last_research_date' => data_get(
                $latestHistoryRow,
                'date'
            ),
            'last_research' => data_get(
                $latestHistoryRow,
                'name'
            ),
            'last_material' => data_get(
                $latestHistoryRow,
                'detail'
            ),
        ];

        $selectedParameterValue = trim(
            (string)$request->query('parameter', '')
        );

        /*
        * Для обратной совместимости принимается и ID, и старый code,
        * но в форме далее используется стабильный ID параметра.
        */
        $selectedParameter = $parameters->first(
            static function ($parameter) use ($selectedParameterValue): bool {
                if ($selectedParameterValue === '') {
                    return false;
                }

                return (string)$parameter->id === $selectedParameterValue
                    || (
                        trim((string)($parameter->code ?? '')) !== ''
                        && (string)$parameter->code === $selectedParameterValue
                    );
            }
        );

        $selectedParameterId = $selectedParameter
            ? (int)$selectedParameter->id
            : null;

        $selectedParameterName = $selectedParameter?->name;
        $selectedParameterUnit = trim(
            (string)($selectedParameter?->unit ?? '')
        );

        $chartPoints = collect();

        if ($selectedParameter) {
            foreach ($researches as $research) {
                $researchDateValue = $research->research_date
                    ?? $research->collected_at
                    ?? $research->result_at
                    ?? $research->updated_at
                    ?? $research->created_at;

                if (!$researchDateValue) {
                    continue;
                }

                $researchDate = Carbon::parse($researchDateValue);

                if ($periodFrom && $researchDate->lt($periodFrom)) {
                    continue;
                }

                foreach ($research->results as $result) {
                    if (
                        (int)$result->lab_parameter_id
                        !== (int)$selectedParameter->id
                    ) {
                        continue;
                    }

                    $numericValue = $this->normalizeLabNumericValue(
                        $result->value
                    );

                    if ($numericValue === null) {
                        continue;
                    }

                    $rawValue = trim((string)$result->value);

                    $chartPoints->push([
                        'result_id' => (int)$result->id,
                        'research_id' => (int)$research->id,
                        'value' => $numericValue,
                        'raw_value' => $rawValue,
                        'date' => $researchDate->format('d.m.Y'),
                        'label' => $researchDate->format('d.m.Y'),
                        'timestamp' => $researchDate->getTimestamp(),
                        'research' => $this->resolveLabResearchName($research),
                        'material' => $this->resolveLabMaterialName($research),
                    ]);
                }
            }
        }

        $chartPoints = $chartPoints
            ->sortBy('timestamp')
            ->values();

        $firstPoint = $chartPoints->first();
        $lastPoint = $chartPoints->last();
        $numericValues = $chartPoints->pluck('value')->values();

        $summaryDelta = null;
        $summaryPercent = null;
        $trendLabel = 'Недостаточно данных';
        $trendClass = 'neutral';

        if ($chartPoints->count() >= 2) {
            $firstValue = (float)data_get($firstPoint, 'value');
            $lastValue = (float)data_get($lastPoint, 'value');
            $summaryDelta = $lastValue - $firstValue;

            if (abs($firstValue) > 0.000001) {
                $summaryPercent =
                    ($summaryDelta / abs($firstValue)) * 100;
            }

            if (abs($summaryDelta) < 0.000001) {
                $trendLabel = 'Без изменений';
                $trendClass = 'stable';
            } elseif ($summaryDelta > 0) {
                $trendLabel = 'Рост';
                $trendClass = 'up';
            } else {
                $trendLabel = 'Снижение';
                $trendClass = 'down';
            }
        }

        /*
        * Референс рассчитывается для возраста пациента на дату последнего
        * результата. Это важно для возрастных и половых норм.
        */
        $referenceMin = null;
        $referenceMax = null;
        $summaryReference = null;

        if ($selectedPatient && $selectedParameter) {
            $normalValues = $selectedParameter->normal_values ?? [];

            if (is_string($normalValues)) {
                $normalValues = json_decode($normalValues, true) ?: [];
            }

            if (is_array($normalValues) && $normalValues !== []) {
                $referenceDate = data_get($lastPoint, 'timestamp')
                    ? Carbon::createFromTimestamp(
                        (int)data_get($lastPoint, 'timestamp')
                    )
                    : now();

                $patientAge = $selectedPatient->birth_at
                    ? (int)Carbon::parse($selectedPatient->birth_at)
                        ->diffInYears($referenceDate)
                    : null;

                $patientSex = $this->normalizePatientSexForLab(
                    $selectedPatient
                );

                $normalRange = $this->findApplicableNormalRange(
                    $normalValues,
                    $patientAge,
                    $patientSex
                );

                if ($normalRange) {
                    $referenceMin = $normalRange['min_value']
                        ?? $normalRange['min']
                        ?? null;

                    $referenceMax = $normalRange['max_value']
                        ?? $normalRange['max']
                        ?? null;

                    if ($referenceMin !== null && $referenceMax !== null) {
                        $summaryReference = $referenceMin
                            . '–'
                            . $referenceMax;
                    } elseif ($referenceMin !== null) {
                        $summaryReference = 'от ' . $referenceMin;
                    } elseif ($referenceMax !== null) {
                        $summaryReference = 'до ' . $referenceMax;
                    }
                }
            }
        }

        $lastStatus = 'unknown';
        $lastStatusLabel = 'Референс не задан';

        if ($lastPoint && ($referenceMin !== null || $referenceMax !== null)) {
            $lastValue = (float)data_get($lastPoint, 'value');

            if (
                $referenceMin !== null
                && $lastValue < (float)$referenceMin
            ) {
                $lastStatus = 'low';
                $lastStatusLabel = 'Ниже референса';
            } elseif (
                $referenceMax !== null
                && $lastValue > (float)$referenceMax
            ) {
                $lastStatus = 'high';
                $lastStatusLabel = 'Выше референса';
            } else {
                $lastStatus = 'normal';
                $lastStatusLabel = 'В пределах референса';
            }
        }

        $researchNames = $chartPoints
            ->pluck('research')
            ->filter()
            ->unique()
            ->values();

        $materialNames = $chartPoints
            ->pluck('material')
            ->filter()
            ->unique()
            ->values();

        $summaryResearch = match (true) {
            $researchNames->count() === 1 => $researchNames->first(),
            $researchNames->count() > 1 => 'Несколько исследований',
            default => null,
        };

        $summaryMaterial = match (true) {
            $materialNames->count() === 1 => $materialNames->first(),
            $materialNames->count() > 1 => 'Несколько материалов',
            default => null,
        };

        $summary = [
            'patient' => $selectedPatientName ?: null,
            'parameter' => $selectedParameterName,
            'research' => $summaryResearch,
            'material' => $summaryMaterial,
            'period' => $periodLabel,
            'unit' => $selectedParameterUnit,
            'points_count' => $chartPoints->count(),
            'minimum' => $numericValues->isNotEmpty()
                ? (float)$numericValues->min()
                : null,
            'maximum' => $numericValues->isNotEmpty()
                ? (float)$numericValues->max()
                : null,
            'average' => $numericValues->isNotEmpty()
                ? (float)$numericValues->average()
                : null,
            'first' => [
                'value' => data_get($firstPoint, 'raw_value'),
                'numeric_value' => data_get($firstPoint, 'value'),
                'date' => data_get($firstPoint, 'date'),
            ],
            'last' => [
                'value' => data_get($lastPoint, 'raw_value'),
                'numeric_value' => data_get($lastPoint, 'value'),
                'date' => data_get($lastPoint, 'date'),
                'status' => $lastStatus,
                'status_label' => $lastStatusLabel,
            ],
            'reference' => [
                'label' => $summaryReference,
                'min' => $referenceMin !== null
                    ? (float)$referenceMin
                    : null,
                'max' => $referenceMax !== null
                    ? (float)$referenceMax
                    : null,
            ],
            'trend' => [
                'label' => $trendLabel,
                'class' => $trendClass,
            ],
            'change' => [
                'delta' => $summaryDelta,
                'percent' => $summaryPercent,
            ],
        ];

        $presentationData = $this->buildDynamicsPresentationData(
            $chartPoints,
            $summary,
            $patientOverview,
            $historyRows
        );

        return view(
            'doctors.analyses.dynamics',
            array_merge(
                [
                    'canChooseDoctor' => $canChooseDoctor,
                    'doctorSearchUrl' => $doctorSearchUrl,
                    'patientSearchUrl' => $patientSearchUrl,
                    'selectedDoctorId' => $selectedDoctorId,
                    'selectedDoctorName' => $selectedDoctorName,
                    'selectedPatientId' => $selectedPatientId,
                    'selectedPatient' => $selectedPatient,
                    'selectedPatientName' => $selectedPatientName,
                    'selectedPeriod' => $selectedPeriod,
                    'periodLabel' => $periodLabel,
                    'parameters' => $parameters,
                    'selectedParameterValue' => $selectedParameterValue,
                    'selectedParameterId' => $selectedParameterId,
                    'selectedParameterName' => $selectedParameterName,
                    'selectedParameterUnit' => $selectedParameterUnit,
                    'chartPoints' => $chartPoints,
                    'summary' => $summary,
                    'patientOverview' => $patientOverview,
                    'historyRows' => $historyRows,
                    'addLabUrl' => $addLabUrl,
                    'addInstrumentalUrl' => $addInstrumentalUrl,
                ],
                $presentationData
            )
        );
    }

    /**
     * Выполняет поиск пациентов для формы нового направления.
     *
     * @param Request $request HTTP-запрос.
     *
     * @return JsonResponse
     */
    public function assignmentSearchPatients(
        Request $request
    ): JsonResponse {
        $search = trim((string) $request->query('q', ''));

        $query = Patient::query()
            ->select([
                'id',
                'surname',
                'name',
                'patronym',
                'birth_at',
            ]);

        $tokens = preg_split(
            '/\s+/u',
            $search,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        foreach ($tokens as $token) {
            $searchValue = '%' . $token . '%';

            $query->where(
                static function (Builder $patientQuery) use (
                    $searchValue
                ): void {
                    $patientQuery
                        ->where('surname', 'ilike', $searchValue)
                        ->orWhere('name', 'ilike', $searchValue)
                        ->orWhere('patronym', 'ilike', $searchValue);
                }
            );
        }

        $patients = $query
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->limit(20)
            ->get()
            ->map(function (Patient $patient): array {
                return [
                    'id' => (int) $patient->id,
                    'text' => $this->resolveAssignmentPersonName(
                        $patient,
                        'Пациент #' . $patient->id
                    ),
                    'birth_at' => $patient->birth_at
                        ? Carbon::parse($patient->birth_at)
                            ->format('d.m.Y')
                        : null,
                ];
            })
            ->values();

        return response()->json([
            'results' => $patients,
        ]);
    }

    /**
     * Выполняет поиск показателей по выбранному биоматериалу.
     *
     * @param Request $request HTTP-запрос.
     *
     * @return JsonResponse
     */
    public function assignmentSearchParameters(
        Request $request
    ): JsonResponse {
        $search = trim((string) $request->query('q', ''));
        $sampleType = trim(
            (string) $request->query('sample_type', '')
        );

        if ($sampleType === '') {
            return response()->json(['results' => []]);
        }

        $query = LabParameter::query()
            ->where('sample_type', $sampleType)
            ->select([
                'id',
                'name',
                'unit',
                'group',
                'sample_type',
            ]);

        if ($search !== '') {
            $query->where(
                static function (Builder $parameterQuery) use (
                    $search
                ): void {
                    $searchValue = '%' . $search . '%';

                    $parameterQuery
                        ->where('name', 'ilike', $searchValue)
                        ->orWhere('group', 'ilike', $searchValue);
                }
            );
        }

        $parameters = $query
            ->orderBy('group')
            ->orderBy('name')
            ->limit(40)
            ->get()
            ->map(static function (LabParameter $parameter): array {
                return [
                    'id' => (int) $parameter->id,
                    'name' => (string) $parameter->name,
                    'text' => (string) $parameter->name,
                    'unit' => (string) ($parameter->unit ?? ''),
                    'group' => (string) ($parameter->group ?? ''),
                    'sample_type' =>
                        (string) ($parameter->sample_type ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'results' => $parameters,
        ]);
    }

    /**
     * Показывает список назначений лабораторных исследований.
     *
     * @param Request $request HTTP-запрос.
     *
     * @return View
     */
    public function assignments(Request $request): View
    {
        $doctor = $request->user()
            ->doctor()
            ->firstOrFail();

        $query = LabResearch::query()
            ->with([
                'patient:id,surname,name,patronym,birth_at',
                'doctor:id,surname,name,patronym,clinic_id',
            ])
            ->whereIn('status', [
                self::ASSIGNMENT_STATUS_ORDERED,
                self::ASSIGNMENT_STATUS_PROCESSING,
                self::ASSIGNMENT_STATUS_READY,
            ]);

        $this->applyDoctorScope($query, (int) $doctor->id);
        $this->applyAssignmentFilters($query, $request);

        $assignments = $query
            ->orderByDesc('created_at')
            ->paginate(self::ASSIGNMENT_PER_PAGE)
            ->withQueryString();

        $parameterIds = collect($assignments->items())
            ->flatMap(
                static fn (LabResearch $research): array =>
                (array) ($research->parameters ?? [])
            )
            ->map(static fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $parametersById = $parameterIds->isEmpty()
            ? collect()
            : LabParameter::query()
                ->whereKey($parameterIds)
                ->get(['id', 'name'])
                ->keyBy('id');

        $assignments->through(
            fn (LabResearch $research): array =>
            $this->buildAssignmentRow(
                $research,
                $parametersById
            )
        );

        $rows = collect($assignments->items());
        $oldSelectedParameters =
            $this->resolveOldAssignmentParameters($request);

        $sampleTypes = LabParameter::query()
            ->whereNotNull('sample_type')
            ->where('sample_type', '<>', '')
            ->distinct()
            ->orderBy('sample_type')
            ->pluck('sample_type')
            ->values();

        $assignmentModalOpen = $request->boolean('create')
            || $request->session()->has('errors');

        return view(
            'doctors.analyses.assignments',
            compact(
                'assignments',
                'rows',
                'oldSelectedParameters',
                'sampleTypes',
                'assignmentModalOpen'
            ) + [
                'patientSearchUrl' => route(
                    'doctors.analyses.assignments.patients.search'
                ),
                'parameterSearchUrl' => route(
                    'doctors.analyses.assignments.parameters.search'
                ),
                'assignmentStoreUrl' => route(
                    'doctors.analyses.assignments.store'
                ),
                'defaultPlannedAt' => now()->toDateString(),
            ]
        );
    }

    /**
     * Сохраняет новое лабораторное направление.
     *
     * @param Request $request HTTP-запрос.
     *
     * @return RedirectResponse
     */
    public function assignmentsStore(
        Request $request
    ): RedirectResponse {
        $data = $request->validateWithBag(
            'assignment',
            [
                'patient_id' => [
                    'required',
                    'integer',
                    'exists:patients,id',
                ],
                'sample_type' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'parameter_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],
                'parameter_ids.*' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:lab_parameters,id',
                ],
                'laboratory' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'planned_at' => [
                    'required',
                    'date',
                ],
                'priority' => [
                    'required',
                    'in:normal,urgent',
                ],
                'comment' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
                'submit_action' => [
                    'nullable',
                    'in:save,save_print',
                ],
            ],
            [
                'patient_id.required' => 'Выберите пациента.',
                'patient_id.exists' => 'Выбранный пациент не найден.',
                'sample_type.required' => 'Выберите биоматериал.',
                'parameter_ids.required' =>
                    'Добавьте хотя бы один показатель.',
                'parameter_ids.min' =>
                    'Добавьте хотя бы один показатель.',
                'planned_at.required' =>
                    'Укажите планируемую дату сдачи.',
            ]
        );

        $doctor = $request->user()
            ->doctor()
            ->firstOrFail();

        $parameterIds = collect($data['parameter_ids'])
            ->map(static fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $parameters = LabParameter::query()
            ->whereKey($parameterIds)
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        $wrongMaterialCount = $parameters
            ->filter(
                static fn (LabParameter $parameter): bool =>
                    (string) $parameter->sample_type
                    !== (string) $data['sample_type']
            )
            ->count();

        if (
            $parameters->count() !== $parameterIds->count()
            || $wrongMaterialCount > 0
        ) {
            return back()
                ->withErrors(
                    [
                        'parameter_ids' =>
                            'Некоторые показатели не относятся к выбранному биоматериалу.',
                    ],
                    'assignment'
                )
                ->withInput();
        }

        $title = trim((string) ($data['laboratory'] ?? ''));

        $labResearch = DB::transaction(
            function () use (
                $data,
                $doctor,
                $parameterIds,
                $parameters,
                $title
            ): LabResearch {
                $research = LabResearch::query()->create([
                    'patient_id' => (int) $data['patient_id'],
                    'doctor_id' => (int) $doctor->id,
                    'planned_at' => $data['planned_at'],
                    'status' => self::ASSIGNMENT_STATUS_ORDERED,
                    'priority' => $data['priority']
                        ?? self::ASSIGNMENT_PRIORITY_NORMAL,
                    'laboratory' => $title !== ''
                        ? $title
                        : $this->buildDefaultAssignmentTitle(
                            $parameters
                        ),
                    'comment' => $data['comment'] ?? null,
                    'parameters' => $parameterIds->all(),
                    'sample_type' => $data['sample_type'],
                    'result_showed_at' => null,
                ]);

                if (
                    $title === ''
                    && trim((string) $research->laboratory) === ''
                ) {
                    $research->laboratory =
                        'Лабораторное направление №' . $research->id;
                    $research->save();
                }

                return $research;
            }
        );

        if (($data['submit_action'] ?? 'save') === 'save_print') {
            return redirect()->route(
                'doctors.analyses.assignments.print',
                [
                    'labResearch' => $labResearch->id,
                    'auto' => 1,
                ]
            );
        }

        return redirect()
            ->route('doctors.analyses.assignments')
            ->with(
                'success',
                'Лабораторное направление сохранено.'
            );
    }

    /**
     * Показывает печатную форму лабораторного направления.
     *
     * @param Request $request HTTP-запрос.
     * @param LabResearch $labResearch Назначение.
     *
     * @return View
     */
    public function assignmentPrint(
        Request $request,
        LabResearch $labResearch
    ): View {
        $this->authorizeAssignmentAccess($labResearch);

        $labResearch->loadMissing([
            'patient',
            'doctor',
        ]);

        $parameterIds = collect($labResearch->parameters ?? [])
            ->map(static fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $parameters = LabParameter::query()
            ->whereKey($parameterIds)
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        $parameterGroups = $parameters
            ->groupBy(
                static fn (LabParameter $parameter): string =>
                trim((string) ($parameter->group ?? ''))
                    ?: 'Лабораторные показатели'
            );

        $doctor = $labResearch->doctor;
        $clinic = $doctor && method_exists($doctor, 'clinic')
            ? $doctor->clinic()->first()
            : null;

        return view(
            'doctors.analyses.assignment-print',
            compact(
                'labResearch',
                'parameters',
                'parameterGroups',
                'clinic'
            ) + [
                'patientName' =>
                    $this->resolveAssignmentPersonName(
                        $labResearch->patient,
                        'Пациент #' . $labResearch->patient_id
                    ),
                'doctorName' =>
                    $this->resolveAssignmentPersonName(
                        $doctor,
                        'Врач #' . $labResearch->doctor_id
                    ),
                'priorityLabel' =>
                    (string) $labResearch->priority
                    === self::ASSIGNMENT_PRIORITY_URGENT
                        ? 'Срочно'
                        : 'Обычный порядок',
                'patientBirthDate' =>
                    $labResearch->patient?->birth_at
                        ? Carbon::parse(
                        $labResearch->patient->birth_at
                    )->format('d.m.Y')
                        : '—',
                'printedAt' => now()->format('d.m.Y H:i'),
                'autoPrint' => $request->boolean('auto'),
            ]
        );
    }


    /**
     * Применяет фильтры к списку лабораторных назначений.
     *
     * @param Builder<LabResearch> $query Запрос назначений.
     * @param Request $request HTTP-запрос.
     *
     * @return void
     */
    private function applyAssignmentFilters(
        Builder $query,
        Request $request
    ): void {
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $tokens = preg_split(
                '/\s+/u',
                $search,
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: [];

            foreach ($tokens as $token) {
                $searchValue = '%' . $token . '%';

                $query->where(
                    static function (Builder $filterQuery) use (
                        $searchValue
                    ): void {
                        $filterQuery
                            ->where('laboratory', 'ilike', $searchValue)
                            ->orWhereHas(
                                'patient',
                                static function (Builder $patientQuery) use (
                                    $searchValue
                                ): void {
                                    $patientQuery
                                        ->where('surname', 'ilike', $searchValue)
                                        ->orWhere('name', 'ilike', $searchValue)
                                        ->orWhere('patronym', 'ilike', $searchValue);
                                }
                            );
                    }
                );
            }
        }

        $stage = trim((string) $request->query('stage', ''));

        match ($stage) {
            'assigned' => $query
                ->where('status', self::ASSIGNMENT_STATUS_ORDERED)
                ->whereDate('planned_at', '>=', today()),
            'processing' => $query->where(
                'status',
                self::ASSIGNMENT_STATUS_PROCESSING
            ),
            'ready' => $query->where(
                'status',
                self::ASSIGNMENT_STATUS_READY
            ),
            'overdue' => $query
                ->whereIn('status', [
                    self::ASSIGNMENT_STATUS_ORDERED,
                    self::ASSIGNMENT_STATUS_PROCESSING,
                ])
                ->whereDate('planned_at', '<', today()),
            default => null,
        };

        $period = trim((string) $request->query('period', ''));

        match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->where(
                'created_at',
                '>=',
                now()->subDays(7)
            ),
            'month' => $query->where(
                'created_at',
                '>=',
                now()->subDays(30)
            ),
            default => null,
        };
    }

    /**
     * Возвращает отображаемое ФИО пациента или врача.
     *
     * @param object|null $person Пациент или врач.
     * @param string $fallback Резервная подпись.
     *
     * @return string
     */
    private function resolveAssignmentPersonName(
        ?object $person,
        string $fallback
    ): string {
        if (!$person) {
            return $fallback;
        }

        $readyName = trim((string) (
        data_get($person, 'full_name')
            ?: data_get($person, 'fullName')
            ?: data_get($person, 'fio')
                ?: ''
        ));

        if ($readyName !== '') {
            return $readyName;
        }

        $name = collect([
            data_get($person, 'surname'),
            data_get($person, 'name'),
            data_get($person, 'patronym'),
        ])
            ->filter(static fn ($value): bool => filled($value))
            ->implode(' ');

        return $name !== '' ? $name : $fallback;
    }

    /**
     * Возвращает этап назначения для интерфейса.
     *
     * @param LabResearch $research Назначение.
     *
     * @return array{key:string,label:string,class:string,index:int}
     */
    private function resolveAssignmentStage(
        LabResearch $research
    ): array {
        $plannedAt = $research->planned_at
            ? Carbon::parse($research->planned_at)
            : null;

        $isOverdue = in_array(
                (string) $research->status,
                [
                    self::ASSIGNMENT_STATUS_ORDERED,
                    self::ASSIGNMENT_STATUS_PROCESSING,
                ],
                true
            )
            && $plannedAt
            && $plannedAt->isBefore(today());

        if ($isOverdue) {
            return [
                'key' => 'overdue',
                'label' => 'Просрочено',
                'class' => 'danger',
                'index' => (string) $research->status
                === self::ASSIGNMENT_STATUS_PROCESSING
                    ? 3
                    : 1,
            ];
        }

        return match ((string) $research->status) {
            self::ASSIGNMENT_STATUS_PROCESSING => [
                'key' => 'processing',
                'label' => 'Выполняется',
                'class' => 'progress',
                'index' => 3,
            ],
            self::ASSIGNMENT_STATUS_READY => [
                'key' => 'ready',
                'label' => 'Готово',
                'class' => 'success',
                'index' => 4,
            ],
            default => [
                'key' => 'assigned',
                'label' => 'Назначено',
                'class' => 'progress',
                'index' => 1,
            ],
        };
    }

    /**
     * Формирует подсказку по сроку выполнения назначения.
     *
     * @param LabResearch $research Назначение.
     *
     * @return string
     */
    private function resolveAssignmentDueHint(
        LabResearch $research
    ): string {
        if (!$research->planned_at) {
            return 'Срок не указан';
        }

        $plannedAt = Carbon::parse($research->planned_at)->startOfDay();
        $today = today();

        if ($plannedAt->isBefore($today)) {
            return 'Просрочено на '
                . $plannedAt->diffInDays($today)
                . ' дн.';
        }

        if ($plannedAt->isSameDay($today)) {
            return 'Срок сегодня';
        }

        return 'Осталось '
            . $today->diffInDays($plannedAt)
            . ' дн.';
    }

    /**
     * Собирает строку назначения для Blade-шаблона.
     *
     * @param LabResearch $research Назначение.
     * @param Collection<int, LabParameter> $parametersById Показатели по ID.
     *
     * @return array<string, mixed>
     */
    private function buildAssignmentRow(
        LabResearch $research,
        Collection $parametersById
    ): array {
        $parameterIds = collect($research->parameters ?? [])
            ->map(static fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $parameterNames = $parameterIds
            ->map(
                static fn (int $id): ?string =>
                $parametersById->get($id)?->name
            )
            ->filter()
            ->values();

        $patientName = $this->resolveAssignmentPersonName(
            $research->patient,
            'Пациент #' . $research->patient_id
        );

        $doctorName = $this->resolveAssignmentPersonName(
            $research->doctor,
            'Врач #' . $research->doctor_id
        );

        $stage = $this->resolveAssignmentStage($research);

        $initials = collect(
            preg_split('/\s+/u', $patientName) ?: []
        )
            ->filter()
            ->take(2)
            ->map(
                static fn (string $part): string =>
                mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('');

        return [
            'id' => (int) $research->id,
            'patient_name' => $patientName,
            'patient_birth_date' => $research->patient?->birth_at
                ? Carbon::parse($research->patient->birth_at)
                    ->format('d.m.Y')
                : 'Дата рождения не указана',
            'initials' => $initials !== '' ? $initials : 'П',
            'research_name' => $research->laboratory
                ?: 'Лабораторное направление №' . $research->id,
            'material' => collect([
                $research->sample_type ?: null,
                $parameterNames->count() . ' показателей',
            ])->filter()->implode(' · '),
            'parameter_preview' => $parameterNames
                ->take(3)
                ->implode(', '),
            'parameter_more_count' => max(
                0,
                $parameterNames->count() - 3
            ),
            'assigned_date' => $research->created_at
                ? Carbon::parse($research->created_at)
                    ->format('d.m.Y H:i')
                : '—',
            'assigned_by' => $doctorName,
            'due_date' => $research->planned_at
                ? Carbon::parse($research->planned_at)->format('d.m.Y')
                : '—',
            'due_hint' => $this->resolveAssignmentDueHint($research),
            'priority_label' => (string) $research->priority
            === self::ASSIGNMENT_PRIORITY_URGENT
                ? 'Срочно'
                : 'Обычно',
            'stage' => $stage['key'],
            'stage_label' => $stage['label'],
            'status_class' => $stage['class'],
            'stage_index' => $stage['index'],
            'medical_card_url' => route(
                'doctors.patients.medical_card',
                [
                    'patient' => $research->patient_id,
                    'tab' => 'labs',
                    'research' => $research->id,
                ]
            ),
            'assignment_url' => route(
                'doctors.patients.medical_card',
                [
                    'patient' => $research->patient_id,
                    'tab' => 'labs',
                    'research' => $research->id,
                ]
            ),
            'print_url' => route(
                'doctors.analyses.assignments.print',
                [
                    'labResearch' => $research->id,
                    'auto' => 1,
                ]
            ),
        ];
    }

    /**
     * Получает показатели, восстановленные после ошибки валидации.
     *
     * @param Request $request HTTP-запрос.
     *
     * @return Collection<int, LabParameter>
     */
    private function resolveOldAssignmentParameters(
        Request $request
    ): Collection {
        $parameterIds = collect(
            $request->session()->getOldInput('parameter_ids', [])
        )
            ->map(static fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($parameterIds->isEmpty()) {
            return collect();
        }

        return LabParameter::query()
            ->whereKey($parameterIds)
            ->orderBy('group')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'unit',
                'group',
                'sample_type',
            ]);
    }

    /**
     * Проверяет доступ текущего врача к направлению.
     *
     * @param LabResearch $research Назначение.
     *
     * @return void
     */
    private function authorizeAssignmentAccess(
        LabResearch $research
    ): void {
        $doctorId = (int) auth()->user()
            ->doctor()
            ->value('id');

        abort_if($doctorId <= 0, 403);

        if (!app()->hasDebugModeEnabled()) {
            abort_unless(
                (int) $research->doctor_id === $doctorId,
                403
            );
        }
    }

    /**
     * Формирует автоматическое название направления.
     *
     * @param Collection<int, LabParameter> $parameters Показатели.
     *
     * @return string
     */
    private function buildDefaultAssignmentTitle(
        Collection $parameters
    ): string {
        if ($parameters->count() === 1) {
            return (string) $parameters->first()->name;
        }

        if ($parameters->isNotEmpty()) {
            return 'Лабораторное направление: '
                . $parameters->first()->name
                . ' и ещё '
                . ($parameters->count() - 1);
        }

        return '';
    }

    private function resolveResearchStatusLabel(?string $status): string {
        $normalizedStatus = mb_strtolower(trim((string)$status));

        return match ($normalizedStatus) {
            'ready', 'completed', 'done' => 'Выполнен',
            'processing', 'in_progress' => 'В работе',
            'ordered', 'assigned', 'appointed', 'pending' => 'Назначен',
            'cancelled', 'canceled' => 'Отменён',
            'draft' => 'Черновик',
            '' => 'Статус не указан',
            default => (string)$status,
        };
    }

    /**
     * Подготавливает все вычисляемые данные для Blade.
     * В представлении не выполняются расчёты и не используются @php-блоки.
     */
    private function buildDynamicsPresentationData(
        Collection $chartPoints,
        array      $summary,
        array      $patientOverview,
        Collection $historyRows
    ): array
    {
        $points = $chartPoints->values();
        $pointCount = $points->count();

        $summaryData = $summary;
        $patientOverviewData = $patientOverview;
        $historyRowsData = $historyRows->values();

        $unit = trim((string)data_get($summaryData, 'unit', ''));
        $referenceLabel = data_get($summaryData, 'reference.label');
        $referenceMin = data_get($summaryData, 'reference.min');
        $referenceMax = data_get($summaryData, 'reference.max');
        $trendClass = (string)data_get(
            $summaryData,
            'trend.class',
            'neutral'
        );
        $trendLabel = (string)data_get(
            $summaryData,
            'trend.label',
            'Недостаточно данных'
        );
        $lastStatus = (string)data_get(
            $summaryData,
            'last.status',
            'unknown'
        );
        $lastStatusLabel = (string)data_get(
            $summaryData,
            'last.status_label',
            'Референс не задан'
        );

        $summaryDelta = data_get($summaryData, 'change.delta');
        $summaryPercent = data_get($summaryData, 'change.percent');

        $changeText = '—';

        if ($summaryDelta !== null && is_numeric($summaryDelta)) {
            $changeText = ((float)$summaryDelta > 0 ? '+' : '')
                . $this->formatDynamicsNumber($summaryDelta)
                . ($unit !== '' ? ' ' . $unit : '');

            if ($summaryPercent !== null && is_numeric($summaryPercent)) {
                $changeText .= ' ('
                    . ((float)$summaryPercent > 0 ? '+' : '')
                    . number_format(
                        (float)$summaryPercent,
                        1,
                        ',',
                        ' '
                    )
                    . '%)';
            }
        }

        $chartWidth = 920;
        $chartHeight = 340;
        $paddingLeft = 66;
        $paddingRight = 30;
        $paddingTop = 28;
        $paddingBottom = 58;
        $plotWidth = $chartWidth - $paddingLeft - $paddingRight;
        $plotHeight = $chartHeight - $paddingTop - $paddingBottom;

        $scaleValues = $points
            ->pluck('value')
            ->filter(static fn($value): bool => is_numeric($value))
            ->map(static fn($value): float => (float)$value)
            ->values();

        if ($referenceMin !== null && is_numeric($referenceMin)) {
            $scaleValues->push((float)$referenceMin);
        }

        if ($referenceMax !== null && is_numeric($referenceMax)) {
            $scaleValues->push((float)$referenceMax);
        }

        $rawMinValue = $scaleValues->isNotEmpty()
            ? (float)$scaleValues->min()
            : 0.0;

        $rawMaxValue = $scaleValues->isNotEmpty()
            ? (float)$scaleValues->max()
            : 1.0;

        $rawRange = $rawMaxValue - $rawMinValue;
        $scalePadding = $rawRange > 0
            ? $rawRange * 0.12
            : max(abs($rawMinValue) * 0.1, 1.0);

        $minValue = $rawMinValue - $scalePadding;
        $maxValue = $rawMaxValue + $scalePadding;
        $range = max(0.000001, $maxValue - $minValue);
        $xDivisor = max(1, $pointCount - 1);

        $mapY = static function ($value) use (
            $paddingTop,
            $plotHeight,
            $minValue,
            $range
        ): float {
            $normalized = ((float)$value - $minValue) / $range;

            return round(
                $paddingTop + $plotHeight - ($plotHeight * $normalized),
                2
            );
        };

        $xLabelStep = max(
            1,
            (int)ceil(max(1, $pointCount - 1) / 6)
        );

        $svgPoints = $points
            ->map(function ($point, $index) use (
                $paddingLeft,
                $plotWidth,
                $xDivisor,
                $mapY,
                $xLabelStep,
                $pointCount,
                $unit
            ): array {
                $value = (float)data_get($point, 'value', 0);
                $x = $paddingLeft
                    + ($plotWidth * ($index / $xDivisor));

                $rawValue = data_get(
                    $point,
                    'raw_value',
                    data_get($point, 'value')
                );

                return [
                    'x' => round($x, 2),
                    'y' => $mapY($value),
                    'value' => $value,
                    'raw_value' => $rawValue,
                    'date' => data_get($point, 'date', ''),
                    'result_text' => $this->formatDynamicsResult(
                        $rawValue,
                        $unit
                    ),
                    'show_label' => $index === 0
                        || $index === $pointCount - 1
                        || $index % $xLabelStep === 0,
                ];
            })
            ->values();

        $polyline = $svgPoints
            ->map(
                static fn(array $point): string => $point['x'] . ',' . $point['y']
            )
            ->implode(' ');

        $tickCount = 4;

        $yTicks = collect(range(0, $tickCount))
            ->map(function ($index) use (
                $tickCount,
                $paddingTop,
                $plotHeight,
                $maxValue,
                $range
            ): array {
                $ratio = $index / $tickCount;
                $value = $maxValue - ($range * $ratio);

                return [
                    'y' => round(
                        $paddingTop + ($plotHeight * $ratio),
                        2
                    ),
                    'value' => $value,
                    'label' => $this->formatDynamicsNumber($value),
                ];
            })
            ->values();

        $referenceBand = null;

        if (
            $referenceMin !== null
            && $referenceMax !== null
            && is_numeric($referenceMin)
            && is_numeric($referenceMax)
        ) {
            $referenceTop = $mapY(
                max((float)$referenceMin, (float)$referenceMax)
            );
            $referenceBottom = $mapY(
                min((float)$referenceMin, (float)$referenceMax)
            );

            $referenceBand = [
                'y' => min($referenceTop, $referenceBottom),
                'height' => max(
                    1,
                    abs($referenceBottom - $referenceTop)
                ),
            ];
        }

        $pointCountLabel = $pointCount
            . ' '
            . $this->dynamicsResultsWord($pointCount);

        $summaryPointsCount = (int)data_get(
            $summaryData,
            'points_count',
            0
        );

        $summaryPointsLabel = $summaryPointsCount
            . ' '
            . $this->dynamicsResultsWord($summaryPointsCount);

        return [
            'pointCount' => $pointCount,
            'pointCountLabel' => $pointCountLabel,
            'summaryPointsLabel' => $summaryPointsLabel,
            'summaryData' => $summaryData,
            'patientOverviewData' => $patientOverviewData,
            'historyRowsData' => $historyRowsData,
            'historyRowsCount' => $historyRowsData->count(),
            'unit' => $unit,
            'referenceLabel' => $referenceLabel,
            'referenceMin' => $referenceMin,
            'referenceMax' => $referenceMax,
            'trendClass' => $trendClass,
            'trendLabel' => $trendLabel,
            'lastStatus' => $lastStatus,
            'lastStatusLabel' => $lastStatusLabel,
            'changeText' => $changeText,
            'lastResultText' => $this->formatDynamicsResult(
                data_get($summaryData, 'last.value'),
                $unit
            ),
            'firstResultText' => $this->formatDynamicsResult(
                data_get($summaryData, 'first.value'),
                $unit
            ),
            'minimumText' => $this->formatDynamicsResult(
                $this->formatDynamicsNumber(
                    data_get($summaryData, 'minimum')
                ),
                $unit
            ),
            'maximumText' => $this->formatDynamicsResult(
                $this->formatDynamicsNumber(
                    data_get($summaryData, 'maximum')
                ),
                $unit
            ),
            'averageText' => $this->formatDynamicsResult(
                $this->formatDynamicsNumber(
                    data_get($summaryData, 'average')
                ),
                $unit
            ),
            'chartWidth' => $chartWidth,
            'chartHeight' => $chartHeight,
            'paddingLeft' => $paddingLeft,
            'plotWidth' => $plotWidth,
            'chartRightX' => $chartWidth - $paddingRight,
            'chartYAxisLabelX' => $paddingLeft - 10,
            'chartXAxisLabelY' => $chartHeight - 21,
            'svgPoints' => $svgPoints,
            'polyline' => $polyline,
            'yTicks' => $yTicks,
            'referenceBand' => $referenceBand,
        ];
    }

    private function formatDynamicsNumber(
        $value,
        int $decimals = 2
    ): string
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return '—';
        }

        return rtrim(
            rtrim(
                number_format(
                    (float)$value,
                    $decimals,
                    ',',
                    ' '
                ),
                '0'
            ),
            ','
        );
    }

    private function formatDynamicsResult(
        $value,
        string $unit = ''
    ): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return trim(
            (string)$value
            . ($unit !== '' ? ' ' . $unit : '')
        );
    }

    private function dynamicsResultsWord(int $count): string
    {
        $mod100 = $count % 100;
        $mod10 = $count % 10;

        if ($mod100 >= 11 && $mod100 <= 19) {
            return 'результатов';
        }

        return match ($mod10) {
            1 => 'результат',
            2, 3, 4 => 'результата',
            default => 'результатов',
        };
    }

    private function normalizeLabNumericValue($value): ?float
    {
        $rawValue = trim((string)$value);

        if ($rawValue === '') {
            return null;
        }

        $normalizedValue = str_replace(
            ["\u{00A0}", ' ', ','],
            ['', '', '.'],
            $rawValue
        );

        return is_numeric($normalizedValue)
            ? (float)$normalizedValue
            : null;
    }

    private function resolveLabResearchName(LabResearch $research): ?string
    {
        return $this->firstFilledLabResearchAttribute(
            $research,
            [
                'research_name',
                'analysis_name',
                'test_name',
                'name',
                'laboratory',
            ]
        );
    }

    private function resolveLabMaterialName(LabResearch $research): ?string
    {
        return $this->firstFilledLabResearchAttribute(
            $research,
            [
                'material',
                'biomaterial',
                'sample_material',
                'sample_type',
                'specimen',
            ]
        );
    }

    private function firstFilledLabResearchAttribute(
        LabResearch $research,
        array       $attributes
    ): ?string
    {
        foreach ($attributes as $attribute) {
            $value = $research->getAttribute($attribute);

            if (!is_scalar($value)) {
                continue;
            }

            $value = trim((string)$value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }


    private function applyDoctorScope(
        Builder $query,
        int     $doctorId
    ): void
    {
        $query->where('doctor_id', $doctorId);
    }

    private function labResearchRelations(): array
    {
        return [
            'patient:id,birth_at,gender,name,surname,patronym',

            'results' => function ($query): void {
                $query->select([
                    'id',
                    'lab_research_id',
                    'patient_id',
                    'lab_parameter_id',
                    'value',
                ]);
            },

            'results.parameter' => function ($query): void {
                $query->select([
                    'id',
                    'name',
                    'unit',
                    'data_type',
                    'normal_values',
                ]);
            },

            'results.parameter.criticalRanges' => function ($query): void {
                $query->select([
                    'id',
                    'parameter_id',
                    'sex',
                    'age_min_y',
                    'age_max_y',
                    'critical_low',
                    'critical_high',
                ]);
            },
        ];
    }

    private function decorateLabResearchAttention(
        LabResearch $research
    ): void
    {
        $research->setAttribute('attention_level', 'normal');
        $research->setAttribute('attention_parameter_name', null);
        $research->setAttribute('attention_value', null);
        $research->setAttribute('attention_unit', null);
        $research->setAttribute('attention_reference', null);
        $research->setAttribute('critical_results', []);
        $research->setAttribute('critical_results_count', 0);

        $criticalResults = $this->getCriticalLabResults($research);

        if ($criticalResults !== []) {
            $firstCriticalResult = $criticalResults[0];

            $research->setAttribute('attention_level', 'critical');
            $research->setAttribute(
                'attention_parameter_name',
                $firstCriticalResult['parameter_name']
            );
            $research->setAttribute(
                'attention_value',
                $firstCriticalResult['value']
            );
            $research->setAttribute(
                'attention_unit',
                $firstCriticalResult['unit']
            );
            $research->setAttribute(
                'attention_reference',
                $firstCriticalResult['reference']
            );
            $research->setAttribute(
                'critical_results',
                $criticalResults
            );
            $research->setAttribute(
                'critical_results_count',
                count($criticalResults)
            );

            return;
        }

        $firstDeviation = $this->getFirstLabDeviation($research);

        if ($firstDeviation === null) {
            return;
        }

        $research->setAttribute('attention_level', 'warning');
        $research->setAttribute(
            'attention_parameter_name',
            $firstDeviation['parameter_name']
        );
        $research->setAttribute(
            'attention_value',
            $firstDeviation['value']
        );
        $research->setAttribute(
            'attention_unit',
            $firstDeviation['unit']
        );
        $research->setAttribute(
            'attention_reference',
            $firstDeviation['reference']
        );
    }

    private function getLabDeviationResults(
        LabResearch $research,
        array       $excludedParameterIds = []
    ): array
    {
        $patientContext = $this->getLabResearchPatientContext(
            $research
        );

        if ($patientContext === null) {
            return [];
        }

        $age = $patientContext['age'];
        $sex = $patientContext['sex'];
        $deviationResults = [];

        foreach ($research->results as $result) {
            $parameter = $result->parameter;

            if (!$parameter) {
                continue;
            }

            if (
                in_array(
                    (int)$parameter->id,
                    $excludedParameterIds,
                    true
                )
            ) {
                continue;
            }

            $rawValue = trim((string)$result->value);

            if ($rawValue === '') {
                continue;
            }

            $normalizedValue = str_replace(
                [' ', ','],
                ['', '.'],
                $rawValue
            );

            $normalValues = $parameter->normal_values ?? [];

            if (is_string($normalValues)) {
                $normalValues = json_decode(
                    $normalValues,
                    true
                ) ?: [];
            }

            if (
                !is_array($normalValues)
                || $normalValues === []
            ) {
                continue;
            }

            /*
            * Числовые показатели.
            */
            if (is_numeric($normalizedValue)) {
                $normalRange = $this->findApplicableNormalRange(
                    $normalValues,
                    $age,
                    $sex
                );

                if ($normalRange) {
                    $min = $normalRange['min_value']
                        ?? $normalRange['min']
                        ?? null;

                    $max = $normalRange['max_value']
                        ?? $normalRange['max']
                        ?? null;

                    $value = (float)$normalizedValue;

                    $belowNormal = $min !== null
                        && $value < (float)$min;

                    $aboveNormal = $max !== null
                        && $value > (float)$max;

                    if ($belowNormal || $aboveNormal) {
                        $reference = null;

                        if ($min !== null && $max !== null) {
                            $reference = $min . '–' . $max;
                        } elseif ($min !== null) {
                            $reference = 'от ' . $min;
                        } elseif ($max !== null) {
                            $reference = 'до ' . $max;
                        }

                        $deviationResults[] = [
                            'result_id' => $result->id,
                            'parameter_id' => $parameter->id,
                            'parameter_name' => $parameter->name,
                            'value' => $rawValue,
                            'unit' => $parameter->unit,
                            'reference' => $reference,
                            'direction' => $belowNormal
                                ? 'low'
                                : 'high',
                            'severity' => 'warning',
                        ];

                        continue;
                    }
                }
            }

            /*
            * Текстовые и категориальные показатели.
            */
            $normalTextValues = collect($normalValues)
                ->map(function ($normalValue): string {
                    if (is_string($normalValue)) {
                        return mb_strtolower(
                            trim($normalValue)
                        );
                    }

                    if (is_array($normalValue)) {
                        return mb_strtolower(
                            trim((string)(
                                $normalValue['value']
                                ?? $normalValue['normal_value']
                                ?? $normalValue['text']
                                ?? ''
                            ))
                        );
                    }

                    return '';
                })
                ->filter()
                ->values();

            if ($normalTextValues->isEmpty()) {
                continue;
            }

            $normalizedTextValue = mb_strtolower(
                trim($rawValue)
            );

            if (
                $normalTextValues->contains(
                    $normalizedTextValue
                )
            ) {
                continue;
            }

            $deviationResults[] = [
                'result_id' => $result->id,
                'parameter_id' => $parameter->id,
                'parameter_name' => $parameter->name,
                'value' => $rawValue,
                'unit' => $parameter->unit,
                'reference' => $normalTextValues->implode(', '),
                'direction' => null,
                'severity' => 'warning',
            ];
        }

        return $deviationResults;
    }

    private function getCriticalLabResults(
        LabResearch $research
    ): array
    {
        $patientContext = $this->getLabResearchPatientContext($research);

        if ($patientContext === null) {
            return [];
        }

        $age = $patientContext['age'];
        $sex = $patientContext['sex'];
        $criticalResults = [];

        foreach ($research->results as $result) {
            $parameter = $result->parameter;

            if (!$parameter) {
                continue;
            }

            $rawValue = trim((string)$result->value);

            if ($rawValue === '') {
                continue;
            }

            $normalizedValue = str_replace(
                [' ', ','],
                ['', '.'],
                $rawValue
            );

            if (!is_numeric($normalizedValue)) {
                continue;
            }

            $criticalRange = $this->findApplicableCriticalRange(
                $parameter->criticalRanges,
                $age,
                $sex
            );

            if (!$criticalRange) {
                continue;
            }

            $value = (float)$normalizedValue;

            $belowCritical = $criticalRange->critical_low !== null
                && $value <= (float)$criticalRange->critical_low;

            $aboveCritical = $criticalRange->critical_high !== null
                && $value >= (float)$criticalRange->critical_high;

            if (!$belowCritical && !$aboveCritical) {
                continue;
            }

            $criticalReference = [];

            if ($criticalRange->critical_low !== null) {
                $criticalReference[] = '≤ ' . $criticalRange->critical_low;
            }

            if ($criticalRange->critical_high !== null) {
                $criticalReference[] = '≥ ' . $criticalRange->critical_high;
            }

            $criticalResults[] = [
                'result_id' => $result->id,
                'parameter_id' => $parameter->id,
                'parameter_name' => $parameter->name,
                'value' => $rawValue,
                'unit' => $parameter->unit,
                'reference' => implode(' или ', $criticalReference),
                'direction' => $belowCritical ? 'low' : 'high',
            ];
        }

        return $criticalResults;
    }

    private function getFirstLabDeviation(
        LabResearch $research
    ): ?array
    {
        $patientContext = $this->getLabResearchPatientContext($research);

        if ($patientContext === null) {
            return null;
        }

        $age = $patientContext['age'];
        $sex = $patientContext['sex'];

        foreach ($research->results as $result) {
            $parameter = $result->parameter;

            if (!$parameter) {
                continue;
            }

            $rawValue = trim((string)$result->value);

            if ($rawValue === '') {
                continue;
            }

            $normalizedValue = str_replace(
                [' ', ','],
                ['', '.'],
                $rawValue
            );

            $normalValues = $parameter->normal_values ?? [];

            if (is_string($normalValues)) {
                $normalValues = json_decode(
                    $normalValues,
                    true
                ) ?: [];
            }

            if (!is_array($normalValues) || $normalValues === []) {
                continue;
            }

            if (
                $parameter->data_type === 'numeric'
                && is_numeric($normalizedValue)
            ) {
                $normalRange = $this->findApplicableNormalRange(
                    $normalValues,
                    $age,
                    $sex
                );

                if (!$normalRange) {
                    continue;
                }

                $min = $normalRange['min_value']
                    ?? $normalRange['min']
                    ?? null;

                $max = $normalRange['max_value']
                    ?? $normalRange['max']
                    ?? null;

                $value = (float)$normalizedValue;

                $belowNormal = $min !== null
                    && $value < (float)$min;

                $aboveNormal = $max !== null
                    && $value > (float)$max;

                if (!$belowNormal && !$aboveNormal) {
                    continue;
                }

                $referenceText = null;

                if ($min !== null && $max !== null) {
                    $referenceText = $min . '-' . $max;
                } elseif ($min !== null) {
                    $referenceText = 'от ' . $min;
                } elseif ($max !== null) {
                    $referenceText = 'до ' . $max;
                }

                return [
                    'parameter_name' => $parameter->name,
                    'value' => $rawValue,
                    'unit' => $parameter->unit,
                    'reference' => $referenceText,
                ];
            }

            $normalTextValues = collect($normalValues)
                ->map(function ($normalValue): string {
                    if (is_string($normalValue)) {
                        return mb_strtolower(trim($normalValue));
                    }

                    if (is_array($normalValue)) {
                        return mb_strtolower(
                            trim((string)(
                                $normalValue['value']
                                ?? $normalValue['normal_value']
                                ?? $normalValue['text']
                                ?? ''
                            ))
                        );
                    }

                    return '';
                })
                ->filter()
                ->values();

            if ($normalTextValues->isEmpty()) {
                continue;
            }

            $normalizedTextValue = mb_strtolower($rawValue);

            if ($normalTextValues->contains($normalizedTextValue)) {
                continue;
            }

            return [
                'parameter_name' => $parameter->name,
                'value' => $rawValue,
                'unit' => $parameter->unit,
                'reference' => $normalTextValues->implode(', '),
            ];
        }

        return null;
    }

    private function getLabResearchPatientContext(
        LabResearch $research
    ): ?array
    {
        $patient = $research->patient;

        if (!$patient || !$patient->birth_at) {
            return null;
        }

        $researchDate = $research->research_date
            ?? $research->collected_at
            ?? $research->planned_at
            ?? now();

        return [
            'age' => (int)Carbon::parse($patient->birth_at)
                ->diffInYears(Carbon::parse($researchDate)),
            'sex' => strtoupper(trim((string)$patient->gender)),
        ];
    }

    private function findApplicableCriticalRange(
        Collection $ranges,
        int        $age,
        string     $sex
    ): mixed
    {
        $matchingRanges = $ranges
            ->filter(function ($range) use ($age, $sex): bool {
                $rangeSex = strtoupper(
                    trim((string)$range->sex)
                );

                $sexMatches = $rangeSex === 'ANY'
                    || $rangeSex === ''
                    || ($sex !== '' && $rangeSex === $sex);

                $ageMinMatches = $range->age_min_y === null
                    || $age >= (int)$range->age_min_y;

                $ageMaxMatches = $range->age_max_y === null
                    || $age <= (int)$range->age_max_y;

                return $sexMatches
                    && $ageMinMatches
                    && $ageMaxMatches;
            });

        $criticalRange = $matchingRanges
            ->first(function ($range) use ($sex): bool {
                return $sex !== ''
                    && strtoupper(
                        trim((string)$range->sex)
                    ) === $sex;
            });

        $criticalRange ??= $matchingRanges
            ->first(function ($range): bool {
                $rangeSex = strtoupper(
                    trim((string)$range->sex)
                );

                return $rangeSex === 'ANY'
                    || $rangeSex === '';
            });

        return $criticalRange;
    }

    /**
     * Нормализует пол пациента для подбора лабораторного референса.
     */
    private function normalizePatientSexForLab(Patient $patient): string
    {
        $gender = strtoupper(
            trim((string)($patient->gender ?? $patient->sex ?? ''))
        );

        return match ($gender) {
            'M', 'MALE', 'М', 'МУЖ', 'МУЖСКОЙ' => 'M',
            'F', 'FEMALE', 'Ж', 'ЖЕН', 'ЖЕНСКИЙ' => 'F',
            default => '',
        };
    }

    private function findApplicableNormalRange(
        array  $normalValues,
        ?int   $age,
        string $sex
    ): ?array
    {
        $matchingRanges = collect($normalValues)
            ->filter(function ($range) use ($age, $sex): bool {
                if (!is_array($range)) {
                    return false;
                }

                $rangeSex = strtoupper(
                    trim((string)($range['sex'] ?? 'ANY'))
                );

                $sexMatches = $rangeSex === 'ANY'
                    || $rangeSex === ''
                    || ($sex !== '' && $rangeSex === $sex);

                $ageMin = $range['age_min_y'] ?? null;
                $ageMax = $range['age_max_y'] ?? null;

                $ageMinMatches = $ageMin === null
                    || $ageMin === ''
                    || $age === null
                    || $age >= (int)$ageMin;

                $ageMaxMatches = $ageMax === null
                    || $ageMax === ''
                    || $age === null
                    || $age <= (int)$ageMax;

                return $sexMatches
                    && $ageMinMatches
                    && $ageMaxMatches;
            });

        $normalRange = $matchingRanges
            ->first(function ($range) use ($sex): bool {
                return $sex !== ''
                    && strtoupper(
                        trim((string)($range['sex'] ?? ''))
                    ) === $sex;
            });

        $normalRange ??= $matchingRanges
            ->first(function ($range): bool {
                $rangeSex = strtoupper(
                    trim((string)($range['sex'] ?? 'ANY'))
                );

                return $rangeSex === 'ANY'
                    || $rangeSex === '';
            });

        /*
        * Если в старых данных пол или возраст не заполнены стандартно,
        * используем первую подходящую строку, а затем первую строку
        * из исходного набора норм.
        */
        $normalRange ??= $matchingRanges->first();

        $normalRange ??= collect($normalValues)
            ->first(function ($range): bool {
                return is_array($range);
            });

        return is_array($normalRange)
            ? $normalRange
            : null;
    }
}

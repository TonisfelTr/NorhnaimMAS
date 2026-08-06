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
                    ?? $research->planned_at
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
        $authUser = $request->user();
        $currentDoctor = $authUser->doctor()->firstOrFail();

        $currentDoctorId = (int)$currentDoctor->id;

        if (method_exists($currentDoctor, 'user')) {
            $currentDoctor->loadMissing('user');
        }

        // Важно: лучше заменить debug-режим на Gate/permission.
        // Пока оставлено прежнее поведение, чтобы не сломать доступы.
        $canChooseDoctor = app()->hasDebugModeEnabled();

        $lookup = (string)$request->query('lookup', '');

        if ($lookup === 'doctors') {
            return $this->dynamicsDoctorsLookup(
                $request,
                $currentDoctor,
                $currentDoctorId,
                $canChooseDoctor
            );
        }

        if ($lookup === 'patients') {
            return $this->dynamicsPatientsLookup(
                $request,
                $currentDoctorId,
                $canChooseDoctor
            );
        }

        [$selectedDoctorId, $selectedDoctorName] =
            $this->resolveDynamicsDoctor(
                $request,
                $authUser,
                $currentDoctor,
                $currentDoctorId,
                $canChooseDoctor
            );

        $selectedPatientId = $request->integer('patient_id') ?: null;
        $selectedPatient = $this->resolveDynamicsPatient(
            $selectedPatientId,
            $selectedDoctorId
        );

        if (!$selectedPatient) {
            $selectedPatientId = null;
        }

        $selectedPatientName = $this->dynamicsPatientName($selectedPatient);

        [$selectedPeriod, $periodLabel, $periodFrom] =
            $this->resolveDynamicsPeriod($request);

        $researches = $this->loadDynamicsLabResearches($selectedPatientId);

        [$historyRows, $addLabUrl, $addInstrumentalUrl] =
            $this->buildDynamicsHistory(
                $selectedDoctorId,
                $selectedPatientId,
                $periodFrom
            );

        // Каждое числовое значение нормализуется только один раз.
        $numericResults = $this->buildDynamicsNumericResults($researches);

        $parameters = $numericResults
            ->pluck('parameter')
            ->filter()
            ->unique('id')
            ->sortBy(
                static fn($parameter): string => mb_strtolower((string)$parameter->name)
            )
            ->values();

        $selectedParameterValue = trim(
            (string)$request->query('parameter', '')
        );

        $selectedParameter = $this->resolveDynamicsParameter(
            $parameters,
            $selectedParameterValue
        );

        $selectedParameterId = $selectedParameter
            ? (int)$selectedParameter->id
            : null;

        $selectedParameterName = $selectedParameter?->name;
        $selectedParameterUnit = trim(
            (string)($selectedParameter?->unit ?? '')
        );

        $chartPoints = $this->buildDynamicsChartPoints(
            $numericResults,
            $selectedParameterId,
            $periodFrom
        );

        $patientOverview = $this->buildDynamicsPatientOverview(
            $selectedDoctorName,
            $selectedPatientName,
            $periodLabel,
            $parameters,
            $historyRows
        );

        $summary = $this->buildDynamicsSummary(
            $chartPoints,
            $selectedPatient,
            $selectedPatientName,
            $selectedParameter,
            $selectedParameterName,
            $selectedParameterUnit,
            $periodLabel
        );

        $presentationData = $this->buildDynamicsPresentationData(
            $chartPoints,
            $summary,
            $patientOverview,
            $historyRows
        );

        return view(
            'doctors.analyses.dynamics',
            array_merge([
                'canChooseDoctor' => $canChooseDoctor,
                'doctorSearchUrl' => $request->url()
                    . '?'
                    . http_build_query(['lookup' => 'doctors']),
                'patientSearchUrl' => $request->url()
                    . '?'
                    . http_build_query(['lookup' => 'patients']),
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
            ], $presentationData)
        );
    }

    private function dynamicsDoctorsLookup(
        Request $request,
        Doctor  $currentDoctor,
        int     $currentDoctorId,
        bool    $canChooseDoctor
    )
    {
        if (!$canChooseDoctor) {
            return response()->json([
                'results' => [[
                    'id' => $currentDoctorId,
                    'text' => $this->dynamicsDoctorName($currentDoctor)
                        ?: 'Текущий врач',
                ]],
                'pagination' => ['more' => false],
            ]);
        }

        $page = max(1, $request->integer('page', 1));
        $perPage = 20;

        $patientDoctorIds = Patient::query()
            ->select('doctor_id')
            ->whereNotNull('doctor_id');

        $this->applyPatientsWithAnyResearch($patientDoctorIds);

        $query = Doctor::query()
            ->whereIn('id', $patientDoctorIds);

        if (method_exists(Doctor::class, 'user')) {
            $query->with('user');
        }

        foreach ($this->dynamicsSearchParts($request) as $part) {
            $search = '%' . $part . '%';

            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('surname', 'ilike', $search)
                    ->orWhere('name', 'ilike', $search)
                    ->orWhere('patronym', 'ilike', $search);

                if (method_exists(Doctor::class, 'user')) {
                    $query->orWhereHas(
                        'user',
                        static function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('surname', 'ilike', $search)
                                ->orWhere('name', 'ilike', $search)
                                ->orWhere('patronym', 'ilike', $search);
                        }
                    );
                }
            });
        }

        $doctors = $query
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->forPage($page, $perPage + 1)
            ->get();

        $hasMore = $doctors->count() > $perPage;

        $items = $doctors
            ->take($perPage)
            ->map(function (Doctor $doctor): array {
                return [
                    'id' => (int)$doctor->id,
                    'text' => $this->dynamicsDoctorName($doctor)
                        ?: 'Врач #' . $doctor->id,
                ];
            })
            ->values();

        return response()->json([
            'results' => $items,
            'pagination' => ['more' => $hasMore],
        ]);
    }

    private function dynamicsPatientsLookup(
        Request $request,
        int     $currentDoctorId,
        bool    $canChooseDoctor
    )
    {
        $page = max(1, $request->integer('page', 1));
        $perPage = 20;

        $doctorId = $canChooseDoctor
            ? ($request->integer('doctor_id') ?: null)
            : $currentDoctorId;

        if ($doctorId && !Doctor::query()->whereKey($doctorId)->exists()) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
                'meta' => [
                    'reason' => 'doctor_not_found',
                    'message' => 'Выбранный врач не найден.',
                ],
            ]);
        }

        $query = Patient::query()
            ->select([
                'id',
                'doctor_id',
                'surname',
                'name',
                'patronym',
            ])
            ->when(
                $doctorId,
                static fn(Builder $query, int $doctorId) => $query->where('doctor_id', $doctorId)
            );

        $this->applyPatientsWithAnyResearch($query);

        foreach ($this->dynamicsSearchParts($request) as $part) {
            $search = '%' . $part . '%';

            $query->where(
                static function (Builder $query) use ($search): void {
                    $query
                        ->where('surname', 'ilike', $search)
                        ->orWhere('name', 'ilike', $search)
                        ->orWhere('patronym', 'ilike', $search);
                }
            );
        }

        $patients = $query
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->forPage($page, $perPage + 1)
            ->get();

        $hasMore = $patients->count() > $perPage;

        $items = $patients
            ->take($perPage)
            ->map(fn(Patient $patient): array => [
                'id' => (int)$patient->id,
                'text' => $this->dynamicsPatientName($patient),
            ])
            ->values();

        return response()->json([
            'results' => $items,
            'pagination' => ['more' => $hasMore],
            'meta' => [
                'doctor_id' => (int)$doctorId,
                'items_count' => $items->count(),
                'reason' => $items->isEmpty()
                    ? 'no_ready_research_patients'
                    : null,
                'message' => $items->isEmpty()
                    ? ($doctorId
                        ? 'У выбранного врача нет пациентов с анализами или исследованиями.'
                        : 'Пациенты с анализами или исследованиями не найдены.')
                    : null,
            ],
        ]);
    }

    private function applyPatientsWithAnyResearch(Builder $query): Builder
    {
        $labPatientIds = LabResearch::query()
            ->select('patient_id')
            ->whereNotNull('patient_id');

        $instrumentalPatientIds = InstrumentalResearch::query()
            ->select('patient_id')
            ->whereNotNull('patient_id');

        return $query->where(
            static function (Builder $query) use (
                $labPatientIds,
                $instrumentalPatientIds
            ): void {
                $query
                    ->whereIn('id', $labPatientIds)
                    ->orWhereIn('id', $instrumentalPatientIds);
            }
        );
    }

    private function dynamicsSearchParts(Request $request): array
    {
        $term = trim((string)(
        $request->input('q')
            ?: $request->input('search')
            ?: $request->input('term')
                ?: $request->input('query')
                    ?: ''
        ));

        return preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    private function resolveDynamicsDoctor(
        Request $request,
                $authUser,
        Doctor  $currentDoctor,
        int     $currentDoctorId,
        bool    $canChooseDoctor
    ): array
    {
        $doctorId = $canChooseDoctor
            ? ($request->filled('doctor_id')
                ? ($request->integer('doctor_id') ?: null)
                : null)
            : $currentDoctorId;

        $doctor = null;

        if ($doctorId) {
            $doctor = $doctorId === $currentDoctorId
                ? $currentDoctor
                : Doctor::query()->find($doctorId);

            if (!$doctor) {
                $doctorId = null;
            }
        }

        if ($doctor && method_exists($doctor, 'user')) {
            $doctor->loadMissing('user');
        }

        $doctorName = $this->dynamicsDoctorName($doctor);

        if ($doctorName === '' && $doctorId === $currentDoctorId) {
            $doctorName = $this->dynamicsNameFromObject($authUser);
        }

        if ($doctorId && $doctorName === '') {
            $doctorName = 'Врач #' . $doctorId;
        }

        return [$doctorId, $doctorName];
    }

    private function resolveDynamicsPatient(
        ?int $patientId,
        ?int $doctorId
    ): ?Patient
    {
        if (!$patientId) {
            return null;
        }

        $query = Patient::query()
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
                $doctorId,
                static fn(Builder $query, int $doctorId) => $query->where('doctor_id', $doctorId)
            );

        $this->applyPatientsWithAnyResearch($query);

        return $query->find($patientId);
    }

    private function resolveDynamicsPeriod(Request $request): array
    {
        $now = now()->startOfDay();

        $periods = [
            'year' => [
                'label' => '1 год',
                'from' => $now->copy()->subYear(),
            ],
            'half_year' => [
                'label' => '6 месяцев',
                'from' => $now->copy()->subMonths(6),
            ],
            'quarter' => [
                'label' => '3 месяца',
                'from' => $now->copy()->subMonths(3),
            ],
            'all' => [
                'label' => 'Весь период',
                'from' => null,
            ],
        ];

        $period = (string)$request->query('period', 'year');

        if (!isset($periods[$period])) {
            $period = 'year';
        }

        return [
            $period,
            $periods[$period]['label'],
            $periods[$period]['from'],
        ];
    }

    private function loadDynamicsLabResearches(?int $patientId): Collection
    {
        if (!$patientId) {
            return collect();
        }

        return LabResearch::query()
            ->where('status', 'ready')
            ->where('patient_id', $patientId)
            ->whereHas('results')
            ->with('results.parameter')
            ->get();
    }

    private function buildDynamicsHistory(
        ?int    $doctorId,
        ?int    $patientId,
        ?Carbon $periodFrom
    ): array
    {
        $addLabUrl = null;
        $addInstrumentalUrl = null;

        if ($patientId) {
            $addLabUrl = route('doctors.patients.medical_card', [
                'patient' => $patientId,
                'tab' => 'labs',
            ]);

            $addInstrumentalUrl = route(
                'doctors.patients.medical_card',
                [
                    'patient' => $patientId,
                    'tab' => 'labs',
                    'section' => 'instrumental',
                ]
            );
        }

        $labRows = $this->buildDynamicsLabHistoryRows(
            $doctorId,
            $patientId,
            $periodFrom
        );

        $instrumentalRows = $this->buildDynamicsInstrumentalHistoryRows(
            $doctorId,
            $patientId,
            $periodFrom
        );

        $historyRows = $labRows
            ->concat($instrumentalRows)
            ->sortByDesc('timestamp')
            ->take(300)
            ->values();

        return [$historyRows, $addLabUrl, $addInstrumentalUrl];
    }

    private function buildDynamicsLabHistoryRows(
        ?int    $doctorId,
        ?int    $patientId,
        ?Carbon $periodFrom
    ): Collection
    {
        $query = LabResearch::query()
            ->with([
                'patient:id,name,surname,patronym',
                'doctor:id,name,surname,patronym,status',
                'results.parameter',
            ])
            ->when(
                $doctorId,
                static function (Builder $query, int $doctorId): void {
                    $query->whereHas(
                        'patient',
                        static fn(Builder $patientQuery) => $patientQuery->where('doctor_id', $doctorId)
                    );
                }
            )
            ->when(
                $patientId,
                static fn(Builder $query, int $patientId) => $query->where('patient_id', $patientId)
            );

        $this->applyDynamicsEffectiveDateFrom(
            $query,
            [
                'research_date',
                'planned_at',
                'updated_at',
                'created_at',
            ],
            $periodFrom
        );

        $this->orderByDynamicsEffectiveDate(
            $query,
            [
                'research_date',
                'planned_at',
                'updated_at',
                'created_at',
            ]
        );

        return $query
            ->limit($patientId ? 500 : 250)
            ->get()
            ->flatMap(function (LabResearch $research): Collection {
                $date = $this->dynamicsLabResearchDate($research);

                if (!$date) {
                    return collect();
                }

                $researchName = $this->resolveLabResearchName($research)
                    ?: 'Лабораторный анализ';
                $materialName = $this->resolveLabMaterialName($research);
                $status = (string)($research->status ?? '');
                $statusLabel = $this->resolveResearchStatusLabel($status);

                $patientName = $this->dynamicsPatientName(
                    $research->getRelationValue('patient')
                );
                $doctorName = $this->dynamicsDoctorName(
                    $research->getRelationValue('doctor')
                );

                $patientName = $patientName !== ''
                    ? $patientName
                    : ($research->patient_id
                        ? 'Пациент #' . $research->patient_id
                        : 'Не указан');

                $doctorName = $doctorName !== ''
                    ? $doctorName
                    : ($research->doctor_id
                        ? 'Врач #' . $research->doctor_id
                        : 'Не указан');

                $doctorAccreditation = trim((string)data_get(
                    $research->getRelationValue('doctor'),
                    'status',
                    ''
                ));

                $baseRow = [
                    'type' => 'lab',
                    'type_label' => 'Анализ',
                    'date' => $date->format('d.m.Y'),
                    'timestamp' => $date->getTimestamp(),
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'doctor_id' => $research->doctor_id
                        ? (int)$research->doctor_id
                        : null,
                    'doctor' => $doctorName,
                    'doctor_accreditation' => $doctorAccreditation !== ''
                        ? $doctorAccreditation
                        : null,
                    'patient_id' => $research->patient_id
                        ? (int)$research->patient_id
                        : null,
                    'patient' => $patientName,
                    'url' => $research->patient_id
                        ? route('doctors.patients.medical_card', [
                            'patient' => $research->patient_id,
                            'tab' => 'labs',
                            'research' => $research->id,
                        ])
                        : null,
                    'delete_url' => $status === 'ordered'
                        ? route('doctors.patients.researches.delete', [
                            'labResearch' => $research->id,
                        ])
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

                $detail = collect([
                    $researchName,
                    $materialName,
                    $statusLabel,
                ])->filter()->unique()->implode(' · ');

                return $research->results->map(
                    static function ($result) use (
                        $baseRow,
                        $researchName,
                        $statusLabel,
                        $detail
                    ): array {
                        $parameter = $result->parameter;
                        $parameterName = trim(
                            (string)($parameter?->name ?? '')
                        );
                        $unit = trim((string)($parameter?->unit ?? ''));
                        $rawValue = trim((string)$result->value);

                        return array_merge($baseRow, [
                            'name' => $parameterName !== ''
                                ? $parameterName
                                : $researchName,
                            'detail' => $detail,
                            'result' => trim(
                                ($rawValue !== '' ? $rawValue : $statusLabel)
                                . ($rawValue !== '' && $unit !== ''
                                    ? ' ' . $unit
                                    : '')
                            ),
                        ]);
                    }
                );
            });
    }

    private function buildDynamicsInstrumentalHistoryRows(
        ?int    $doctorId,
        ?int    $patientId,
        ?Carbon $periodFrom
    ): Collection
    {
        $query = InstrumentalResearch::query()
            ->with([
                'patient:id,name,surname,patronym',
                'doctor:id,name,surname,patronym,status',
            ])
            ->when(
                $doctorId,
                static function (Builder $query, int $doctorId): void {
                    $query->whereHas(
                        'patient',
                        static fn(Builder $patientQuery) => $patientQuery->where('doctor_id', $doctorId)
                    );
                }
            )
            ->when(
                $patientId,
                static fn(Builder $query, int $patientId) => $query->where('patient_id', $patientId)
            );

        $this->applyDynamicsEffectiveDateFrom(
            $query,
            ['result_at', 'performed_at', 'updated_at', 'created_at'],
            $periodFrom
        );

        $this->orderByDynamicsEffectiveDate(
            $query,
            ['result_at', 'performed_at', 'updated_at', 'created_at']
        );

        return $query
            ->limit($patientId ? 500 : 250)
            ->get()
            ->map(function (InstrumentalResearch $research): ?array {
                $date = $this->dynamicsInstrumentalResearchDate($research);

                if (!$date) {
                    return null;
                }

                $patientName = $this->dynamicsPatientName(
                    $research->getRelationValue('patient')
                );
                $doctorName = $this->dynamicsDoctorName(
                    $research->getRelationValue('doctor')
                );

                $patientName = $patientName !== ''
                    ? $patientName
                    : ($research->patient_id
                        ? 'Пациент #' . $research->patient_id
                        : 'Не указан');

                $doctorName = $doctorName !== ''
                    ? $doctorName
                    : ($research->doctor_id
                        ? 'Врач #' . $research->doctor_id
                        : 'Не указан');

                $doctorAccreditation = trim((string)data_get(
                    $research->getRelationValue('doctor'),
                    'status',
                    ''
                ));

                $name = trim((string)($research->name ?? ''));
                $status = (string)($research->status ?? '');
                $statusLabel = $this->resolveResearchStatusLabel($status);

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

                return [
                    'type' => 'instrumental',
                    'type_label' => 'Исследование',
                    'date' => $date->format('d.m.Y'),
                    'timestamp' => $date->getTimestamp(),
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'doctor_id' => $research->doctor_id
                        ? (int)$research->doctor_id
                        : null,
                    'doctor' => $doctorName,
                    'doctor_accreditation' => $doctorAccreditation !== ''
                        ? $doctorAccreditation
                        : null,
                    'patient_id' => $research->patient_id
                        ? (int)$research->patient_id
                        : null,
                    'patient' => $patientName,
                    'name' => $name !== ''
                        ? $name
                        : 'Инструментальное исследование',
                    'detail' => $detail,
                    'result' => $result !== '' ? $result : $statusLabel,
                    'url' => $research->patient_id
                        ? route('doctors.patients.medical_card', [
                            'patient' => $research->patient_id,
                            'tab' => 'labs',
                            'section' => 'instrumental',
                            'instrumental_research' => $research->id,
                        ])
                        : null,
                    'delete_url' => $status === 'ordered'
                    && $research->patient_id
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
            ->filter()
            ->values();
    }

    private function applyDynamicsEffectiveDateFrom(
        Builder $query,
        array   $columns,
        ?Carbon $from
    ): Builder
    {
        if (!$from) {
            return $query;
        }

        return $query->where(
            static function (Builder $outerQuery) use ($columns, $from): void {
                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';

                    $outerQuery->{$method}(
                        static function (Builder $dateQuery) use (
                            $columns,
                            $index,
                            $column,
                            $from
                        ): void {
                            foreach (array_slice($columns, 0, $index) as $previous) {
                                $dateQuery->whereNull($previous);
                            }

                            $dateQuery->where($column, '>=', $from);
                        }
                    );
                }
            }
        );
    }

    private function orderByDynamicsEffectiveDate(
        Builder $query,
        array   $columns
    ): Builder
    {
        $grammar = $query->getQuery()->getGrammar();
        $wrappedColumns = array_map(
            static fn(string $column): string => $grammar->wrap($column),
            $columns
        );

        return $query->orderByRaw(
            'COALESCE(' . implode(', ', $wrappedColumns) . ') DESC'
        );
    }

    private function buildDynamicsNumericResults(Collection $researches): Collection
    {
        return $researches->flatMap(function (LabResearch $research): Collection {
            $date = $this->dynamicsLabResearchDate($research);

            if (!$date) {
                return collect();
            }

            return $research->results
                ->map(function ($result) use ($research, $date): ?array {
                    if (!$result->parameter) {
                        return null;
                    }

                    $value = $this->normalizeLabNumericValue($result->value);

                    if ($value === null) {
                        return null;
                    }

                    return [
                        'result' => $result,
                        'research' => $research,
                        'parameter' => $result->parameter,
                        'parameter_id' => (int)$result->lab_parameter_id,
                        'value' => $value,
                        'raw_value' => trim((string)$result->value),
                        'date' => $date,
                        'timestamp' => $date->getTimestamp(),
                    ];
                })
                ->filter();
        })->values();
    }

    private function resolveDynamicsParameter(
        Collection $parameters,
        string     $value
    )
    {
        if ($value === '') {
            return null;
        }

        return $parameters->first(
            static function ($parameter) use ($value): bool {
                if ((string)$parameter->id === $value) {
                    return true;
                }

                $code = trim((string)($parameter->code ?? ''));

                return $code !== '' && $code === $value;
            }
        );
    }

    private function buildDynamicsChartPoints(
        Collection $numericResults,
        ?int       $parameterId,
        ?Carbon    $periodFrom
    ): Collection
    {
        if (!$parameterId) {
            return collect();
        }

        $researchMeta = [];

        return $numericResults
            ->filter(
                static function (array $row) use (
                    $parameterId,
                    $periodFrom
                ): bool {
                    if ($row['parameter_id'] !== $parameterId) {
                        return false;
                    }

                    return !$periodFrom || $row['date']->gte($periodFrom);
                }
            )
            ->map(function (array $row) use (&$researchMeta): array {
                /** @var LabResearch $research */
                $research = $row['research'];
                $researchId = (int)$research->id;

                if (!isset($researchMeta[$researchId])) {
                    $researchMeta[$researchId] = [
                        'name' => $this->resolveLabResearchName($research),
                        'material' => $this->resolveLabMaterialName($research),
                    ];
                }

                $date = $row['date'];
                $result = $row['result'];

                return [
                    'result_id' => (int)$result->id,
                    'research_id' => $researchId,
                    'value' => $row['value'],
                    'raw_value' => $row['raw_value'],
                    'date' => $date->format('d.m.Y'),
                    'label' => $date->format('d.m.Y'),
                    'timestamp' => $row['timestamp'],
                    'research' => $researchMeta[$researchId]['name'],
                    'material' => $researchMeta[$researchId]['material'],
                ];
            })
            ->sortBy('timestamp')
            ->values();
    }

    private function buildDynamicsPatientOverview(
        string     $doctorName,
        string     $patientName,
        string     $periodLabel,
        Collection $parameters,
        Collection $historyRows
    ): array
    {
        $latest = $historyRows->first();
        $typeCounts = $historyRows->countBy('type');

        return [
            'doctor' => $doctorName,
            'patient' => $patientName ?: null,
            'period' => $periodLabel,
            'researches_count' => $historyRows
                ->pluck('url')
                ->filter()
                ->unique()
                ->count(),
            'history_count' => $historyRows->count(),
            'lab_history_count' => (int)$typeCounts->get('lab', 0),
            'instrumental_history_count' => (int)$typeCounts->get(
                'instrumental',
                0
            ),
            'parameters_count' => $parameters->count(),
            'last_research_date' => data_get($latest, 'date'),
            'last_research' => data_get($latest, 'name'),
            'last_material' => data_get($latest, 'detail'),
        ];
    }

    private function buildDynamicsSummary(
        Collection $chartPoints,
        ?Patient   $patient,
        string     $patientName,
                   $parameter,
        ?string    $parameterName,
        string     $parameterUnit,
        string     $periodLabel
    ): array
    {
        $firstPoint = $chartPoints->first();
        $lastPoint = $chartPoints->last();
        $values = $chartPoints->pluck('value')->values();

        $delta = null;
        $percent = null;
        $trendLabel = 'Недостаточно данных';
        $trendClass = 'neutral';

        if ($chartPoints->count() >= 2) {
            $firstValue = (float)data_get($firstPoint, 'value');
            $lastValue = (float)data_get($lastPoint, 'value');
            $delta = $lastValue - $firstValue;

            if (abs($firstValue) > 0.000001) {
                $percent = ($delta / abs($firstValue)) * 100;
            }

            if (abs($delta) < 0.000001) {
                $trendLabel = 'Без изменений';
                $trendClass = 'stable';
            } elseif ($delta > 0) {
                $trendLabel = 'Рост';
                $trendClass = 'up';
            } else {
                $trendLabel = 'Снижение';
                $trendClass = 'down';
            }
        }

        [$referenceMin, $referenceMax, $referenceLabel] =
            $this->resolveDynamicsReference(
                $patient,
                $parameter,
                $lastPoint
            );

        [$lastStatus, $lastStatusLabel] = $this->resolveDynamicsLastStatus(
            $lastPoint,
            $referenceMin,
            $referenceMax
        );

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

        return [
            'patient' => $patientName ?: null,
            'parameter' => $parameterName,
            'research' => $this->dynamicsDistinctSummary(
                $researchNames,
                'Несколько исследований'
            ),
            'material' => $this->dynamicsDistinctSummary(
                $materialNames,
                'Несколько материалов'
            ),
            'period' => $periodLabel,
            'unit' => $parameterUnit,
            'points_count' => $chartPoints->count(),
            'minimum' => $values->isNotEmpty()
                ? (float)$values->min()
                : null,
            'maximum' => $values->isNotEmpty()
                ? (float)$values->max()
                : null,
            'average' => $values->isNotEmpty()
                ? (float)$values->average()
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
                'label' => $referenceLabel,
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
                'delta' => $delta,
                'percent' => $percent,
            ],
        ];
    }

    private function resolveDynamicsReference(
        ?Patient $patient,
                 $parameter,
        ?array   $lastPoint
    ): array
    {
        if (!$patient || !$parameter) {
            return [null, null, null];
        }

        $normalValues = $parameter->normal_values ?? [];

        if (is_string($normalValues)) {
            $normalValues = json_decode($normalValues, true) ?: [];
        }

        if (!is_array($normalValues) || $normalValues === []) {
            return [null, null, null];
        }

        $referenceDate = data_get($lastPoint, 'timestamp')
            ? Carbon::createFromTimestamp(
                (int)data_get($lastPoint, 'timestamp')
            )
            : now();

        $patientAge = $patient->birth_at
            ? (int)Carbon::parse($patient->birth_at)
                ->diffInYears($referenceDate)
            : null;

        $normalRange = $this->findApplicableNormalRange(
            $normalValues,
            $patientAge,
            $this->normalizePatientSexForLab($patient)
        );

        if (!$normalRange) {
            return [null, null, null];
        }

        $min = $normalRange['min_value']
            ?? $normalRange['min']
            ?? null;
        $max = $normalRange['max_value']
            ?? $normalRange['max']
            ?? null;

        $label = match (true) {
            $min !== null && $max !== null => $min . '–' . $max,
            $min !== null => 'от ' . $min,
            $max !== null => 'до ' . $max,
            default => null,
        };

        return [$min, $max, $label];
    }

    private function resolveDynamicsLastStatus(
        ?array $lastPoint,
               $referenceMin,
               $referenceMax
    ): array
    {
        if (!$lastPoint || ($referenceMin === null && $referenceMax === null)) {
            return ['unknown', 'Референс не задан'];
        }

        $lastValue = (float)data_get($lastPoint, 'value');

        if ($referenceMin !== null && $lastValue < (float)$referenceMin) {
            return ['low', 'Ниже референса'];
        }

        if ($referenceMax !== null && $lastValue > (float)$referenceMax) {
            return ['high', 'Выше референса'];
        }

        return ['normal', 'В пределах референса'];
    }

    private function dynamicsDistinctSummary(
        Collection $values,
        string     $multipleLabel
    ): ?string
    {
        return match (true) {
            $values->count() === 1 => (string)$values->first(),
            $values->count() > 1 => $multipleLabel,
            default => null,
        };
    }

    private function dynamicsLabResearchDate(LabResearch $research): ?Carbon
    {
        $value = $research->research_date
            ?? $research->planned_at
            ?? $research->updated_at
            ?? $research->created_at;

        return $value ? Carbon::parse($value) : null;
    }

    private function dynamicsInstrumentalResearchDate(
        InstrumentalResearch $research
    ): ?Carbon
    {
        $value = $research->result_at
            ?? $research->performed_at
            ?? $research->updated_at
            ?? $research->created_at;

        return $value ? Carbon::parse($value) : null;
    }

    private function dynamicsPatientName(?Patient $patient): string
    {
        return $patient ? $this->dynamicsNameFromObject($patient) : '';
    }

    private function dynamicsDoctorName(?Doctor $doctor): string
    {
        if (!$doctor) {
            return '';
        }

        $readyName = trim((string)(
        data_get($doctor, 'full_name')
            ?: data_get($doctor, 'fullname')
            ?: data_get($doctor, 'fio')
                ?: ''
        ));

        if ($readyName !== '') {
            return $readyName;
        }

        $doctorName = $this->dynamicsNameFromObject($doctor);

        if ($doctorName !== '') {
            return $doctorName;
        }

        // Не вызываем lazy loading в циклах истории.
        $doctorUser = method_exists($doctor, 'user')
        && $doctor->relationLoaded('user')
            ? $doctor->getRelation('user')
            : null;

        if (!$doctorUser) {
            return '';
        }

        $readyUserName = trim((string)(
        data_get($doctorUser, 'full_name')
            ?: data_get($doctorUser, 'fullname')
            ?: data_get($doctorUser, 'fio')
                ?: ''
        ));

        return $readyUserName !== ''
            ? $readyUserName
            : $this->dynamicsNameFromObject($doctorUser);
    }

    private function dynamicsNameFromObject($person): string
    {
        if (!$person) {
            return '';
        }

        return collect([
            data_get($person, 'surname'),
            data_get($person, 'name'),
            data_get($person, 'patronym'),
        ])
            ->map(static fn($value): string => trim((string)$value))
            ->filter(static fn(string $value): bool => $value !== '')
            ->implode(' ');
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
    ): void
    {
        $search = trim((string)$request->query('search', ''));

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

        $stage = trim((string)$request->query('stage', ''));

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

        $period = trim((string)$request->query('period', ''));

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
        string  $fallback
    ): string
    {
        if (!$person) {
            return $fallback;
        }

        $readyName = trim((string)(
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
            ->filter(static fn($value): bool => filled($value))
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
    ): array
    {
        $plannedAt = $research->planned_at
            ? Carbon::parse($research->planned_at)
            : null;

        $isOverdue = in_array(
                (string)$research->status,
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
                'index' => (string)$research->status
                === self::ASSIGNMENT_STATUS_PROCESSING
                    ? 3
                    : 1,
            ];
        }

        return match ((string)$research->status) {
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
    ): string
    {
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
        Collection  $parametersById
    ): array
    {
        $parameterIds = collect($research->parameters ?? [])
            ->map(static fn($id): int => (int)$id)
            ->filter()
            ->unique()
            ->values();

        $parameterNames = $parameterIds
            ->map(
                static fn(int $id): ?string => $parametersById->get($id)?->name
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
                static fn(string $part): string => mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('');

        return [
            'id' => (int)$research->id,
            'patient_id' => $research->patient_id,
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
            'priority_label' => (string)$research->priority
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
    ): Collection
    {
        $parameterIds = collect(
            $request->session()->getOldInput('parameter_ids', [])
        )
            ->map(static fn($id): int => (int)$id)
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
    ): void
    {
        $doctorId = (int)auth()->user()
            ->doctor()
            ->value('id');

        abort_if($doctorId <= 0, 403);

        if (!app()->hasDebugModeEnabled()) {
            abort_unless(
                (int)$research->doctor_id === $doctorId,
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
    ): string
    {
        if ($parameters->count() === 1) {
            return (string)$parameters->first()->name;
        }

        if ($parameters->isNotEmpty()) {
            return 'Лабораторное направление: '
                . $parameters->first()->name
                . ' и ещё '
                . ($parameters->count() - 1);
        }

        return '';
    }

    private function resolveResearchStatusLabel(?string $status): string
    {
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
            ?? $research->planned_at
            ?? $research->updated_at
            ?? $research->created_at
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

    /**
     * Выполняет поиск пациентов для формы нового направления.
     *
     * @param Request $request HTTP-запрос.
     *
     * @return JsonResponse
     */
    public function assignmentSearchPatients(
        Request $request
    ): JsonResponse
    {
        $search = trim((string)$request->query('q', ''));

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
                    'id' => (int)$patient->id,
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
    ): JsonResponse
    {
        $search = trim((string)$request->query('q', ''));
        $sampleType = trim(
            (string)$request->query('sample_type', '')
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
                    'id' => (int)$parameter->id,
                    'name' => (string)$parameter->name,
                    'text' => (string)$parameter->name,
                    'unit' => (string)($parameter->unit ?? ''),
                    'group' => (string)($parameter->group ?? ''),
                    'sample_type' =>
                        (string)($parameter->sample_type ?? ''),
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

        $this->applyDoctorScope($query, (int)$doctor->id);
        $this->applyAssignmentFilters($query, $request);

        $assignments = $query
            ->orderByDesc('created_at')
            ->paginate(self::ASSIGNMENT_PER_PAGE)
            ->withQueryString();

        $parameterIds = collect($assignments->items())
            ->flatMap(
                static fn(LabResearch $research): array => (array)($research->parameters ?? [])
            )
            ->map(static fn($id): int => (int)$id)
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
            fn(LabResearch $research): array => $this->buildAssignmentRow(
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
    ): RedirectResponse
    {
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
            ->map(static fn($id): int => (int)$id)
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
                static fn(LabParameter $parameter): bool => (string)$parameter->sample_type
                    !== (string)$data['sample_type']
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

        $title = trim((string)($data['laboratory'] ?? ''));

        $labResearch = DB::transaction(
            function () use (
                $data,
                $doctor,
                $parameterIds,
                $parameters,
                $title
            ): LabResearch {
                $research = LabResearch::query()->create([
                    'patient_id' => (int)$data['patient_id'],
                    'doctor_id' => (int)$doctor->id,
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
                    && trim((string)$research->laboratory) === ''
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
        Request     $request,
        LabResearch $labResearch
    ): View
    {
        $this->authorizeAssignmentAccess($labResearch);

        $labResearch->loadMissing([
            'patient',
            'doctor',
        ]);

        $parameterIds = collect($labResearch->parameters ?? [])
            ->map(static fn($id): int => (int)$id)
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
                static fn(LabParameter $parameter): string => trim((string)($parameter->group ?? ''))
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
                    (string)$labResearch->priority
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
}

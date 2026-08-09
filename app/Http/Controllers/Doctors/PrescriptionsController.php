<?php

namespace App\Http\Controllers\Doctors;

use App\Enums\MedicineTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\PrescriptionStoreRequest;
use App\Jobs\PrescriptionsJob;
use App\Models\ContraindicationsType;
use App\Models\Diagnose;
use App\Models\Drug;
use App\Models\MedicalPrescription;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class PrescriptionsController extends Controller
{
    private const DRUG_FORM_LABELS = [
        'tablets' => 'Таблетки',
        'capsules' => 'Капсулы',
        'dragees' => 'Драже',
        'ampules' => 'Ампулы',
        'solution' => 'Раствор',
        'syrup' => 'Сироп',
        'ointment' => 'Мазь',
        'gel' => 'Гель',
        'spray' => 'Спрей',
        'drops' => 'Капли',
        'powder' => 'Порошок',
    ];

    /**
     * Формирует данные для общей верхней навигации модуля назначений.
     *
     * Маршруты будущих разделов проверяются через Route::has(), поэтому
     * отсутствие ещё не реализованного маршрута не приводит к исключению.
     *
     * @return array{
     *     specialistReferralRoute:?string,
     *     specialistReferralCreateRoute:?string,
     *     hospitalReferralRoute:?string,
     *     hospitalReferralCreateRoute:?string,
     *     consultationsRoute:?string,
     *     labAssignmentsRoute:?string,
     *     isPrescriptionSection:bool,
     *     isReferralSection:bool,
     *     isReferenceSection:bool
     * }
     */
    private function prescriptionNavigationData(): array
    {
        return [
            'specialistReferralRoute' => Route::has('doctors.referrals.specialists.index')
                ? route('doctors.referrals.specialists.index')
                : null,
            'specialistReferralCreateRoute' => Route::has('doctors.referrals.specialists.create')
                ? route('doctors.referrals.specialists.create')
                : null,
            'hospitalReferralRoute' => Route::has('doctors.referrals.hospital.index')
                ? route('doctors.referrals.hospital.index')
                : null,
            'hospitalReferralCreateRoute' => Route::has('doctors.referrals.hospital.create')
                ? route('doctors.referrals.hospital.create')
                : null,
            'consultationsRoute' => Route::has('doctors.referrals.consultations.index')
                ? route('doctors.referrals.consultations.index')
                : null,
            'labAssignmentsRoute' => Route::has('doctors.analyses.assignments')
                ? route('doctors.analyses.assignments')
                : null,
            'isPrescriptionSection' => request()->routeIs(
                'doctors.prescriptions.index',
                'doctors.prescriptions.new',
                'doctors.prescriptions.print*',
                'doctors.prescriptions.repeat'
            ),
            'isReferralSection' => request()->routeIs(
                'doctors.referrals.*',
                'doctors.analyses.assignments*'
            ),
            'isReferenceSection' => request()->routeIs('doctors.prescriptions.base'),
        ];
    }

    /**
     * Преобразует JSON/array форм выпуска препарата в структуру для Blade.
     *
     * @param  Drug  $drug  Препарат из справочника.
     *
     * @return array<int, array{
     *     label:string,
     *     entries:array<int, array{text:string}>
     * }>
     */
    private function buildDrugDisplayForms(Drug $drug): array
    {
        $forms = $drug->forms;

        if (!is_iterable($forms)) {
            return [];
        }

        return collect($forms)
            ->map(function ($doses, $form): array {
                $entries = [];

                if (is_iterable($doses)) {
                    foreach ($doses as $dose => $packs) {
                        $packsText = is_array($packs)
                            ? implode(', ', $packs)
                            : (string)$packs;

                        $entries[] = [
                            'text' => trim(
                                (string)$dose . ' мг — ' . $packsText . ' шт'
                            ),
                        ];
                    }
                } elseif ($doses !== null && $doses !== '') {
                    $entries[] = ['text' => (string)$doses];
                }

                return [
                    'label' => self::DRUG_FORM_LABELS[$form]
                        ?? ucfirst((string)$form),
                    'entries' => $entries,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Преобразует модель рецепта в массив, полностью готовый для таблицы.
     *
     * Благодаря этому Blade не содержит бизнес-логики: он только выводит
     * подготовленные значения, статусы и URL действий.
     *
     * @param  MedicalPrescription  $prescription  Исходная запись рецепта.
     *
     * @return array<string, mixed> Подготовленная строка таблицы.
     */
    private function buildPrescriptionRow(
        MedicalPrescription $prescription
    ): array
    {
        $issuedAt = $prescription->issued_at
            ? \Illuminate\Support\Carbon::parse($prescription->issued_at)
            : null;

        /*
         * Срок действия рецепта.
         */
        $validityDays = 0;

        if (is_numeric($prescription->validity_period)) {
            $validityDays = max(
                0,
                (int)$prescription->validity_period
            );
        } else {
            preg_match(
                '/\d+/',
                (string)$prescription->validity_period,
                $matches
            );

            if (isset($matches[0])) {
                $validityDays = max(0, (int)$matches[0]);
            }
        }

        $expiresAt = $issuedAt && $validityDays > 0
            ? $issuedAt->copy()->addDays($validityDays)
            : null;

        /*
         * Строгий рецепт.
         */
        $isStrict =
            (bool)$prescription->is_strict
            || str_contains(
                mb_strtolower(
                    trim((string)$prescription->prescription_form)
                ),
                '148'
            );

        /*
         * Статус.
         */
        if ($expiresAt && $expiresAt->isBefore(today())) {
            $statusLabel = 'Срок истёк';
            $statusClass = 'warning';
            $statusHint = 'До ' . $expiresAt->format('d.m.Y');
        } elseif ($isStrict) {
            $statusLabel = 'Строгий учёт';
            $statusClass = 'warning';

            $statusHint = $expiresAt
                ? 'До ' . $expiresAt->format('d.m.Y')
                : '';
        } else {
            $statusLabel = 'Действует';
            $statusClass = 'active';

            $statusHint = $expiresAt
                ? 'До ' . $expiresAt->format('d.m.Y')
                : '';
        }

        /*
         * ФИО пациента.
         */
        $patientName = trim(
            (string)$prescription->patient_name
        );

        /*
         * Инициалы для аватара.
         */
        $initials = collect(
            preg_split('/\s+/u', $patientName) ?: []
        )
            ->filter()
            ->take(2)
            ->map(
                static fn(string $part): string => mb_strtoupper(
                    mb_substr($part, 0, 1)
                )
            )
            ->implode('');

        /*
         * Серия и номер документа.
         */
        $documentNumber = collect([
            $prescription->series
                ? 'Серия ' . $prescription->series
                : null,

            $prescription->number
                ? '№ ' . $prescription->number
                : null,
        ])
            ->filter()
            ->implode(' ');

        return [
            'id' => (int)$prescription->id,

            'patient_id' => $prescription->patient_id
                ? (int)$prescription->patient_id
                : null,

            'patient_name' => $patientName !== ''
                ? $patientName
                : 'Пациент не указан',

            'patient_birth_date' => $prescription->birth_at
                ? \Illuminate\Support\Carbon::parse(
                    $prescription->birth_at
                )->format('d.m.Y')
                : 'не указана',

            'initials' => $initials !== ''
                ? $initials
                : 'П',

            /*
             * Пока используем generic_name.
             * Если понадобится русское название препарата,
             * можно сделать связь с Drug.
             */
            'drug_name' => (string)$prescription->generic_name,

            'drug_latin_name' => (string)$prescription->generic_name,

            'drug_meta' => collect([
                $prescription->drug_form ?: null,

                $prescription->dosage
                    ? 'дозировка ' . $prescription->dosage
                    : null,

                $prescription->quantity
                    ? 'количество ' . $prescription->quantity
                    : null,
            ])
                ->filter()
                ->implode(' · '),

            'document_number' => $documentNumber !== ''
                ? $documentNumber
                : 'Запись № ' . $prescription->id,

            'usage_instructions' => trim(
                (string)$prescription->usage_instructions
            ) ?: 'Схема приёма не указана',

            'prescription_form' =>
                (string)$prescription->prescription_form,

            'issued_date' => $issuedAt
                ? $issuedAt->format('d.m.Y')
                : '—',

            'issued_time' => '',

            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'status_hint' => $statusHint,

            'is_strict' => $isStrict,

            /*
             * Ссылка на медицинскую карту.
             */
            'medical_card_url' => $prescription->patient_id
                ? route(
                    'doctors.patients.medical_card',
                    [
                        'patient' => $prescription->patient_id,
                    ]
                )
                : null,

            /*
             * Печать рецепта.
             */
            'print_url' => route(
                'doctors.prescriptions.print.from_table',
                [
                    'id' => $prescription->id,
                ]
            ),

            /*
             * Повтор рецепта.
             */
            'repeat_url' => route(
                'doctors.prescriptions.repeat',
                [
                    'prescription' => $prescription->id,
                ]
            ),
        ];
    }

    /**
     * Преобразует латинское название вещества в родительный падеж
     * для печатной формы рецепта.
     *
     * @param  string  $latinName  Латинское наименование вещества.
     *
     * @return string Преобразованное наименование.
     */
    private function toGenitiveCase($latinName)
    {
        $exceptions = [
            'aqua' => 'aquae',
            'spiritus' => 'spiritus',
            'oleum' => 'olei'
        ];

        if (array_key_exists(strtolower($latinName), $exceptions)) {
            return $exceptions[strtolower($latinName)];
        }

        if (substr($latinName, -2) === 'um') return substr($latinName, 0, -2) . 'i';
        if (substr($latinName, -2) === 'us') return substr($latinName, 0, -2) . 'i';
        if (substr($latinName, -1) === 'a') return substr($latinName, 0, -1) . 'ae';

        return $latinName . 'is';
    }

    /**
     * Возвращает сокращённое ФИО текущего врача.
     *
     * @return string ФИО в формате "И. О. Фамилия".
     */
    private function getDoctorFullName(): string
    {
        $currentUser = auth()->user();
        $isAdminRole = $currentUser->getRoleNames()[0] === 'admins';
        $doctor = $currentUser->doctor;

        if ($doctor) {
            $initials = mb_substr($doctor->name, 0, 1) . '. ';
            if (!empty($doctor->patronym)) {
                $initials .= mb_substr($doctor->patronym, 0, 1) . '. ';
            }
            return $initials . $doctor->surname;
        }

        if ($isAdminRole) {
            return 'A. A. Admin';
        }

        abort(403, 'Доступ запрещён: вы не доктор и не администратор.');
    }

    /**
     * Подбирает Bootstrap Icons-класс для фармакологической группы.
     *
     * @param  string  $group  Название фармакологической группы.
     *
     * @return string CSS-класс Bootstrap Icons.
     */
    private function prescriptionGroupDisplayName(string $group): string
    {
        $normalized = mb_strtolower(trim($group));

        return match (true) {
            str_contains($normalized, 'антипсих') =>
            'Антипсихотики',

            str_contains($normalized, 'антидепресс') =>
            'Антидепрессанты',

            str_contains($normalized, 'анкси') ||
            str_contains($normalized, 'транкв') =>
            'Анксиолитики',

            str_contains($normalized, 'холиноблок') ||
            str_contains($normalized, 'антихолин') =>
            'Холиноблокаторы',

            str_contains($normalized, 'нормотим') =>
            'Нормотимики',

            str_contains($normalized, 'снотвор') =>
            'Снотворные',

            str_contains($normalized, 'седатив') =>
            'Седативные',

            str_contains($normalized, 'ноотроп') =>
            'Ноотропы',

            str_contains($normalized, 'психостим') ||
            str_contains($normalized, 'стимулятор') =>
            'Психостимуляторы',

            str_contains($normalized, 'противосуд') ||
            str_contains($normalized, 'антиконвульс') =>
            'Противосудорожные',

            str_contains($normalized, 'дофаминомим') =>
            'Дофаминомиметики',

            str_contains($normalized, 'бета-блок') ||
            str_contains($normalized, 'β-блок') =>
            'Бета-блокаторы',

            default => mb_strtoupper(
                    mb_substr($group, 0, 1)
                ) . mb_substr($group, 1),
        };
    }


    private function prescriptionGroupIcon(string $group): string
    {
        $normalized = mb_strtolower(trim($group));

        return match (true) {
            /*
             * Используем существующие Bootstrap Icons.
             * bi-brain убран — в твоём наборе его, похоже, нет.
             */
            str_contains($normalized, 'антипсих') =>
            'bi-capsule-pill',

            str_contains($normalized, 'антидепресс') =>
            'bi-emoji-smile',

            str_contains($normalized, 'анкси') ||
            str_contains($normalized, 'транкв') =>
            'bi-heart-pulse',

            str_contains($normalized, 'холиноблок') ||
            str_contains($normalized, 'антихолин') =>
            'bi-slash-circle',

            str_contains($normalized, 'нормотим') =>
            'bi-activity',

            str_contains($normalized, 'снотвор') =>
            'bi-moon-stars',

            str_contains($normalized, 'седатив') =>
            'bi-moon',

            str_contains($normalized, 'ноотроп') =>
            'bi-lightning-charge',

            str_contains($normalized, 'психостим') ||
            str_contains($normalized, 'стимулятор') =>
            'bi-lightning',

            str_contains($normalized, 'противосуд') ||
            str_contains($normalized, 'антиконвульс') =>
            'bi-shield-plus',

            str_contains($normalized, 'дофаминомим') =>
            'bi-arrow-repeat',

            str_contains($normalized, 'бета-блок') ||
            str_contains($normalized, 'β-блок') =>
            'bi-heart',

            /*
             * Для неизвестной новой группы всегда будет иконка,
             * а не пустое место.
             */
            default =>
            'bi-capsule',
        };
    }

    /**
     * Показывает журнал рецептов текущего врача.
     *
     * Метод решает четыре отдельные задачи:
     *
     * 1. Формирует сводные показатели за текущий календарный день:
     *    - количество выписанных рецептов;
     *    - количество уникальных пациентов;
     *    - количество рецептов строгого учёта;
     *    - количество повторных назначений.
     *
     * 2. Формирует статистику сегодняшних рецептов по фармакологическим
     *    группам препаратов.
     *
     * 3. Применяет пользовательские фильтры к полному журналу рецептов
     *    текущего врача и создаёт paginator `$prescriptions`.
     *
     * 4. Преобразует записи текущей страницы paginator-а в `$rows` —
     *    коллекцию уже подготовленных для Blade строк таблицы.
     *
     * Важно различать `$prescriptions` и `$rows`:
     *
     * - `$prescriptions` — LengthAwarePaginator. Он хранит общее количество
     *   найденных записей, текущую страницу, URL следующей/предыдущей страницы
     *   и другие сведения о пагинации;
     * - `$rows` — Collection с рецептами ТОЛЬКО текущей страницы, где каждая
     *   модель MedicalPrescription преобразована методом buildPrescriptionRow().
     *
     * Для определения "сегодня" используется `created_at`, так как это
     * фактическое время создания записи рецепта в БД. Верхняя граница дня
     * задаётся как начало следующего дня и сравнение `<`, что надёжнее
     * использования `<= endOfDay()` при наличии микросекунд.
     *
     * @param  Request  $request  HTTP-запрос с GET-фильтрами журнала.
     *
     * @return View Представление журнала рецептов.
     */
    public function index(Request $request): View
    {
        /*
         * Текущий врач.
         */
        $doctor = auth()->user()
            ->doctor()
            ->firstOrFail();

        $doctorId = (int) $doctor->id;
        $doctorFullName = $this->getDoctorFullName();

        /*
         * Дата, отображаемая пользователю и записываемая
         * в medical_prescriptions.issued_at.
         *
         * Именно issued_at является источником истины
         * для статистики и фильтрации рецептов.
         */
        $today = today();

        /*
         * Фильтры журнала.
         */
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => trim((string) $request->query('status', '')),
            'period' => trim((string) $request->query('period', 'all')),
        ];

        if (!in_array(
            $filters['status'],
            ['', 'regular', 'strict', 'repeated'],
            true
        )) {
            $filters['status'] = '';
        }

        if (!in_array(
            $filters['period'],
            ['all', 'today', 'week', 'month'],
            true
        )) {
            $filters['period'] = 'all';
        }

        /*
         * ---------------------------------------------------------
         * БАЗОВЫЙ ЗАПРОС
         * ---------------------------------------------------------
         *
         * Все рецепты текущего врача.
         * Ограничения по датам здесь специально нет.
         */
        $doctorPrescriptionsQuery = MedicalPrescription::query()
            ->where('doctor_id', $doctorId);

        /*
         * ---------------------------------------------------------
         * РЕЦЕПТЫ ЗА СЕГОДНЯ
         * ---------------------------------------------------------
         *
         * Используем issued_at, а НЕ created_at.
         */
        $todayPrescriptionsQuery = (clone $doctorPrescriptionsQuery)
            ->whereDate('issued_at', $today);

        /*
         * 1. Количество рецептов, оформленных сегодня.
         */
        $currentPrescription = (clone $todayPrescriptionsQuery)
            ->count('id');

        /*
         * 2. Количество уникальных пациентов,
         * получивших сегодня хотя бы один рецепт.
         */
        $patientsWithPrescriptionsToday = (clone $todayPrescriptionsQuery)
            ->whereNotNull('patient_id')
            ->distinct()
            ->count('patient_id');

        /*
         * 3. Количество рецептов формы №148 за сегодня.
         *
         * is_strict = true является источником истины.
         */
        $strictPrescriptionsToday = (clone $todayPrescriptionsQuery)
            ->where('is_strict', true)
            ->count('id');

        /*
         * ---------------------------------------------------------
         * 4. ПОВТОРНЫЕ РЕЦЕПТЫ №148 ЗА СЕГОДНЯ
         * ---------------------------------------------------------
         *
         * Правило:
         *
         * Первый №148 пациенту:
         *     не считается повторным.
         *
         * Второй №148:
         *     +1.
         *
         * Третий №148:
         *     ещё +1.
         *
         * Препарат значения НЕ имеет.
         *
         * История предыдущих рецептов проверяется
         * по пациенту вообще, независимо от врача.
         */
        $repeatedPrescriptionsToday = (clone $todayPrescriptionsQuery)
            ->whereNotNull('patient_id')
            ->where('is_strict', true)
            ->whereExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('medical_prescriptions as previous_prescriptions')
                    ->whereColumn(
                        'previous_prescriptions.patient_id',
                        'medical_prescriptions.patient_id'
                    )
                    ->where(
                        'previous_prescriptions.is_strict',
                        true
                    )
                    ->whereColumn(
                        'previous_prescriptions.id',
                        '<',
                        'medical_prescriptions.id'
                    );
            })
            ->count('id');

        /*
         * ---------------------------------------------------------
         * ВСЕГО РЕЦЕПТОВ ТЕКУЩЕГО ВРАЧА
         * ---------------------------------------------------------
         */
        $totalPrescriptionsCount = (clone $doctorPrescriptionsQuery)
            ->count('id');

        /*
         * ---------------------------------------------------------
         * РЕЦЕПТЫ ПО ГРУППАМ ПРЕПАРАТОВ
         * ЗА ТЕКУЩИЙ КАЛЕНДАРНЫЙ МЕСЯЦ
         * ---------------------------------------------------------
         */
        /*
 * ---------------------------------------------------------
 * РЕЦЕПТЫ ПО ФАРМАКОЛОГИЧЕСКИМ ГРУППАМ
 * ЗА ТЕКУЩИЙ КАЛЕНДАРНЫЙ МЕСЯЦ
 * ---------------------------------------------------------
 *
 * ВАЖНО:
 * название группы берём через Drug::groupName(),
 * а не индексируем MedicineTypesEnum вручную.
 */

        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        /*
         * Сначала считаем количество рецептов
         * по каждому конкретному препарату.
         */
        $drugStatistics = MedicalPrescription::query()
            ->join(
                'drugs',
                'medical_prescriptions.generic_name',
                '=',
                'drugs.latin_name'
            )
            ->where(
                'medical_prescriptions.doctor_id',
                $doctorId
            )
            ->whereDate(
                'medical_prescriptions.issued_at',
                '>=',
                $monthStart
            )
            ->whereDate(
                'medical_prescriptions.issued_at',
                '<=',
                $monthEnd
            )
            ->select([
                'drugs.id as drug_id',
                'drugs.group as group_id',
            ])
            ->selectRaw(
                'COUNT(DISTINCT medical_prescriptions.id) as total'
            )
            ->groupBy(
                'drugs.id',
                'drugs.group'
            )
            ->get();

        /*
         * Одним запросом загружаем препараты,
         * попавшие в статистику.
         */
        $drugsById = Drug::query()
            ->whereIn(
                'id',
                $drugStatistics
                    ->pluck('drug_id')
                    ->filter()
                    ->unique()
                    ->values()
            )
            ->get()
            ->keyBy('id');

        /*
         * Для каждого препарата определяем группу
         * тем же методом, который используется
         * в базе препаратов.
         */
        $groupStatistics = $drugStatistics
            ->map(function ($item) use ($drugsById): array {

                /** @var Drug|null $drug */
                $drug = $drugsById->get(
                    (int) $item->drug_id
                );

                $groupName = $drug
                    ? trim((string) $drug->groupName())
                    : '';

                if ($groupName === '') {
                    $groupName = 'Без группы';
                }

                return [
                    'name' => $groupName,
                    'total' => (int) $item->total,
                ];
            })

            /*
             * Несколько разных препаратов могут относиться
             * к одной фармакологической группе.
             */
            ->groupBy('name')
            ->map(function ($items, $groupName): array {

                /*
                 * После groupBy $items — это Collection
                 * всех препаратов этой группы.
                 *
                 * Например:
                 * антипсихотик:
                 * [
                 *     ['name' => 'антипсихотик', 'total' => 3],
                 * ]
                 */
                $total = (int) $items->sum('total');

                /*
                 * Красивое название для интерфейса:
                 * антипсихотик -> Антипсихотики
                 */
                $displayName = $this->prescriptionGroupDisplayName(
                    (string) $groupName
                );

                return [
                    'name' => $displayName,
                    'total' => $total,
                    'icon' => $this->prescriptionGroupIcon(
                        (string) $groupName
                    ),
                ];
            })
            ->sortByDesc('total')
            ->values();

        /*
         * ---------------------------------------------------------
         * ОСНОВНОЙ ЖУРНАЛ
         * ---------------------------------------------------------
         */
        $prescriptionsQuery = clone $doctorPrescriptionsQuery;

        /*
         * Поиск пациента / препарата.
         */
        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';

            $prescriptionsQuery->where(
                function ($query) use ($search): void {
                    $query
                        ->where('patient_name', 'ilike', $search)
                        ->orWhere('generic_name', 'ilike', $search);
                }
            );
        }

        /*
         * ---------------------------------------------------------
         * ФИЛЬТР ПО ТИПУ РЕЦЕПТА
         * ---------------------------------------------------------
         */
        if ($filters['status'] === 'strict') {

            /*
             * Только №148.
             */
            $prescriptionsQuery
                ->where('is_strict', true);

        } elseif ($filters['status'] === 'regular') {

            /*
             * Обычные рецепты.
             */
            $prescriptionsQuery->where(
                function ($query): void {
                    $query
                        ->where('is_strict', false)
                        ->orWhereNull('is_strict');
                }
            );

        } elseif ($filters['status'] === 'repeated') {

            /*
             * Повторные №148.
             *
             * Логика полностью совпадает
             * с карточкой repeatedPrescriptionsToday.
             */
            $prescriptionsQuery
                ->whereNotNull('patient_id')
                ->where('is_strict', true)
                ->whereExists(function ($query): void {
                    $query
                        ->selectRaw('1')
                        ->from(
                            'medical_prescriptions as previous_prescriptions'
                        )
                        ->whereColumn(
                            'previous_prescriptions.patient_id',
                            'medical_prescriptions.patient_id'
                        )
                        ->where(
                            'previous_prescriptions.is_strict',
                            true
                        )
                        ->whereColumn(
                            'previous_prescriptions.id',
                            '<',
                            'medical_prescriptions.id'
                        );
                });
        }

        /*
         * ---------------------------------------------------------
         * ФИЛЬТР ПО ПЕРИОДУ
         * ---------------------------------------------------------
         *
         * Также используем issued_at.
         */
        if ($filters['period'] === 'today') {

            $prescriptionsQuery
                ->whereDate('issued_at', $today);

        } elseif ($filters['period'] === 'week') {

            /*
             * Сегодня + предыдущие 6 дней.
             */
            $weekStart = $today->copy()->subDays(6);

            $prescriptionsQuery
                ->whereDate('issued_at', '>=', $weekStart)
                ->whereDate('issued_at', '<=', $today);

        } elseif ($filters['period'] === 'month') {

            /*
             * Последние 30 календарных дней,
             * включая сегодняшний.
             */
            $periodStart = $today->copy()->subDays(29);

            $prescriptionsQuery
                ->whereDate('issued_at', '>=', $periodStart)
                ->whereDate('issued_at', '<=', $today);
        }

        /*
         * ---------------------------------------------------------
         * ПАГИНАЦИЯ
         * ---------------------------------------------------------
         *
         * Сортируем по той же дате,
         * которая отображается пользователю.
         */
        $prescriptions = $prescriptionsQuery
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate(
                20,
                ['*'],
                'prescriptions_page'
            )
            ->withQueryString();

        /*
         * Подготовленные строки текущей страницы.
         */
        $rows = $prescriptions
            ->getCollection()
            ->map(
                fn (MedicalPrescription $prescription): array =>
                $this->buildPrescriptionRow($prescription)
            );

        /*
         * Старый формат статистики оставляем
         * для обратной совместимости.
         */
        $statistics = $groupStatistics
            ->pluck('total', 'name')
            ->all();

        /*
         * ---------------------------------------------------------
         * ДАННЫЕ VIEW
         * ---------------------------------------------------------
         */
        $viewData = [
            'currentPrescription' => $currentPrescription,

            'patientsWithPrescriptionsToday' =>
                $patientsWithPrescriptionsToday,

            'strictPrescriptionsToday' =>
                $strictPrescriptionsToday,

            'repeatedPrescriptionsToday' =>
                $repeatedPrescriptionsToday,

            'totalPrescriptionsCount' =>
                $totalPrescriptionsCount,

            'doctorFullName' =>
                $doctorFullName,

            'filters' =>
                $filters,

            'prescriptions' =>
                $prescriptions,

            'rows' =>
                $rows,

            'groupStatistics' =>
                $groupStatistics,

            'statistics' =>
                $statistics,
        ];

        return view(
            'doctors.prescriptions.prescriptions_tables',
            array_merge(
                $viewData,
                $this->prescriptionNavigationData()
            )
        );
    }

    /**
     * Показывает форму создания нового рецепта.
     *
     * @return View Представление формы выписки рецепта.
     */
    public function create(): View
    {
        $patients = Patient::select('name', 'surname', 'patronym', 'birth_at')->limit(10)->get();
        $drugs = Drug::select('name', 'latin_name')->limit(10)->get();

        return view(
            'doctors.prescriptions.prescriptions_create',
            array_merge(
                compact('patients', 'drugs'),
                $this->prescriptionNavigationData()
            )
        );
    }

    /**
     * Сохраняет новый рецепт через очередь PrescriptionsJob.
     *
     * Тип рецепта определяется по `drugs.strict`:
     * strict=true  -> форма № 148-1/у-88;
     * strict=false -> форма № 107-1/у.
     *
     * @param  PrescriptionStoreRequest  $request  Валидированные данные формы.
     *
     * @return RedirectResponse Перенаправление в медицинскую карту пациента.
     */
    public function store(PrescriptionStoreRequest $request): RedirectResponse
    {
        $patient = Patient::query()
            ->findOrFail($request->integer('patient_id'));

        $drug = Drug::query()
            ->findOrFail($request->integer('drug_id'));

        $doctor = $request->user()?->doctor;

        if (!$doctor) {
            return back()
                ->withInput()
                ->withErrors([
                    'prescription' =>
                        'Невозможно создать рецепт: '
                        . 'у пользователя не найдена карточка врача.',
                ])
                ->with('open_prescription_modal', true);
        }

        $patientNameInitial = mb_substr(
            $patient->name ?? '',
            0,
            1,
            'UTF-8'
        );

        $patientPatronymInitial = mb_substr(
            $patient->patronym ?? '',
            0,
            1,
            'UTF-8'
        );

        /*
         * Источник истины для формы рецепта — поле drugs.strict.
         * Например, для Алпразолама strict = true.
         */
        $isStrict = (bool)$drug->strict;

        $data = [
            'doctor_id' => (int)$doctor->getKey(),
            'doctor_name' => $this->getDoctorFullName(),

            'patient_id' => (int)$patient->getKey(),
            'patient_name' => trim(
                "{$patient->surname} "
                . "{$patientNameInitial}. "
                . "{$patientPatronymInitial}."
            ),

            'generic_name' => $drug->latin_name,
            'drug_form' => $request->drug_form,
            'dosage' => $request->dosage,
            'quantity' => $request->quantity,
            'standards' => $request->standard,
            'usage_instructions' => $request->usage_instructions,
            'prescription_form' => $isStrict
                ? '№ 148-1/у-88'
                : '№ 107-1/у',
            'issued_at' => now()->toDateString(),
            'validity_period' => $request->validity_period,
            'birth_at' => Carbon::parse(
                $request->birth_at
            )->toDateString(),
            'is_strict' => $isStrict,
        ];

        PrescriptionsJob::dispatch($data);

        return redirect()
            ->route(
                'doctors.patients.medical_card',
                $patient->id
            )
            ->with('success', 'Рецепт передан на создание.');
    }

    public function print(Request $request)
    {
        $patient = Patient::findOrFail($request->patient_id);
        $drug = Drug::where('id', $request->drug_id)->firstOrFail();
        $doctor = Auth::user()->doctor;

        $shortForms = [
            'Таблетки' => 'Tab',
            'Драже' => 'Dragee',
            'Ампулы' => 'Sol',
            'Капсулы' => 'Caps'
        ];

        $data = [
            'patientFullName' => $patient->surname . ' ' . substr($patient->name, 0, 1) . '. ' . substr($patient->patronym, 0, 1) . '.',
            'patientBirthday' => Carbon::parse($patient->birth_at)->format('d.m.Y'),
            'doctorFullName' => ($doctor->surname ?? 'Администратор') . ' ' . ($doctor->name ?? 'Тест') . ' ' . ($doctor->patronym ?? 'Админович'),
            'drugShortForm' => $shortForms[$request->drug_form] ?? $request->drug_form,
            'drugLatinName' => $this->toGenitiveCase($drug->latin_name),
            'drugDose' => $request->dosage,
            'drugQuantity' => $request->quantity,
            'drugStandardCount' => $request->ampule_volume ? $request->ampule_volume . ' мл' : '',
            'drugStandards' => $request->standard ?? '1',
            'drugUsingSchema' => $request->usage_instructions,
            'dateAsDay' => date('d'),
            'dateAsMonth' => now()->translatedFormat('F'),
            'dateAsYear' => date('Y'),
        ];


        $bladeTemplate = (bool)$drug->strict
            ? 'doctors.prescriptions.148-1у'
            : 'doctors.prescriptions.107-1у';

        $htmlContent = view(
            $bladeTemplate,
            $data
        )->render();

        return response($htmlContent);
    }

    public function printForTable($id)
    {
        $prescription = MedicalPrescription::findOrFail($id);

        $shortForms = [
            'Таблетки' => 'Tab',
            'Драже' => 'Dragee',
            'Ампулы' => 'Sol',
            'Капсулы' => 'Caps'
        ];

        $fioParts = explode(' ', $prescription->patient_name);
        $patientFullName = count($fioParts) >= 3
            ? $fioParts[0] . ' ' . mb_substr($fioParts[1], 0, 1) . '. ' . mb_substr($fioParts[2], 0, 1) . '.'
            : $prescription->patient_name;

        $data = [
            'patientFullName' => $patientFullName,
            'patientBirthday' => Carbon::parse($prescription->birth_at)->format('d.m.Y'),
            'doctorFullName' => $prescription->doctor_name,
            'drugShortForm' => $shortForms[$prescription->drug_form] ?? $prescription->drug_form,
            'drugLatinName' => $this->toGenitiveCase($prescription->generic_name),
            'drugDose' => $prescription->dosage,
            'drugQuantity' => $prescription->quantity,
            'drugStandardCount' => (
                (bool)$prescription->is_strict
                || str_contains(
                    (string)$prescription->prescription_form,
                    '148'
                )
            ) && $prescription->standards
                ? $prescription->standards . ' мл'
                : '',
            'drugStandards' => $prescription->standards,
            'drugUsingSchema' => $prescription->usage_instructions,
            'dateAsDay' => now()->format('d'),
            'dateAsMonth' => now()->translatedFormat('F'),
            'dateAsYear' => now()->format('Y'),
        ];

        $isStrict =
            (bool)$prescription->is_strict
            || str_contains(
                mb_strtolower(
                    trim((string)$prescription->prescription_form)
                ),
                '148'
            );

        $bladeTemplate = $isStrict
            ? 'doctors.prescriptions.148-1у'
            : 'doctors.prescriptions.107-1у';

        $htmlContent = view(
            $bladeTemplate,
            $data
        )->render();

        return response($htmlContent);
    }

    public function base(Request $request): View
    {
        /*
         * Не загружаем весь справочник диагнозов и не делаем with('diagnoses').
         * Для фильтра показаний используем уже существующую связь Drug::indications().
         */
        $query = Drug::query();

        /*
         * Фильтр по названию препарата.
         */
        if ($request->filled('name')) {
            $search = '%' . trim((string)$request->get('name')) . '%';

            $query->where(function ($drugQuery) use ($search): void {
                $drugQuery
                    ->where('name', 'ilike', $search)
                    ->orWhere('latin_name', 'ilike', $search);
            });
        }

        /*
         * Фильтр по фармакологической группе.
         */
        if ($request->filled('group')) {
            $query->where('group', $request->get('group'));
        }

        /*
         * Показания.
         *
         * 1. Если indications_ids заполнен — фильтруем по ID диагнозов.
         * 2. Если ID нет, но заполнен indications — ищем по коду МКБ
         *    или названию диагноза.
         *
         * Связь с препаратом идёт через medicine_indications.diagnose_id,
         * поэтому фильтрация выполняется через Drug::indications().
         */
        $indicationIds = collect(
            json_decode(
                (string)$request->get('indications_ids', ''),
                true
            ) ?: []
        )
            ->map(static fn($id): int => (int)$id)
            ->filter(static fn(int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($indicationIds !== []) {
            $query->whereHas(
                'indications',
                function ($indicationQuery) use ($indicationIds): void {
                    $indicationQuery->whereIn(
                        'diagnose_id',
                        $indicationIds
                    );
                }
            );
        } elseif ($request->filled('indications')) {
            $indicationSearch = trim(
                (string)$request->get('indications')
            );

            if ($indicationSearch !== '') {
                $like = '%' . $indicationSearch . '%';

                $query->whereHas(
                    'indications',
                    function ($indicationQuery) use ($like): void {
                        $indicationQuery->whereIn(
                            'diagnose_id',
                            function ($diagnosisQuery) use ($like): void {
                                $diagnosisQuery
                                    ->select('id')
                                    ->from('diagnoses')
                                    ->where(
                                        function ($query) use ($like): void {
                                            $query
                                                ->where(
                                                    'code',
                                                    'ilike',
                                                    $like
                                                )
                                                ->orWhere(
                                                    'title',
                                                    'ilike',
                                                    $like
                                                );
                                        }
                                    );
                            }
                        );
                    }
                );
            }
        }

        /*
         * Разрешён при беременности.
         */
        if ($request->has('pregnancy')) {
            $query->where('pregnancy', true);
        }

        /*
         * Разрешён при лактации.
         */
        if ($request->has('lactation')) {
            $query->where('lactation', true);
        }

        /*
         * Исключение препаратов по противопоказаниям.
         */
        if ($request->filled('contraindications_ids')) {
            $contraindicationIds = collect(
                json_decode(
                    (string)$request->get(
                        'contraindications_ids'
                    ),
                    true
                ) ?: []
            )
                ->map(static fn($id): int => (int)$id)
                ->filter(static fn(int $id): bool => $id > 0)
                ->unique()
                ->values()
                ->all();

            if ($contraindicationIds !== []) {
                $query->whereDoesntHave(
                    'contraindications',
                    function ($contraindicationsQuery) use (
                        $contraindicationIds
                    ): void {
                        $contraindicationsQuery->whereIn(
                            'contraindications_types.id',
                            $contraindicationIds
                        );
                    }
                );
            }
        }

        /*
         * Метаболизм не печенью.
         */
        if ($request->has('liver')) {
            $query->where('liver', false);
        }

        /*
         * Метаболизм не почками.
         */
        if ($request->has('kidneys')) {
            $query->where('kidneys', false);
        }

        /*
         * Получаем 15 препаратов страницы.
         */
        $drugs = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        /*
         * Для отображения показаний используем ту же связь indications(),
         * на которой уже работает подбор препаратов в API.
         *
         * Так мы не зависим от отдельной/устаревшей связи $drug->diagnoses.
         */
        $drugCollection = $drugs->getCollection();

        $drugCollection->load([
            'indications',
            'contraindications',
        ]);

        $diagnosisIds = $drugCollection
            ->flatMap(
                static fn(Drug $drug) => $drug->indications->pluck('diagnose_id')
            )
            ->map(static fn($id): int => (int)$id)
            ->filter()
            ->unique()
            ->values();

        $diagnosesById = $diagnosisIds->isEmpty()
            ? collect()
            : Diagnose::query()
                ->whereKey($diagnosisIds)
                ->get([
                    'id',
                    'code',
                    'title',
                ])
                ->keyBy('id');

        $drugCollection->each(
            function (Drug $drug) use ($diagnosesById): void {
                $displayIndications = $drug->indications
                    ->map(
                        static function ($medicineIndication) use (
                            $diagnosesById
                        ): ?array {
                            $diagnosis = $diagnosesById->get(
                                (int)$medicineIndication->diagnose_id
                            );

                            if (!$diagnosis) {
                                return null;
                            }

                            return [
                                'id' => (int)$diagnosis->id,
                                'code' => (string)$diagnosis->code,
                                'title' => (string)$diagnosis->title,
                                'indicated_in_russia' => (bool)(
                                    $medicineIndication->indicated_in_russia
                                    ?? false
                                ),
                                'indicated_by_fda' => (bool)(
                                    $medicineIndication->indicated_by_fda
                                    ?? false
                                ),
                            ];
                        }
                    )
                    ->filter()
                    ->unique('id')
                    ->values();

                $drug->setRelation('displayIndications', $displayIndications);
                $drug->setAttribute(
                    'display_forms',
                    $this->buildDrugDisplayForms($drug)
                );
                $drug->setAttribute(
                    'display_group_name',
                    (string)$drug->groupName()
                );
                $drug->setAttribute(
                    'display_prescription_form',
                    $drug->strict ? '№ 148-1/у-88' : '№ 107-1/у'
                );
                $drug->setAttribute(
                    'display_preferential_label',
                    $drug->preferential
                        ? 'Доступен по льготе'
                        : 'Недоступен по льготе'
                );
            }
        );

        $contraindications = ContraindicationsType::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        $groups = MedicineTypesEnum::getAllMatches();

        $totalDrugs = (int)$drugs->total();
        $currentPageCount = (int)$drugs->count();
        $currentPage = (int)$drugs->currentPage();
        $lastPage = max((int)$drugs->lastPage(), 1);

        $selectedContraindicationIds = collect(
            json_decode(
                (string)$request->get('contraindications_ids', ''),
                true
            ) ?: []
        )
            ->map(static fn($id): string => (string)$id)
            ->filter()
            ->values()
            ->all();

        $viewData = compact(
            'drugs',
            'groups',
            'contraindications',
            'totalDrugs',
            'currentPageCount',
            'currentPage',
            'lastPage',
            'selectedContraindicationIds'
        );

        return view(
            'doctors.prescriptions.prescriptions_base',
            array_merge($viewData, $this->prescriptionNavigationData())
        );
    }

    public function repeatPrescription(MedicalPrescription $prescription): RedirectResponse
    {
        $prescription->replicate()->save();

        return redirect()->back();
    }
}

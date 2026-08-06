<?php

namespace App\Http\Controllers\Doctors;

use App\Enums\MedicineTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\PrescriptionStoreRequest;
use App\Jobs\PrescriptionsJob;
use App\Models\ContraindicationsType;
use App\Models\Drug;
use App\Models\MedicalPrescription;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PrescriptionsController extends Controller
{
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
        if (substr($latinName, -1) === 'a')  return substr($latinName, 0, -1) . 'ae';

        return $latinName . 'is';
    }

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


    public function index(): View
    {
        $doctor = auth()->user()
            ->doctor()
            ->firstOrFail();

        $doctorId = (int) $doctor->id;
        $today = today();

        $drugGroups = Drug::query()
            ->whereNotNull('group')
            ->where('group', '<>', '')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        /*
         * Количество рецептов по группам препаратов за сегодня.
         */
        $statistics = [];

        foreach ($drugGroups as $group) {
            $statistics[$group] = MedicalPrescription::query()
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
                ->where(
                    'drugs.group',
                    $group
                )
                ->whereDate(
                    'medical_prescriptions.issued_at',
                    $today
                )
                ->distinct()
                ->count('medical_prescriptions.id');
        }

        /*
         * Журнал рецептов текущего врача.
         */
        $prescriptions = MedicalPrescription::query()
            ->where('doctor_id', $doctorId)
            ->select([
                'id',
                'patient_id',
                'patient_name',
                'generic_name',
                'issued_at',
            ])
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate(
                20,
                ['*'],
                'prescriptions_page'
            );

        /*
         * Базовый запрос по рецептам текущего врача за сегодня.
         */
//        $todayPrescriptionsQuery = MedicalPrescription::query()
//            ->where('doctor_id', $doctorId)
//            ->whereDate('issued_at', $today);


        // Рецептов оформленных сегодня
        $currentPrescription = MedicalPrescription::where('doctor_id', $doctorId)
            ->where('issued_at', '>=', now()->copy()->startOfDay())
            ->where('issued_at', '<=', now()->copy()->endOfDay())
            ->count('id');

        // Кол-во пациентов, получивших рецепт.
        $patientsWithPrescriptionsToday = MedicalPrescription::query()
            ->where('doctor_id', $doctorId)
            ->where('issued_at', '>=', now()->copy()->startOfDay())
            ->where('issued_at', '<=', now()->copy()->endOfDay())
            ->whereNotNull('patient_id')
            ->distinct()
            ->count('patient_id');


        $patientPrescriptionToday = MedicalPrescription::query()
            ->where('doctor_id', $doctorId)
            ->where('issued_at', '<=', now()->copy()->endOfDay())
            ->where('issued_at', '>=', now()->copy()->startOfDay ())
            ->whereNotNull('patient_id')
            ->distinct()
            ->count('patient_id');

        return view(
            'doctors.prescriptions.prescriptions_tables',
            compact(
                'statistics',
                'prescriptions',
                // 'prescriptedToday',
                'patientPrescriptionToday',
            // 'strictDispensedToday'
            )
        );
    }

    public function create(): View
    {
        $patients = Patient::select('name', 'surname', 'patronym', 'birth_at')->limit(10)->get();
        $drugs = Drug::select('name', 'latin_name')->limit(10)->get();

        return view('doctors.prescriptions.prescriptions_create', compact('patients', 'drugs'));
    }

    public function store(PrescriptionStoreRequest $request)
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

        $data = [
            'doctor_id' => (int) $doctor->getKey(),
            'doctor_name' => $this->getDoctorFullName(),

            'patient_id' => (int) $patient->getKey(),
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
            'prescription_form' => '№ 107-1/у',
            'issued_at' => now()->toDateString(),
            'validity_period' => $request->validity_period,
            'birth_at' => Carbon::parse(
                $request->birth_at
            )->toDateString(),
            'is_strict' => false,
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


        $htmlContent = view(
            'doctors.prescriptions.107-1у',
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
            'drugStandardCount' => '',
            'drugStandards' => $prescription->standards,
            'drugUsingSchema' => $prescription->usage_instructions,
            'dateAsDay' => now()->format('d'),
            'dateAsMonth' => now()->translatedFormat('F'),
            'dateAsYear' => now()->format('Y'),
        ];

        $htmlContent = view(
            'doctors.prescriptions.107-1у',
            $data
        )->render();

        return response($htmlContent);
    }

    public function base(Request $request): View
    {
        $query = Drug::query();

        // Фильтр по названию
        if ($request->filled('name')) {
            $query->where('name', 'ilike', '%' . $request->get('name') . '%');
        }

        // Фильтр по группе
        if ($request->filled('group')) {
            $query->where('group', $request->get('group'));
        }

        // Фильтр по беременности
        if ($request->has('pregnancy')) {
            $query->where('pregnancy', true);
        }

        // Фильтр по лактации
        if ($request->has('lactation')) {
            $query->where('lactation', true);
        }

        // Фильтр по противопоказаниям
        if ($request->filled('contraindications_ids')) {
            $ids = json_decode($request->get('contraindications_ids'), true);

            if (is_array($ids) && count($ids)) {
                $query->whereDoesntHave('contraindications', function ($q) use ($ids) {
                    $q->whereIn('contraindication_id', $ids);
                });
            }
        }

        // Фильтр по метаболизму печенью
        if ($request->has('liver')) {
            $query->where('liver', false);
        }

        // Фильтр по метаболизму почками
        if ($request->has('kidneys')) {
            $query->where('kidneys', false);
        }

        $drugs = $query->paginate(15)->withQueryString(); // сохраняем параметры при пагинации

        $contraindications = ContraindicationsType::pluck('name', 'id');
        $groups = MedicineTypesEnum::getAllMatches();

        return view('doctors.prescriptions.prescriptions_base', compact('drugs', 'groups', 'contraindications'));
    }

    public function repeatPrescription(MedicalPrescription $prescription): RedirectResponse
    {
        $prescription->replicate()->save();

        return redirect()->back();
    }
}

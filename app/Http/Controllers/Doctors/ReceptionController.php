<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRegistryRecordRequest;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ReceptionController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isDebug = app()->hasDebugModeEnabled();
        $doctorId = Auth::id();
        $doctors = Doctor::orderBy('surname');

        $receptionsQuery = RegistryRecord::with('patient');

        if (!$isDebug) {
            $receptionsQuery = $receptionsQuery->where('doctor_id', $doctorId)
                ->whereBetween('for_datetime', [
                    Carbon::now()->startOfDay(),
                    Carbon::now()->endOfDay()
                ]);
        }

        if ($request->filled('fullname') || $request->filled('birth_at') || $request->filled('address_residence')) {
            $receptionsQuery->whereHas('patient', function ($query) use ($request) {
                if ($request->filled('fullname')) {
                    $searchParts = explode(' ', $request->get('fullname'));
                    foreach ($searchParts as $part) {
                        $query->where(function ($q) use ($part) {
                            $q->where('surname', 'LIKE', "%{$part}%")
                                ->orWhere('name', 'LIKE', "%{$part}%")
                                ->orWhere('patronym', 'LIKE', "%{$part}%")
                                ->orWhere(DB::raw("CONCAT(LEFT(name, 1), '.', LEFT(patronym, 1), '.')"), 'LIKE', "%{$part}%");
                        });
                    }
                }

                if ($request->filled('birth_at')) {
                    $query->whereDate('birth_at', Carbon::parse($request->get('birth_at')));
                }

                if ($request->filled('address_residence')) {
                    $query->where('address_residence', 'ilike', "%" . $request->get('address_residence') . "%");
                }
            });
        }

        if (!$isDebug) {
            $doctor = $user->doctor()->first();
            $nurse = $user->nurse()->first();
            $clinics = $doctor?->clinic ?? $nurse?->clinic;
            $receptionsQuery->whereIn('clinic_id', $clinics?->pluck('id') ?? []);

            $hostClinic = $clinics?->pluck('name');
            $name = $doctor?->name ?? $nurse?->name ?? '';
            $surname = $doctor?->surname ?? $nurse?->surname ?? '';
            $patronym = $doctor?->patronym ?? $nurse?->patronym ?? '';
            $profession = $nurse?->profession ?? 'врач';
            $doctors = $doctors->where('clinic_id', $doctor->clinic_id ?? $nurse->clinic_id)
                ->get();
        } else {
            $hostClinic = Clinic::inRandomOrder()->first();
            $name = 'Администратор';
            $surname = 'Дебаггеров';
            $patronym = 'Тестович';
            $profession = 'дебаггер';

            $doctors = $doctors->get();
        }

        if ($request->filled('doctor_id')) {
            $receptionsQuery->where('doctor_id', $request->get('doctor_id'));
        }

        $receptions = $receptionsQuery->paginate(30);

        $futureReceptions = RegistryRecord::where('for_datetime', '>', Carbon::now()->addDay()->startOfDay());
        if (!$isDebug) {
            $futureReceptions = $futureReceptions->where('clinic_id', $doctor->clinic_id);
        }
        $futureReceptions = $futureReceptions->get()->count();

        return view('doctors.reception.table', compact(
            'receptions',
            'hostClinic',
            'name',
            'surname',
            'patronym',
            'profession',
            'doctors',
            'futureReceptions',
        ));
    }

    public function create(): View
    {
        if (!app()->hasDebugModeEnabled()) {
            $patients = auth()
                ->user()
                ->doctor
                ->clinic->pluck('name', 'id') ??
                auth()
                    ->user()
                    ->nurse
                    ->clinic->pluck('name', 'id');
            $doctors = Doctor::where('clinic_id', auth()
                ->user()
                ->doctor
                ->clinic
                ->id ?? auth()->user()->nurse->clinic->id);
        } else {
            $patients = Patient::orderBy('surname')->get();
            $doctors = Doctor::orderBy('surname')->get();
        }

        return view('doctors.reception.create', compact('patients', 'doctors'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'surname' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'patronym' => 'required|string|max:255',
            'birth_at' => 'required|date',
            'appointment_datetime' => 'required|date|after_or_equal:now',
            'address_registration' => 'required|string',
            'address_residence' => 'required|string',
            'serial' => 'required|string|max:10',
            'number' => 'required|string|max:15',
            'department_code' => 'required|string|max:7',
            'issued_by' => 'required|string',
            'issued_at' => 'required|date',
            'birth_place' => 'required|string',
            'snils' => 'required|string|max:14',
            'oms' => 'required|string|max:16',
            'doctor_id' => 'required|exists:doctors,id',
        ], [
            'required' => 'Поле ":attribute" обязательно для заполнения.',
            'string' => 'Поле ":attribute" должно быть строкой.',
            'max' => 'Поле ":attribute" не должно превышать :max символов.',
            'date' => 'Поле ":attribute" должно быть датой.',
            'after_or_equal' => 'Поле ":attribute" должно быть датой не раньше текущего момента.',
            'exists' => 'Значение поля ":attribute" не зарегистрировано в базе данных.'
        ],
        [
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'patronym' => 'Отчество',
            'birth_at' => 'Дата рождения',
            'appointment_datetime' => 'Дата приёма',
            'address_registration' => 'Адрес регистрации',
            'address_residence' => 'Адрес проживания',
            'serial' => 'Серия паспорта',
            'number' => 'Номер паспорта',
            'department_code' => 'Код подразделения',
            'issued_by' => 'Кем выдан',
            'issued_at' => 'Дата выдачи',
            'birth_place' => 'Место рождения',
            'snils' => 'СНИЛС',
            'oms' => 'ОМС',
            'doctor_id' => 'Лечащий врач'
        ]);


        // Ищем пациента по паспортным данным и ФИО
        $patient = Patient::updateOrCreate(
            [
                'serial' => $validatedData['serial'],
                'number' => $validatedData['number']
            ],
            [
                'surname' => $validatedData['surname'],
                'name' => $validatedData['name'],
                'patronym' => $validatedData['patronym'],
                'birth_at' => $validatedData['birth_at'],
                'address_registration' => $validatedData['address_registration'],
                'address_residence' => $validatedData['address_residence'],
                'department_code' => $validatedData['department_code'],
                'issued_by' => $validatedData['issued_by'],
                'issued_at' => $validatedData['issued_at'],
                'birth_place' => $validatedData['birth_place'],
                'snils' => $validatedData['snils'],
                'oms' => $validatedData['oms'],
                'doctor_id' => $validatedData['doctor_id'],
            ]
        );

        if (!app()->hasDebugModeEnabled()) {
            $clinic_id = auth()->user()->doctor->clinic_id;
            $doctor_id = auth()->user()->doctor->id;
        } else {
            $doctor = Doctor::find($validatedData['doctor_id']);

            $doctor_id = $validatedData['doctor_id'];
            $clinic_id = $doctor->clinic_id ?? null;
        }

        // Создаем запись в registry_records
        RegistryRecord::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor_id,
            'for_datetime' => Carbon::parse($validatedData['appointment_datetime']),
            'appointment' => false,
            'clinic_id' => $clinic_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('doctors.reception')
            ->with('success', 'Пациент успешно записан в регистратуру.');
    }

    public function archives(Request $request): View
    {
        $now = Carbon::now();
        $user = auth()->user();
        $isDebug = app()->hasDebugModeEnabled();
        $recordsQuery = RegistryRecord::where(function ($query) use ($now) {
            $query->where('appointment', true)
                ->orWhere('for_datetime', '<=', $now);
        });

        $doctors = Doctor::orderBy('surname');

        if (!$isDebug) {
            $doctor = $user->doctor()->first();
            $nurse = $user->nurse()->first();
            $clinics = $doctor?->clinic ?? $nurse?->clinic;

            $recordsQuery->whereIn('clinic_id', $clinics?->pluck('id') ?? []);
            $hostClinic = $clinics?->pluck('name');
            $name = $doctor?->name ?? $nurse?->name ?? '';
            $surname = $doctor?->surname ?? $nurse?->surname ?? '';
            $patronym = $doctor?->patronym ?? $nurse?->patronym ?? '';
            $profession = $nurse?->profession ?? 'врач';

            // отфильтровать докторов по клинике
            $doctors = $doctors->where('clinic_id', $doctor->clinic_id ?? $nurse->clinic_id)->get();
        } else {
            $hostClinic = Clinic::inRandomOrder()->first();
            $name = 'Администратор';
            $surname = 'Дебаггеров';
            $patronym = 'Тестович';
            $profession = 'дебаггер';
            $doctors = $doctors->get();
        }

        // 👇 фильтрация по врачу
        if ($request->filled('doctor_id')) {
            $recordsQuery->where('doctor_id', $request->get('doctor_id'));
        }

        $records = $recordsQuery->withTrashed()->paginate(30);

        return view('doctors.reception.archives', compact(
            'records',
            'hostClinic',
            'name',
            'surname',
            'patronym',
            'profession',
            'doctors'
        ));
    }

    public function edit($id)
    {
        $record = RegistryRecord::with('patient')->findOrFail($id);
        $doctors = Doctor::orderBy('surname')->get();

        return view('doctors.reception.edit', compact('record', 'doctors'));
    }

    public function update(UpdateRegistryRecordRequest $request, $id)
    {
        $record = RegistryRecord::with('patient')->findOrFail($id);

        $record->doctor_id = $request->doctor_id;
        $record->for_datetime = $request->appointment_datetime;
        $record->save();

        $patient = $record->patient;
        $patient->fill($request->only([
            'surname', 'name', 'patronym', 'birth_at',
            'address_registration', 'address_residence',
            'serial', 'number', 'department_code',
            'issued_by', 'issued_at', 'birth_place',
            'snils', 'oms'
        ]));
        $patient->save();

        return redirect()
            ->route('doctors.reception')
            ->with('success', 'Запись успешно обновлена.');
    }

    public function destroy($id)
    {
        $record = RegistryRecord::findOrFail($id);
        $record->delete(); // Soft delete — удаляется через `deleted_at`

        return redirect()
            ->route('doctors.reception')
            ->with('success', 'Запись успешно перенесена в архив.');
    }
}

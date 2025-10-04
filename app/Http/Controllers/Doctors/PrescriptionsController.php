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
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;

class PrescriptionsController extends Controller
{
    private function getMonthInRussian($month)
    {
        $months = [
            '01' => 'января', '02' => 'февраля', '03' => 'марта', '04' => 'апреля',
            '05' => 'мая', '06' => 'июня', '07' => 'июля', '08' => 'августа',
            '09' => 'сентября', '10' => 'октября', '11' => 'ноября', '12' => 'декабря',
        ];

        return $months[$month] ?? 'месяц';
    }

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

    private function getPrescriptionSeriesByDoctorAddress(string $address): string
    {
        $region = $this->getRegionFromAddress($address);

        $regionCodes = [
            'Москва' => '77', 'Санкт-Петербург' => '78', /* остальные регионы */
        ];

        if (!$region || !isset($regionCodes[$region])) {
            throw new InvalidArgumentException("Не удалось определить серию для адреса: '{$address}'.");
        }

        return $regionCodes[$region] . Carbon::now()->format('y');
    }

    private function getRegionFromAddress(string $address): ?string
    {
        $yandexApiKey = config('yandex.yandex_key');
        $url = "https://geocode-maps.yandex.ru/1.x/?apikey={$yandexApiKey}&geocode=" . urlencode($address) . "&format=json";

        $response = file_get_contents($url);
        if (!$response) return null;

        $data = json_decode($response, true);
        $components = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['Components'] ?? null;

        return $components[2]['name'] ?? null;
    }

    private function generatePrescriptionNumber(string $series): string
    {
        $lastNumber = MedicalPrescription::where('series', $series)->orderBy('number', 'desc')->value('number');
        return str_pad((string)(($lastNumber ?? 0) + 1), 6, '0', STR_PAD_LEFT);
    }

    public function index(): View
    {
        $doctorFullName = $this->getDoctorFullName();
        $drugGroups = Drug::distinct()->pluck('group');

        $statistics = [];
        foreach ($drugGroups as $group) {
            $statistics[$group] = MedicalPrescription::join('drugs', 'medical_prescriptions.generic_name', '=', 'drugs.latin_name')
                ->where('drugs.group', $group)
                ->where('medical_prescriptions.doctor_name', $doctorFullName)
                ->whereDate('medical_prescriptions.issued_at', today())
                ->count();
        }

        $prescriptions = MedicalPrescription::where('doctor_name', $doctorFullName)
            ->select('patient_name', 'generic_name', 'issued_at', 'id')
            ->paginate(20, ['*'], 'prescriptions_page');

        return view('doctors.prescriptions.prescriptions_tables', compact('statistics', 'prescriptions'));
    }

    public function create(): View
    {
        $patients = Patient::select('name', 'surname', 'patronym', 'birth_at')->limit(10)->get();
        $drugs = Drug::select('name', 'latin_name')->limit(10)->get();

        return view('doctors.prescriptions.prescriptions_create', compact('patients', 'drugs'));
    }

    public function store(PrescriptionStoreRequest $request)
    {
        $patient = Patient::findOrFail($request->post('patient_id'));
        $drug = Drug::findOrFail($request->post('drug_id'));

        $data = [
            'doctor_name' => $this->getDoctorFullName(),
            'patient_name' => "{$patient->surname} " . substr($patient->name, 0, 1) . ". " . substr($patient->patronym, 0, 1) . ".",
            'generic_name' => $drug->latin_name,
            'drug_form' => $request->drug_form,
            'dosage' => $request->dosage,
            'quantity' => $request->quantity,
            'standards' => $request->standard,
            'usage_instructions' => $request->usage_instructions,
            'prescription_form' => $drug->strict ? '№ 148-1/88-у' : '№ 107-1/у',
            'issued_at' => now(),
            'validity_period' => $request->validity_period,
            'birth_at' => Carbon::parse($request->birth_at)->format('Y-m-d'),
        ];

        if ($drug->strict) {
            $data['series'] = $this->getPrescriptionSeriesByDoctorAddress($doctor->address_job);
            $data['number'] = $this->generatePrescriptionNumber($data['series']);
        }

        PrescriptionsJob::dispatch($data);

        return redirect()->route('doctors.prescriptions')->with('success', 'Рецепт успешно создан.');
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

//        if ($drug->strict) {
//            $prescriptionNumber = PrescriptionNumber::firstOrCreate(['series' => '148СМ']);
//            $prescriptionNumber->increment('number');
//
//            $data['series'] = $prescriptionNumber->series;
//            $data['number'] = str_pad($prescriptionNumber->number, 6, '0', STR_PAD_LEFT);
//        }

        $bladeTemplate = $drug->strict
            ? 'doctors.prescriptions.148-1у'
            : 'doctors.prescriptions.107-1у';
        $htmlContent = view($bladeTemplate, $data)->render();

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
            'drugStandardCount' => $prescription->prescription_form === '148-1/у-88' && $prescription->standards
                ? $prescription->standards . ' мл'
                : '',
            'drugStandards' => $prescription->standards,
            'drugUsingSchema' => $prescription->usage_instructions,
            'dateAsDay' => now()->format('d'),
            'dateAsMonth' => now()->translatedFormat('F'),
            'dateAsYear' => now()->format('Y'),
        ];

        $bladeTemplate = $prescription->prescription_form === '148-1/у-88'
            ? 'doctors.prescriptions.148-1у'
            : 'doctors.prescriptions.107-1у';

        $htmlContent = view($bladeTemplate, $data)->render();

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
                // Предположим: у тебя есть связь drugs → drug_contraindication (many-to-many)
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
}

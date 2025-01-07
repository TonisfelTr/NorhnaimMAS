<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrescriptionStoreRequest;
use App\Jobs\PrescriptionsJob;
use App\Models\Drug;
use App\Models\MedicalPrescription;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use InvalidArgumentException;
use PhpOffice\PhpWord\TemplateProcessor;

class PrescriptionsController extends Controller
{
    /**
     * Возвращает название месяца на русском.
     *
     * @param int $month
     * @return string
     */
    private function getMonthInRussian($month)
    {
        $months = [
            '01' => 'января',
            '02' => 'февраля',
            '03' => 'марта',
            '04' => 'апреля',
            '05' => 'мая',
            '06' => 'июня',
            '07' => 'июля',
            '08' => 'августа',
            '09' => 'сентября',
            '10' => 'октября',
            '11' => 'ноября',
            '12' => 'декабря',
        ];

        return $months[$month] ?? 'месяц';
    }

    private function toGenitiveCase($latinName) {
        $exceptions = [
            'aqua' => 'aquae',
            'spiritus' => 'spiritus',
            'oleum' => 'olei'
            // Добавьте больше исключений при необходимости
        ];

        if (array_key_exists(strtolower($latinName), $exceptions)) {
            return $exceptions[strtolower($latinName)];
        }

        // Проверка окончания слова
        if (substr($latinName, -2) === 'um') {
            return substr($latinName, 0, -2) . 'i'; // Преобразование для среднего рода (-um -> -i)
        }

        if (substr($latinName, -2) === 'us') {
            return substr($latinName, 0, -2) . 'i'; // Преобразование для мужского рода (-us -> -i)
        }

        if (substr($latinName, -1) === 'a') {
            return substr($latinName, 0, -1) . 'ae'; // Преобразование для женского рода (-a -> -ae)
        }

        // Универсальный случай: добавление окончания -is
        return $latinName . 'is';
    }

    private function getDoctorFullName(): string {
        $currentDoctorUser = auth()->user()->userable;
        $doctorSurname = $currentDoctorUser->surname;
        $doctorName = substr($currentDoctorUser->name, 0, 1);
        $doctorPatronym = substr($currentDoctorUser->patronym, 0, 1);
        return "{$doctorSurname} {$doctorName}. {$doctorPatronym}.";
    }

    private function getPrescriptionSeriesByDoctorAddress(string $address): string
    {
        // Список регионов и их кодов для рецептов
        $regionCodes = [
            'Москва' => '77',
            'Санкт-Петербург' => '78',
            'Республика Адыгея' => '01',
            'Республика Башкортостан' => '02',
            'Республика Бурятия' => '03',
            'Республика Алтай' => '04',
            'Республика Дагестан' => '05',
            'Республика Ингушетия' => '06',
            'Кабардино-Балкарская Республика' => '07',
            'Республика Калмыкия' => '08',
            'Карачаево-Черкесская Республика' => '09',
            'Республика Карелия' => '10',
            'Республика Коми' => '11',
            'Республика Марий Эл' => '12',
            'Республика Мордовия' => '13',
            'Республика Саха (Якутия)' => '14',
            'Республика Северная Осетия' => '15',
            'Республика Татарстан' => '16',
            'Республика Тыва' => '17',
            'Удмуртская Республика' => '18',
            'Республика Хакасия' => '19',
            'Чеченская Республика' => '20',
            'Чувашская Республика' => '21',
            'Алтайский край' => '22',
            'Забайкальский край' => '75',
            'Краснодарский край' => '23',
            'Красноярский край' => '24',
            'Пермский край' => '59',
            'Приморский край' => '25',
            'Ставропольский край' => '26',
            'Хабаровский край' => '27',
            'Амурская область' => '28',
            'Архангельская область' => '29',
            'Астраханская область' => '30',
            'Белгородская область' => '31',
            'Брянская область' => '32',
            'Владимирская область' => '33',
            'Волгоградская область' => '34',
            'Вологодская область' => '35',
            'Воронежская область' => '36',
            'Еврейская автономная область' => '79',
            'Ивановская область' => '37',
            'Иркутская область' => '38',
            'Калининградская область' => '39',
            'Калужская область' => '40',
            'Камчатский край' => '41',
            'Кемеровская область' => '42',
            'Кировская область' => '43',
            'Костромская область' => '44',
            'Курганская область' => '45',
            'Курская область' => '46',
            'Ленинградская область' => '47',
            'Липецкая область' => '48',
            'Магаданская область' => '49',
            'Московская область' => '50',
            'Мурманская область' => '51',
            'Нижегородская область' => '52',
            'Новгородская область' => '53',
            'Новосибирская область' => '54',
            'Омская область' => '55',
            'Оренбургская область' => '56',
            'Орловская область' => '57',
            'Пензенская область' => '58',
            'Псковская область' => '60',
            'Ростовская область' => '61',
            'Рязанская область' => '62',
            'Самарская область' => '63',
            'Саратовская область' => '64',
            'Сахалинская область' => '65',
            'Свердловская область' => '66',
            'Смоленская область' => '67',
            'Тамбовская область' => '68',
            'Томская область' => '69',
            'Тверская область' => '70',
            'Тульская область' => '71',
            'Ульяновская область' => '73',
            'Челябинская область' => '74',
            'Ярославская область' => '76',
            'Севастополь' => '92',
            'Республика Крым' => '91',
            'Ханты-Мансийский автономный округ' => '86',
            'Ямало-Ненецкий автономный округ' => '89',
            'Чукотский автономный округ' => '87',
        ];

        // Используем функцию для определения региона по адресу
        $region = $this->getRegionFromAddress($address);

        if (!$region || !isset($regionCodes[$region])) {
            throw new InvalidArgumentException("Не удалось определить серию для адреса: '{$address}'.");
        }

        $regionCode = $regionCodes[$region];
        $currentYear = Carbon::now()->format('y'); // Получаем текущий год в формате двух цифр

        return $regionCode . $currentYear; // Формируем серию
    }

    private function getRegionFromAddress(string $address): ?string
    {
        $yandexApiKey = config('yandex.yandex_key');
        $url = "https://geocode-maps.yandex.ru/1.x/?apikey={$yandexApiKey}&geocode=" . urlencode($address) . "&format=json";

        $response = file_get_contents($url);

        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);

        if (isset($data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'])) {
            $geoObject = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];

            // Пробуем найти область в компоненте 'province'
            $components = $geoObject['metaDataProperty']['GeocoderMetaData']['Address']['Components'];
            return $components[2]['name'];
        }

        return null; // Если ничего не нашли
    }

    // Генерация номера рецепта
    private function generatePrescriptionNumber(string $series): string
    {
        $lastNumber = MedicalPrescription::where('series', $series)
                                         ->orderBy('number', 'desc')
                                         ->value('number');

        $newNumber = $lastNumber ? (int)$lastNumber + 1 : 1;

        return str_pad((string)$newNumber, 6, '0', STR_PAD_LEFT);
    }

    public function index(): View {
        $drugs = Drug::select('name', 'forms')->paginate(20, ['*'], 'drugs_page');

        $doctorFullName = $this->getDoctorFullName();

        // Получение всех групп из таблицы Drugs
        $drugGroups = Drug::select('group')->distinct()->pluck('group');

        // Инициализация статистики
        $statistics = [];

        foreach ($drugGroups as $group) {
            $statistics[$group] = MedicalPrescription::join('drugs', 'medical_prescriptions.generic_name', '=', 'drugs.latin_name')
                                                     ->where('drugs.group', $group)
                                                     ->where('medical_prescriptions.doctor_name', $doctorFullName)
                                                     ->whereDate('medical_prescriptions.issued_at', Carbon::now()->toDateString())
                                                     ->count();
        }

        $prescriptions = MedicalPrescription::where('doctor_name', auth()->user()->userable->shortFullName())
                                            ->select('patient_name', 'generic_name', 'issued_at', 'id')
                                            ->paginate(20, ['*'], 'prescriptions_page');

        return view('doctors.prescriptions.prescriptions_tables', compact('drugs', 'statistics', 'prescriptions'));
    }

    public function create(): View {
        $patients = Patient::select('name', 'surname', 'patronym', 'birth_at')->get(10);
        $drugs = Drug::select('name', 'latin_name')->get(10);

        return view('doctors.prescriptions.prescriptions_create', compact('patients', 'drugs'));
    }

    public function store(PrescriptionStoreRequest $request) {
        $data = [];

        $currentDoctorUser = auth()->user()->userable;
        $doctorSurname = $currentDoctorUser->surname;
        $doctorName = substr($currentDoctorUser->name, 0, 1);
        $doctorPatronym = substr($currentDoctorUser->patronym, 0, 1);
        $doctorFullName = "{$doctorSurname} {$doctorName}. {$doctorPatronym}.";

        $patient = Patient::select('name', 'patronym', 'surname')
                          ->where('id', $request->post('patient_id'))
                          ->first();
        $patientName = substr($patient->name, 0, 1);
        $patientPatronym = substr($patient->patronym, 0, 1);
        $patientFullName = "{$patient->surname} {$patientName}. {$patientPatronym}.";

        $drug = Drug::select('latin_name', 'strict')->where('id', $request->post('drug_id'))->first();

        $data['doctor_name'] = $doctorFullName;
        $data['patient_name'] = $patientFullName;
        $data['generic_name'] = $drug->latin_name;
        $data['drug_form'] = $request->post('drug_form');
        $data['dosage'] = $request->post('dosage');
        $data['quantity'] = $request->post('quantity');
        $data['standards'] = $request->post('standard');
        $data['usage_instructions'] = $request->post('usage_instructions');
        $data['prescription_form'] = $drug->strict ? '№ 148-1/88-у' : '№ 107-1/у';
        $data['issued_at'] = Carbon::now();
        $data['validity_period'] = $request->post('validity_period');
        $data['birth_at'] = Carbon::parse($request->post('birth_at'))->format('Y-m-d');

        if ($drug->strict) {
            $data['series'] = $this->getPrescriptionSeriesByDoctorAddress(auth()->user()->userable->address_job);
            $data['number'] = $this->generatePrescriptionNumber($data['series']);
        } else {
            $data['series'] = null;
            $data['number'] = null;
        }

        PrescriptionsJob::dispatch($data);

        return redirect()->route('doctors.prescriptions')->with('success', 'Рецепт был успешно создан. Теперь Вы можете его распечатать.');
    }

    public function print(Request $request) {
        $patient = Patient::find($request->post('patient_id'));
        $drug = Drug::find($request->post('drug_id'));

        $month = $this->getMonthInRussian(date('m'));
        $doctorFullName = $this->getDoctorFullName();

        $patientName = substr($patient->name, 0, 1);
        $patientPatronym = substr($patient->patronym, 0, 1);
        $patientFullName = "{$patient->surname} {$patientName} {$patientPatronym}";
        $patientBirthday = Carbon::parse($patient->birth_at)->format('d.m.Y');

        $drugShortForm = match ($request->post('drug_form')) {
            'Таблетки' => 'Tab',
            'Драже'    => 'Dragee',
            'Ампулы'   => 'Sol',
            'Капли'    => 'Gtt',
            default    => '?'
        };

        $drugLatinName = $this->toGenitiveCase($drug->latin_name);
        $drugQuantity  = $request->post('quantity');
        $drugDose      = $request->post('dosage');
        $drugStandards = $request->post('standard');
        $drugUsingSchema = $request->post('usage_instructions');
        $drugStandardCount = match ($request->post('drug_form')) {
            'Таблетки' => 'tab.',
            'Ампулы' => 'amp.',
            'Капсулы' => 'caps.',
            'Драже'   => 'dragee.',
            default => ''
        };

        $totalUnits = (int)$drugQuantity * (int)$drugStandards;
        $dosePerDay = (float)$drugDose * (int)$request->post('taking_count');
        $daysCount = $dosePerDay > 0 ? floor($totalUnits / $dosePerDay) : 0;

        if (!$drug->strict) {
            return view(
                'doctors.prescriptions.107-1у',
                compact(
                    'month',
                    'doctorFullName',
                    'patientFullName',
                    'patientBirthday',
                    'drugShortForm',
                    'drugLatinName',
                    'drugQuantity',
                    'drugDose',
                    'drugStandards',
                    'drugStandardCount',
                    'drugUsingSchema',
                    'daysCount'
                )
            );
        } else {
            $series = $this->getPrescriptionSeriesByDoctorAddress(auth()->user()->userable->address_job);
            $number = $this->generatePrescriptionNumber($series);
            return view(
                'doctors.prescriptions.148-1у',
                compact(
                    'month',
                    'doctorFullName',
                    'patientFullName',
                    'patientBirthday',
                    'drugShortForm',
                    'drugLatinName',
                    'drugQuantity',
                    'drugDose',
                    'drugStandards',
                    'drugStandardCount',
                    'drugUsingSchema',
                    'daysCount',
                    'series',
                    'number'
                )
            );
        }
    }

    public function printFromTable(int $id) {
        // Находим рецепт по ID
        $prescription = MedicalPrescription::findOrFail($id);

        $month = $this->getMonthInRussian(date('m', strtotime($prescription->issued_at)));
        $doctorFullName = $this->getDoctorFullName();

        $patientFullName = $prescription->patient_name;
        $patientBirthday = Carbon::parse($prescription->birth_at)->format('d.m.Y');

        $drugShortForm = match ($prescription->drug_form) {
            'Таблетки' => 'Tab',
            'Драже'    => 'Dragee',
            'Ампулы'   => 'Sol',
            'Капли'    => 'Gtt',
            default    => '?'
        };

        $drugLatinName = $this->toGenitiveCase($prescription->generic_name);
        $drugQuantity  = $prescription->quantity;
        $drugDose      = $prescription->dosage;
        $drugStandards = $prescription->standards;
        $drugUsingSchema = $prescription->usage_instructions;
        $drugStandardCount = match ($prescription->drug_form) {
            'Таблетки' => 'tab.',
            'Ампулы' => 'amp.',
            'Капсулы' => 'caps.',
            'Драже'   => 'dragee.',
            default => ''
        };

        $totalUnits = (int)$drugQuantity * (int)$drugStandards;
        $dosePerDay = (float)$drugDose * (int)$prescription->taking_count;
        $daysCount = $dosePerDay > 0 ? floor($totalUnits / $dosePerDay) : 0;

        $dateAsDay = Carbon::parse($prescription->issued_at)->format('d');
        $dateAsMonth = $this->getMonthInRussian(Carbon::parse($prescription->issued_at)->format('m'));
        $dateAsYear = Carbon::parse($prescription->issued_at)->format('Y');

        if ($prescription->prescription_form == "№ 107-1/у") {
            // Возвращаем шаблон для обычного рецепта
            return view(
                'doctors.prescriptions.107-1у',
                compact(
                    'dateAsDay',
                    'dateAsMonth',
                    'dateAsYear',
                    'month',
                    'doctorFullName',
                    'patientFullName',
                    'patientBirthday',
                    'drugShortForm',
                    'drugLatinName',
                    'drugQuantity',
                    'drugDose',
                    'drugStandards',
                    'drugStandardCount',
                    'drugUsingSchema',
                    'daysCount'
                )
            );
        } else {
            // Генерируем серию и номер для строгого рецепта
            $series = $this->getPrescriptionSeriesByDoctorAddress(auth()->user()->userable->address_job);
            $number = $this->generatePrescriptionNumber($series);

            // Возвращаем шаблон для рецепта строгого учета
            return view(
                'doctors.prescriptions.148-1у',
                compact(
                    'dateAsDay',
                    'dateAsMonth',
                    'dateAsYear',
                    'month',
                    'doctorFullName',
                    'patientFullName',
                    'patientBirthday',
                    'drugShortForm',
                    'drugLatinName',
                    'drugQuantity',
                    'drugDose',
                    'drugStandards',
                    'drugStandardCount',
                    'drugUsingSchema',
                    'daysCount',
                    'series',
                    'number'
                )
            );
        }
    }
}

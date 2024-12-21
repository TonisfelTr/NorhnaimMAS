<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistryRecordDeleteRequest;
use App\Http\Requests\RegistryRecordUpdateRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RegistryRecordController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $forDatetime = $request->get('for_datetime'); // Дата и время

        if (!$doctorId || !$forDatetime) {
            return response()->json(['error' => 'Не указаны необходимые параметры.'], 400);
        }

        // Проверяем, существует ли запись с таким временем и врачом
        $exists = RegistryRecord::where('doctor_id', $doctorId)
                                ->where('for_datetime', $forDatetime)
                                ->exists();

        return response()->json(['available' => !$exists]);
    }

    public function index(Request $request): View {
        $search = $request->get('search');

        $records = RegistryRecord::query()
                                 ->when($search, function ($query) use ($search) {
                                     $dateFormats = ['Y-m-d', 'd.m.Y', 'm-d', 'd.m'];
                                     $date = null;

                                     foreach ($dateFormats as $format) {
                                         try {
                                             if (in_array($format, ['m-d', 'd.m'])) {
                                                 $searchWithYear = $search . '.' . date('Y');
                                                 $parsedDate = Carbon::createFromFormat($format . '.Y', $searchWithYear, 'UTC');
                                             } else {
                                                 $parsedDate = Carbon::createFromFormat($format, $search, 'UTC');
                                             }

                                             if ($parsedDate && $parsedDate->format($format) === $search) {
                                                 $date = $parsedDate->format('Y-m-d');
                                                 break;
                                             }
                                         } catch (\Exception $e) {
                                             // Игнорируем ошибку парсинга и продолжаем
                                         }
                                     }

                                     if ($date) {
                                         $query->whereDate('for_datetime', $date);
                                     } else {
                                         $query->whereHas('patient', function ($q) use ($search) {
                                             $q->where('name', 'ilike', '%' . $search . '%')
                                               ->orWhere('surname', 'ilike', '%' . $search . '%')
                                               ->orWhere('patronym', 'ilike', '%' . $search . '%');
                                         })
                                               ->orWhereHas('doctor', function ($q) use ($search) {
                                                   $q->where('name', 'ilike', '%' . $search . '%')
                                                     ->orWhere('surname', 'ilike', '%' . $search . '%')
                                                     ->orWhere('patronym', 'ilike', '%' . $search . '%');
                                               });
                                     }
                                 })
                                 ->paginate(20);

        return view('adminpanel.registry', compact('records'));
    }

    public function create() {
        $doctors = Doctor::all();
        $patients = Patient::all();

        return view('adminpanel.service.registry_new', compact('doctors', 'patients'));
    }

    public function store(Request $request) {
        $for_datetime = $request->post('for_datetime');
        $formatted_dt = Carbon::parse($for_datetime)->format('Y-m-d H:i:s');

        $registry = new RegistryRecord();
        $registry->doctor_id = $request->post('doctor_id');
        $registry->patient_id = $request->post('patient_id');
        $registry->for_datetime = $formatted_dt;
        $registry->appointment = $request->post('appointment') ?? false;
        $registry->save();

        return redirect()->route('admin.dictionary.registration')->with([
            'status' => 'record.success',
            'message' => 'Запись была успешно создана.'
        ]);
    }

    public function delete(RegistryRecordDeleteRequest $request, int $registry_id) {
        RegistryRecord::findOrFail($registry_id)->delete();

        return redirect()->route('admin.dictionary.registration')->with([
            'status' => 'record.success',
            'message' => 'Запись была успешно удалена.'
        ]);
    }

    public function massDelete(RegistryRecordDeleteRequest $request) {
        RegistryRecord::whereIn('id', $request->get('selected'))->delete();

        return redirect()->route('admin.dictionary.registration')->with([
            'status' => 'record.success',
            'message' => 'Выбранные записи успешно удалены.'
        ]);
    }

    public function edit(int $record_id) {
        $record = RegistryRecord::findOrFail($record_id);
        $doctors = Doctor::all();
        $patients = Patient::all();

        return view('adminpanel.service.registry_edit', compact('record', 'doctors', 'patients'));
    }

    public function save(RegistryRecordUpdateRequest $request, int $record_id) {
        $formattedDT = Carbon::parse($request->post('for_datetime'))->format('Y-m-d H:i:s');

        $record = RegistryRecord::findOrFail($record_id);
        $record->doctor_id = $request->post('doctor_id');
        $record->patient_id = $request->post('patient_id');
        $record->appointment = $request->post('appointment');
        $record->for_datetime = $formattedDT;
        $record->save();

        return redirect()->route('admin.dictionary.registration')->with([
            'status' => 'record.success',
            'message' => 'Запись была успешно отредактирована.'
        ]);
    }
}

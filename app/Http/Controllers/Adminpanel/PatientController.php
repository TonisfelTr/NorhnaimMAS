<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
use App\Http\Requests\PatientUpdateRequest;
use App\Http\Requests\UserRoleStoreRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View {
        if ($request->has('search')) {
            $search = $request->get('search');
            $patients = Patient::where('surname', 'ilike', "%$search%")
                    ->orWhere('name', 'ilike', "%$search%")
                    ->orWhere('patronym', 'ilike', "%$search%")
                    ->paginate(20);
        } else {
            $patients = Patient::paginate(20);
        }

        return view('adminpanel.sub-users.patient', compact('patients'));
    }

    public function store(UserRoleStoreRequest $request): View|RedirectResponse {
        $method = $request->getMethod();

        if ($method == 'POST') {
            (new Patient($request->toArray()))->save();

            return redirect()->route('admin.users.patients')->with([
                'message' => 'Создание нового пациента прошло успешно!',
                'status' => 'patients.success']);
        } else {
            return view('adminpanel.service.patient_new');
        }
    }

    public function delete(int $patient_id): RedirectResponse {
        Patient::find($patient_id)->delete();

        return redirect()->route('admin.users.patients')->with([
            'message' => 'Запись пациента была удалена.',
            'status' => 'patients.success'
        ]);
    }

    public function massDelete(MassDeleteRequest $request): RedirectResponse {
        if (!group()->patient_delete) {
            return redirect()->back()->withErrors('У вас недостаточно прав.');
        }

        Patient::whereIn('id', $request->post('selected'))->delete();

        return redirect()->back()->with([
            'message' => 'Выбранные записи пациентов были удалены.',
            'status' => 'patients.success'
        ]);
    }

    public function edit(int $patient_id): View {
        $patient = Patient::findOrFail($patient_id);

        return view('adminpanel.service.patient_edit', compact('patient'));
    }

    public function save(PatientUpdateRequest $request, int $patient_id) {
        $patient = Patient::findOrFail($patient_id);
        $patient->name = $request->post('name');
        $patient->surname = $request->post('surname');
        $patient->patronym = $request->post('patronym');
        $patient->married = $request->post('married');
        $patient->address_job = $request->post('address_job');
        $patient->address_registration = $request->post('address_registration');
        $patient->address_residence = $request->post('address_residence');
        $patient->profession = $request->post('profession');
        $patient->disability = $request->post('disability', false);
        $patient->socially_dangerous = $request->post('socially_dangerous', false);
        $patient->save();

        return redirect()->route('admin.users.patients')->with('Данные о пациенте были успешно обновлены.');
    }
}

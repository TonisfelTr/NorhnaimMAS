<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
use App\Http\Requests\SavePatientsRequest;
use App\Http\Requests\UserRoleStoreRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(): View {
        $patients = Patient::paginate(20);

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

    public function delete(MassDeleteRequest $request): RedirectResponse {
        Patient::whereIn('id', $request->all()->selected)->delete();

        return redirect()->route('admin.users.patients')->with('Запись врача была успешно удалена!');
    }

    public function edit(int $patient_id): View {
        $patient = Patient::findOrFail($patient_id);

        return view('adminpanel.service.patient_edit', compact('patient'));
    }

    public function save(SavePatientsRequest $request, int $patient_id) {
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

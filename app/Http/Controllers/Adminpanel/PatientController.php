<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
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
}

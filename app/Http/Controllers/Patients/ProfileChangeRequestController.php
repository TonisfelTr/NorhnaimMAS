<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\PatientProfileChangeRequestFormRequest;
use App\Services\PatientProfileChangeService;
use Illuminate\Http\RedirectResponse;

class ProfileChangeRequestController extends Controller
{
    public function store(
        PatientProfileChangeRequestFormRequest $request,
        PatientProfileChangeService $service
    ): RedirectResponse {
        $user = $request->user();
        $patient = $user->patient()->firstOrFail();

        $service->submit($user, $patient, $request->validated());

        return redirect()
            ->route('patients.profile.index', ['tab' => 'identity'])
            ->with('success', 'Запрос на изменение данных отправлен на рассмотрение.');
    }
}

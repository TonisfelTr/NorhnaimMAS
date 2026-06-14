<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\ContraindicationsType;
use App\Models\Drug;
use App\Models\LabParameter;
use App\Models\LabResearchTemplate;
use App\Models\Patient;
use App\Models\PatientCondition;
use App\Models\Test;
use App\Models\TestSession;
use App\Services\PrescriptionAiSelectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class APIController extends Controller
{
    public function storeLabTemplate(Request $request): JsonResponse
    {
        $template = new LabResearchTemplate();
        $template->name = $request->name;
        $template->lab_parameters = collect($request->lab_parameters)->map(fn ($current) => intval($current));
        $template->doctor_id = auth()->user()->doctor?->id ?? 18;
        $template->save();

        return response()->json($template);
    }

    public function autosave(Request $r, TestSession $session)
    {
        abort_unless($session->is_locked && auth()->id()===$session->locked_by, 403);

        $token = $r->header('X-Test-Pin-Token');
        $ok = $token && Cache::get("test_api_token:{$session->id}:".session()->getId()) === $token;
        abort_unless($ok, 423);

        return response()->noContent();
    }

    public function prescriptionAutofill(
        Request $request,
        PrescriptionAiSelectionService $service
    ): JsonResponse {
        $validated = $request->validate([
            'patient_id' => ['nullable', 'integer'],
            'diagnosis_code' => ['nullable', 'string', 'max:32'],
            'birth_at' => ['nullable', 'date'],
            'indication_source' => ['nullable', 'in:russia,fda'],
        ]);

        if (Cache::has("prescription-" . $request->indication_source . session()->getId())) {
            return response()->json(Cache::get("prescription-" . $request->indication_source . session()->getId()));
        }

        $result = $service->process($validated);

        Cache::put("prescription-" . $request->indication_source . session()->getId(), $result, 60);

        return response()->json($result);
    }
}

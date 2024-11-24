<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Patient;
use \Illuminate\Http\JsonResponse;

class AjaxController extends Controller
{
    public function medicineList(int $groupIdentificator): JsonResponse {
        return response()->json([
            "data" => Drug::whereGroup($groupIdentificator)->with('receptors')->get()
        ]);
    }

    public function getDoctors(): JsonResponse {
        return response()->json([
            'status' => 'success',
            'items' => Doctor::get(['id', 'name', 'surname', 'patronym'])->toArray()
            ]);
    }

    public function getPatient(): JsonResponse {
        return response()->json([
            'status' => 'success',
            'items' => Patient::get(['id', 'name', 'surname', 'patronym'])->toArray()
            ]);
    }
}

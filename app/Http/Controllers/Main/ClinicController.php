<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;

class ClinicController extends Controller
{
    public function index() {
        $clinics = Clinic::paginate(12, ['*'], 'clinics_page'); // Параметр 'clinics_page' для клиник
        $doctors = Doctor::paginate(12, ['*'], 'doctors_page'); // Параметр 'doctors_page' для врачей

        return view('main.clinics', compact('clinics', 'doctors'));
    }
}

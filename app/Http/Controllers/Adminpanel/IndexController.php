<?php

namespace app\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\User;

class IndexController extends Controller
{
    public function __invoke() {
        // Users data
        $doctorsCount = Doctor::all()->count();
        $patientCount = Patient::all()->count();
        $adminsCount  = User::whereUserableType('administrators')->get()->count();

        // Dictionaries data
        $clinicsCount = Clinic::all()->count() ?? 0;
        $drugsCount = Drug::all()->count() ?? 0;
        $diagnosesCount = Diagnose::all()->count() ?? 0;

        return view('adminpanel.main', compact(
            'doctorsCount',
            'patientCount',
            'adminsCount',
            'clinicsCount',
            'drugsCount',
            'diagnosesCount'
        ));
    }
}

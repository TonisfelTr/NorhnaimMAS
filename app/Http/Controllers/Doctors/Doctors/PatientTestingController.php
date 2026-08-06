<?php

namespace App\Http\Controllers\Doctors\Doctors;

use App\Http\Controllers\Controller;

class PatientTestingController extends Controller
{
    public function form(string $token)
    {
        return view('patient.testing.session', compact('token'));
    }
}

<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Consilium;
use App\Models\ConsiliumsDoctor;
use App\Models\ConsiliumsPatient;
use App\Models\Patient;
use Illuminate\Http\Request;

class ConsiliumController extends Controller
{
    public function store(Request $request)
    {
        $consilium = new Consilium();
        $consilium->title = $request->title;
        $consilium->datetime = $request->datetime;
        $consilium->description = $request->description;
        $consilium->number = fake()->numerify('#########');
        $consilium->save();

        $patient = new ConsiliumsPatient();
        $patient->consilium_id = $consilium->id;
        $patient->patient_id = $request->patient_ids[0];
        $patient->save();

        foreach ($request->doctor_ids as $doctor) {
            $consiliumDoctor = new ConsiliumsDoctor();
            $consiliumDoctor->doctor_id = $doctor;
            $consiliumDoctor->consilium_id = $consilium->id;
            $consiliumDoctor->save();
        }

        return redirect()->back()->with('Консилиум был успешно создан.');
    }
}

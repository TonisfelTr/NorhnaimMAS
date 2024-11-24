<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicManipulationRequest;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClinicController extends Controller
{
    public function index(): View {
        $clinics = Clinic::paginate(20);

        return view('adminpanel.sub-dictionaries.clinic', compact('clinics'));
    }

    public function store(ClinicManipulationRequest $request): RedirectResponse|View {
        if ($request->getMethod() == 'POST') {
            $data = $request->post();

            $clinic = new Clinic();
            $clinic->name = $data['name'];
            $clinic->address = $data['address'];
            $clinic->description = $data['description'];
            $clinic->save();

            return redirect()->route('admin.dictionary.clinics')->with([
                                                                           'status' => 'clinics.success',
                                                                           'message' => "Клиника \"{$clinic->name}\" была успешно добавлена!"
                                                                       ]);
        } else {
            return view('adminpanel.sub-dictionaries.clinic_new');
        }
    }
}

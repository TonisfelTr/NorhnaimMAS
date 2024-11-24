<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicManipulationRequest;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function edit(int $clinic_id) {
        $clinic = Clinic::findOrFail($clinic_id);
        $services = DB::table('services')->where('clinic_id', $clinic_id)->get();

        return view('adminpanel.service.clinic_edit', compact('clinic', 'services'));
    }

    public function save(Request $request, int $clinic_id): RedirectResponse {
        $clinic = Clinic::findOrFail($clinic_id);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('photos', $filename, 'public');
            $clinic->photo = $filePath;
        }

        $clinic->name = $request->post('name');
        $clinic->description = $request->post('description');
        $clinic->address = $request->post('address');
        $clinic->phone = $request->post('phone');
        $clinic->save();

        $servicesTable = DB::table('services');
        $names = $request->post('services');
        $services = array_map(function ($name) use ($clinic_id) {
            return [
                'clinic_id' => $clinic_id,
                'name' => $name
            ];
        }, $names);
        $servicesTable->where('clinic_id', $clinic_id)->delete();
        $servicesTable->insert($services);

        return redirect()->route('admin.dictionary.clinics')->with('Изменения были успешно сохранены!');
    }
}

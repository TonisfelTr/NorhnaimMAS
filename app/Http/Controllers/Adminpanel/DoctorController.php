<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
use App\Http\Requests\UserRoleStoreRequest;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View {
        $doctors = Doctor::paginate(20);

        return view('adminpanel.sub-users.doctor', compact('doctors'));
    }

    public function store(UserRoleStoreRequest $request): View {
        $method = $request->getMethod();

        if ($method == 'POST') {

        } else {
            return view('adminpanel.service.user_add');
        }
    }

    public function delete(MassDeleteRequest $request): RedirectResponse {
        Doctor::whereIn('id', $request->all()->selected)->delete();

        return redirect()->route('admin.users.doctors')->with("Запись доктора была удалена.");
    }

    public function edit(int $doctor_id) {
        $doctor = Doctor::findOrFail($doctor_id);
        $pricelist = DB::table('doctors_pricelists')
            ->where('doctor_id', $doctor_id)
            ->get()
            ->toArray();
        $clinics = Clinic::all();

        return view('adminpanel.service.doctor_edit', compact('doctor', 'clinics', 'pricelist'));
    }

    public function save(Request $request, int $doctor_id) {
        $doctor = Doctor::findOrFail($doctor_id);
        $priceTable = [];
        $dpTable = DB::table('doctors_pricelists');

        foreach ($request->post('group_name') ?? [] as $index => $group_name) {
            $priceTable[] = [
                'name' => $request->post('name')[$index],
                'group' => $group_name,
                'price' => $request->post('price')[$index],
                'discount_price' => $request->post('discount_price')[$index],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        $dpTable->where('doctor_id', $doctor_id)->delete();
        $dpTable->insert($priceTable);

        $doctor->update($request->post());

        return redirect()->route('admin.users.doctors')->with('success', 'Запись врача была отредактирована.');
    }
}

<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorStoreRequest;
use App\Http\Requests\DoctorUpdateRequest;
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
    public function index(Request $request): View {
        if ($request->has('search')) {
            $search = $request->get('search');
            $doctors = Doctor::where('name', 'ilike', "%$search%")
                ->orWhere('surname', 'ilike', "%$search%")
                ->orWhere('patronym', 'ilike', "%search%")
                ->orWhere('city', 'ilike', "%$search%")
                ->paginate(20);
        } else {
            $doctors = Doctor::paginate(20);
        }

        return view('adminpanel.sub-users.doctor', compact('doctors'));
    }

    public function create(): View {
        return view('adminpanel.service.user_add');
    }

    public function store(DoctorStoreRequest $request): RedirectResponse {
        if (!(is_authed() && group()->doctors_add)) {
            return redirect()->route('admin.users.doctors')->withErrors('У вас недостаточно прав.');
        }

        Doctor::create($request->validated());

        return redirect()->route('admin.users.doctors')->with([
            'message' => 'Запись о докторе была создана.'
        ]);
    }

    public function delete(int $doctor_id): RedirectResponse {
        if (!(is_authed() && group()->doctors_remove)) {
            return redirect()->route('admin.users.doctors')->withErrors('У вас недостаточно прав');
        }

        Doctor::find($doctor_id)->delete();

        return redirect()->route('admin.users.doctors')->with([
            'message' => 'Запись была удалена.'
        ]);
    }

    public function massDelete(MassDeleteRequest $request): RedirectResponse {
        if (!(is_authed() && group()->doctors_remove)) {
            return redirect()->route('admin.users.doctors')->withErrors('У вас недостаточно прав');
        }

        Doctor::whereIn('id', $request->post('selected'))->delete();

        return redirect()->route('admin.users.doctors')->with("Запись докторов были удалены.");
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

    public function update(DoctorUpdateRequest $request, int $doctor_id) {
        $doctor = Doctor::findOrFail($doctor_id);

        $priceTable = collect($request->validated('group_name'))
            ->map(function ($group_name, $index) use ($request) {
                return [
                    'name' => $request->validated('name')[$index],
                    'group' => $group_name,
                    'price' => $request->validated('price')[$index],
                    'discount_price' => $request->validated('discount_price')[$index],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            })->toArray();

        DB::table('doctors_pricelists')->where('doctor_id', $doctor_id)->delete();
        DB::table('doctors_pricelists')->insert($priceTable);

        $doctor->update($request->validated());

        return redirect()->route('admin.users.doctors')->with('success', 'Запись врача была отредактирована.');
    }
}

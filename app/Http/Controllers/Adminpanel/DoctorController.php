<?php

namespace app\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
use App\Http\Requests\UserRoleStoreRequest;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
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
}

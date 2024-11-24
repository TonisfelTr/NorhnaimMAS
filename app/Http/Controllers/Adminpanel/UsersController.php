<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserEditRequest;
use App\Models\Doctor;
use App\Models\Group;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function list() {
        $users = User::paginate(20);

        return view('adminpanel.users', compact('users'));
    }

    public function edit(int $userID): View {
        $user = User::whereId($userID)->first();
        $groups = Group::get(['name', 'id']);
        $doctors = Doctor::get(['name', 'surname', 'patronym', 'id']);
        $patients = Patient::get(['name', 'surname', 'patronym', 'id']);

        return view('adminpanel.service.user_edit', compact('user', 'groups', 'doctors', 'patients'));
    }

    public function save(UserEditRequest $request, int $user_id): RedirectResponse {
        $information = $request->all();
        $user = User::find($user_id);
        $user->login = $information['login'];
        $user->email = $information['email'];
        $user->email_verified_at = $information['email_verified_at'];
        $user->userable_id = $information['userable_id'] ?? 1;
        $user->userable_type = $information['userable_type'];
        $user->group_id = $information['group_id'];
        $user->balance = $information['balance'];
        $user->save();

        return redirect()->route('admin.users')->with([
            'message' => "Данные пользователя {$user->login} были успешно обновлёны!",
            'status' => 'users.success'
                                                      ]);
    }
}

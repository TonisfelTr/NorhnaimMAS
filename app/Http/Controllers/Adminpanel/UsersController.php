<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserStoreRequest;
use App\Jobs\BalanceTransactionJob;
use App\Models\Doctor;
use App\Models\Group;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function list(Request $request) {
        if ($request->has('search')) {
            $searchingStr = $request->get('search');

            $users = User::whereRaw("login ILIKE ?", ['%' . $searchingStr . '%'])
                         ->orWhereRaw("email ILIKE ?", ['%' . $searchingStr . '%'])
                         ->paginate(20);
        } else {
            $users = User::paginate(20);
        }

        return view('adminpanel.users', compact('users'));
    }

    public function create(): View {
        $groups = Group::all();
        $doctors = Doctor::all();
        $patients = Patient::all();

        return view('adminpanel.service.user_add', compact('groups', 'doctors', 'patients'));
    }

    public function store(UserStoreRequest $request) {
        $user = new User();
        $user->login = $request->post('login');
        $user->email = $request->post('email');
        $user->email_verified_at = $request->post('email_verified_at');
        $user->password = Hash::make($request->post('password'));
        $user->group_id = $request->post('group_id');
        $user->balance = $request->post('balance');

        if ($user->save()) {
            DB::table('balance_transactions')->insert([
                'user_id' => $user->id,
                'reason' => 'При создании, баланс установлен в ' . $user->balance,
                'old_balance' => '0',
                'new_balance' => $user->balance
            ]);
        }

        return redirect()->route('admin.users')->with([
            'status' => 'users.success',
            'message' => 'Пользователь был успешно добавлен.'
        ]);
    }

    public function edit(int $userID): View {
        $user = User::whereId($userID)->first();
        $groups = Group::get(['name', 'id']);
        $doctors = Doctor::get(['name', 'surname', 'patronym', 'id']);
        $patients = Patient::get(['name', 'surname', 'patronym', 'id']);
        $transactions = DB::table('balance_transactions')->where('user_id', $userID)->get();

        return view('adminpanel.service.user_edit', compact('user', 'groups', 'doctors', 'patients', 'transactions'));
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
        $user->save();

        $administratorID = Auth::user()->id;
        BalanceTransactionJob::dispatch("Баланс изменён администратором с ID {$administratorID}", $user_id, $information['balance']);

        return redirect()->route('admin.users')->with([
            'message' => "Данные пользователя {$user->login} были успешно обновлёны!",
            'status' => 'users.success'
                                                      ]);
    }

    public function delete(int $userID)
    {
        if ($userID == 1) {
            return redirect()->back()->withErrors('Нельзя удалить пользователя с ID 1');
        }

        User::findOrFail($userID)->delete();

        return redirect()->route('admin.users')->with([
                'message' => "Пользователь был удалён.",
                'status' => 'users.success'
            ]);
    }

    public function massDelete(Request $request) {
        $users = $request->post('selected');

        User::whereIn('id', $users)->delete();

        return redirect()->back()->with([
            'message' => 'Выделенные пользователи были удалены.',
            'status' => 'users.success'
        ]);
    }
}

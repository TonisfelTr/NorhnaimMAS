<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserChangePasswordRequest;
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
    public function list(Request $request): View
    {
        $search = $request->get('search');
        $users = User::query()
                     ->when($search, function ($query, $search) {
                         $query->where('login', 'ilike', "%$search%")
                               ->orWhere('email', 'ilike', "%$search%");
                     })
                     ->paginate(20);

        return view('adminpanel.users', compact('users'));
    }

    public function create(): View
    {
        return view('adminpanel.service.user_add', [
            'groups' => Group::all(),
            'doctors' => Doctor::all(),
            'patients' => Patient::all(),
        ]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        if ($data['balance']) {
            DB::table('balance_transactions')->insert([
                'user_id' => $user->id,
                'reason' => "При создании, баланс установлен в {$data['balance']}",
                'old_balance' => 0,
                'new_balance' => $data['balance'],
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'Пользователь был успешно добавлен.');
    }

    public function edit(int $userID): View
    {
        return view('adminpanel.service.user_edit', [
            'user' => User::findOrFail($userID),
            'groups' => Group::all(['name', 'id']),
            'doctors' => Doctor::all(['name', 'surname', 'patronym', 'id']),
            'patients' => Patient::all(['name', 'surname', 'patronym', 'id']),
            'transactions' => DB::table('balance_transactions')->where('user_id', $userID)->get(),
        ]);
    }

    public function save(UserEditRequest $request, int $user_id): RedirectResponse
    {
        $user = User::findOrFail($user_id);
        $data = $request->validated();

        $user->update($data);

        if ($data['balance']) {
            $adminID = Auth::id();
            BalanceTransactionJob::dispatch(
                "Баланс изменён администратором с ID {$adminID}",
                $user_id,
                $data['balance']
            );
        }

        return redirect()->route('admin.users')->with('success', "Данные пользователя {$user->login} были успешно обновлены!");
    }

    public function delete(int $userID): RedirectResponse
    {
        if ($userID == 1) {
            return redirect()->back()->withErrors('Нельзя удалить пользователя с ID 1.');
        }

        User::findOrFail($userID)->forceDelete();

        return redirect()->route('admin.users')->with('success', 'Пользователь был удалён.');
    }

    public function massDelete(Request $request): RedirectResponse
    {
        $userIDs = $request->input('selected', []);
        User::whereIn('id', $userIDs)->forceDelete();

        return redirect()->back()->with('success', 'Выделенные пользователи были удалены.');
    }

    public function changePassword(UserChangePasswordRequest $request, int $user_id): RedirectResponse
    {
        $user = User::findOrFail($user_id);
        $user->update(['password' => Hash::make($request->post('password'))]);

        return redirect()->route('admin.users')->with('success', 'Пароль был изменён.');
    }
}

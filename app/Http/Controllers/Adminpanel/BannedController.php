<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannedStoreRequest;
use App\Models\Banned;
use app\Models\Rule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BannedController extends Controller
{
    public function index(): View {
        $banneds = Banned::paginate(20);

        return view('adminpanel.sub-users.banned', compact('banneds'));
    }

    public function create(): View {
        $users = User::whereNot('id', auth()->user()->id)->get();
        $rules = Rule::all();

        return view('adminpanel.service.banned_add', compact('users', 'rules'));
    }

    public function store(BannedStoreRequest $request) {
        $ban = new Banned();
        $ban->admin_id = auth()->user()->id;
        $ban->user_id = User::where('id', $request->post('login'))->first()->id;
        $ban->rule_id = $request->post('rule_id');
        $ban->from = Carbon::today();
        $ban->to = Carbon::parse($request->post('to'))->format('Y-m-d');
        $ban->save();

        return redirect()->route('admin.users.banned')->with("Пользователь {$request->post('login')} был заблокирован.");
    }
}

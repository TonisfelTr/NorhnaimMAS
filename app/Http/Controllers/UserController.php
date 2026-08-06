<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorizationRequest;
use App\Models\Banned;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(AuthorizationRequest $request) {
        if (Auth::attempt(["email" => $request->email, "password" => $request->password], (bool)$request->remember_me)) {
            $banned = Banned::where('user_id', User::where('email', $request->email)->first()->id);
            if ($banned->count() === 0) {
                return redirect()->back();
            } else {
                Auth::logout();
                $message = Rule::whereId($banned->first()->rule_id)->first()->text;
                return redirect()->back()->withErrors("Ваш аккаунт был заблокирован по причине нарушения правила: \"$message\"")->with('open_modal', 'authorization-block');
            }
        } else {
            return redirect()
                ->back()
                ->withErrors('Неправильный логин или пароль.')
                ->with('open_modal', 'authorization-block');
        }
    }

    public function logout() {
        if (Auth::user()) {
            Auth::logout();
            return redirect()->route('main.index');
        }
    }
}

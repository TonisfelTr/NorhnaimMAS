<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorizationRequest;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(AuthorizationRequest $request) {
        if (Auth::attempt(["email" => $request->email, "password" => $request->password], $request->remember_me)) {
            return redirect()->back();
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

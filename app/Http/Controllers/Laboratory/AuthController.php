<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function create()
    {
        return view('laboratory.auth.login');
    }

    public function store(Request $r)
    {
        $data = $r->validate(['email' => ['required', 'email', 'max:255'], 'password' => ['required', 'string']]);
        if (!Auth::attempt($data, false)) throw ValidationException::withMessages(['email' => 'Неверный email или пароль.']);
        $r->session()->regenerate();
        $u = $r->user();
        if (!method_exists($u, 'hasRole') || !$u->hasRole('laborant') || !$u->laboratory_clinic_id) {
            Auth::logout();
            $r->session()->invalidate();
            $r->session()->regenerateToken();
            throw ValidationException::withMessages(['email' => 'Доступ в лабораторию не разрешён.']);
        }
        return redirect()->route('laboratory.index');
    }

    public function destroy(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('laboratory.login');
    }
}

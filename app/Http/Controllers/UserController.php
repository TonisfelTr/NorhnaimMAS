<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorizationRequest;
use App\Models\Banned;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    public function login(AuthorizationRequest $request)
    {
        $remember = $request->boolean('remember_me');

        if (! Auth::attempt([
            'email' => mb_strtolower(trim((string) $request->email)),
            'password' => $request->password,
        ], $remember)) {
            return redirect()
                ->back()
                ->withInput($request->only('email', 'remember_me'))
                ->withErrors([
                    'email' => 'Неправильный логин или пароль.',
                ])
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'login');
        }

        $request->session()->regenerate();

        $banned = Banned::query()
            ->where('user_id', Auth::id())
            ->first();

        if (! $banned) {
            return redirect()->back();
        }

        $message = Rule::query()
            ->find($banned->rule_id)
            ?->text ?? 'Нарушение правил сервиса';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('main.index')
            ->withErrors([
                'email' => "Ваш аккаунт был заблокирован по причине нарушения правила: \"{$message}\"",
            ])
            ->with('open_modal', 'authorization-block')
            ->with('auth_mode', 'login');
    }

    public function register(Request $request)
    {
        $request->merge([
            'login' => trim((string) $request->input('login')),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $validator = Validator::make(
            $request->all(),
            [
                'g-recaptcha-response' => ['required'],
                'login' => ['required', 'string', 'min:2', 'max:255', 'unique:users,login'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', PasswordRule::min(8)],
                'accept_privacy' => ['accepted'],
            ],
            [
                'g-recaptcha-response.required' => 'Не удалось отличить Вас от бота. Повторите попытку.',
                'login.required' => 'Укажите имя или логин.',
                'login.unique' => 'Такой логин уже используется.',
                'email.required' => 'Укажите электронную почту.',
                'email.email' => 'Укажите корректный адрес электронной почты.',
                'email.unique' => 'Пользователь с такой электронной почтой уже зарегистрирован.',
                'password.required' => 'Укажите пароль.',
                'password.confirmed' => 'Пароли не совпадают.',
                'password.min' => 'Пароль должен содержать не менее :min символов.',
                'accept_privacy.accepted' => 'Необходимо согласиться с политикой конфиденциальности.',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                    'g-recaptcha-response',
                ]))
                ->withErrors($validator)
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'register');
        }

        $data = $validator->validated();

        try {
            DB::transaction(function () use ($data): void {
                /*
                 * В текущей схеме users.email_verified_at NOT NULL.
                 * Пока отдельная email-верификация не внедрена, сохраняем дату
                 * регистрации. На следующем этапе это можно заменить полноценной
                 * верификацией email.
                 */
                $user = User::query()->create([
                    'login' => $data['login'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'email_verified_at' => now(),
                ]);

                $patientRole = Role::findOrCreate('patients', 'web');
                $user->assignRole($patientRole);

                /*
                 * Patient здесь намеренно НЕ создаём.
                 * Медкарта могла быть заведена врачом раньше. Безопасную привязку
                 * User -> Patient делаем отдельно в панели пациента после
                 * подтверждения личности.
                 */
            });
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput($request->only('login', 'email'))
                ->withErrors([
                    'email' => 'Не удалось завершить регистрацию. Повторите попытку.',
                ])
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'register');
        }

        return redirect()
            ->route('main.index')
            ->with('open_modal', 'authorization-block')
            ->with('auth_mode', 'login')
            ->with('auth_success', 'Регистрация завершена. Теперь войдите в систему.');
    }

    public function forgotPassword(Request $request)
    {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $validator = Validator::make(
            $request->all(),
            [
                'g-recaptcha-response' => ['required'],
                'email' => ['required', 'email', 'max:255'],
            ],
            [
                'g-recaptcha-response.required' => 'Не удалось отличить Вас от бота. Повторите попытку.',
                'email.required' => 'Укажите электронную почту.',
                'email.email' => 'Укажите корректный адрес электронной почты.',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->withErrors($validator)
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'forgot');
        }

        try {
            /*
             * Намеренно не раскрываем, существует ли такой email в базе.
             */
            Password::sendResetLink([
                'email' => $request->input('email'),
            ]);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Не удалось отправить письмо. Повторите попытку позднее.',
                ])
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'forgot');
        }

        return redirect()
            ->back()
            ->with('open_modal', 'authorization-block')
            ->with('auth_mode', 'forgot')
            ->with(
                'auth_success',
                'Если такой email зарегистрирован, на него отправлена ссылка для восстановления пароля.'
            );
    }

    public function showResetPassword(Request $request, string $token)
    {
        return redirect()
            ->route('main.index')
            ->with('open_modal', 'authorization-block')
            ->with('auth_mode', 'reset')
            ->with('reset_token', $token)
            ->with(
                'reset_email',
                mb_strtolower(trim((string) $request->query('email')))
            );
    }

    public function resetPassword(Request $request)
    {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $validator = Validator::make(
            $request->all(),
            [
                'g-recaptcha-response' => ['required'],
                'token' => ['required', 'string'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'confirmed', PasswordRule::min(8)],
            ],
            [
                'g-recaptcha-response.required' => 'Не удалось отличить Вас от бота. Повторите попытку.',
                'token.required' => 'Ссылка восстановления недействительна.',
                'email.required' => 'Укажите электронную почту.',
                'email.email' => 'Укажите корректный адрес электронной почты.',
                'password.required' => 'Укажите новый пароль.',
                'password.confirmed' => 'Пароли не совпадают.',
                'password.min' => 'Пароль должен содержать не менее :min символов.',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->withErrors($validator)
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'reset')
                ->with('reset_token', $request->input('token'))
                ->with('reset_email', $request->input('email'));
        }

        $status = Password::reset(
            $validator->validated(),
            function (User $user, string $password): void {
                /*
                 * В User уже есть cast password => hashed,
                 * поэтому передаём новый пароль без ручного Hash::make().
                 */
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Не удалось изменить пароль. Возможно, ссылка устарела или уже была использована.',
                ])
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'reset')
                ->with('reset_token', $request->input('token'))
                ->with('reset_email', $request->input('email'));
        }

        return redirect()
            ->route('main.index')
            ->with('open_modal', 'authorization-block')
            ->with('auth_mode', 'login')
            ->with('auth_success', 'Пароль изменён. Теперь можно войти.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('main.index');
    }
}

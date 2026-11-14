<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\PatientIdentityLinkRequest;
use App\Services\PatientLinkingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IdentityController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('patients'), 403);

        return redirect()->route('patients.profile.index', ['tab' => 'identity']);
    }

    public function submit(
        PatientIdentityLinkRequest $request,
        PatientLinkingService $linking
    ): RedirectResponse {
        $linking->submit($request->user(), $request->validated());

        return redirect()
            ->route('patients.profile.index', ['tab' => 'identity'])
            ->with(
                'success',
                'Заявка принята. Если в существующей медицинской карте указан email, код подтверждения будет отправлен на этот адрес. В остальных случаях заявку проверит сотрудник клиники.'
            );
    }

    public function verify(
        Request $request,
        PatientLinkingService $linking
    ): RedirectResponse {
        abort_unless($request->user()?->hasRole('patients'), 403);

        $data = $request->validate([
            'request_id' => ['required', 'integer'],
            'code' => ['required', 'digits:6'],
        ], [
            'request_id.required' => 'Заявка не найдена.',
            'code.required' => 'Введите код из письма.',
            'code.digits' => 'Код должен содержать 6 цифр.',
        ]);

        $result = $linking->verify(
            $request->user(),
            (int) $data['request_id'],
            (string) $data['code']
        );

        return match ($result) {
            'linked' => redirect()
                ->route('patients.profile.index', ['tab' => 'identity'])
                ->with('success', 'Медицинская карта успешно привязана к личному кабинету.'),

            'manual' => redirect()
                ->route('patients.profile.index', ['tab' => 'identity'])
                ->with('success', 'Запрос передан сотруднику клиники для дополнительной проверки.'),

            default => redirect()
                ->route('patients.profile.index', ['tab' => 'identity'])
                ->withErrors([
                    'code' => 'Код неверный. Проверьте письмо и попробуйте ещё раз.',
                ]),
        };
    }
}

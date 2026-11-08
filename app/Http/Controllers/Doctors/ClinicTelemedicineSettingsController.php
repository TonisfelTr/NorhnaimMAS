<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Services\ClinicTelemedicineSettings;
use App\Support\ClinicAppointmentTime;
use App\Support\ClinicTelemedicineSettingsAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ClinicTelemedicineSettingsController extends SettingsBaseController
{
    public function index(Request $request): View
    {
        $clinic = ClinicTelemedicineSettingsAccess::current($request);
        $this->requireStorage();
        $profile = ClinicTelemedicineSettings::profile($clinic);
        $old = $request->session()->getOldInput();
        $form = [];

        foreach (['provider', 'webrtc_url', 'telemost_mode'] as $field) {
            $value = array_key_exists($field, $old) ? $old[$field] : $profile[$field];
            $form[$field] = is_string($value) ? $value : '';
        }

        if (!in_array($form['telemost_mode'], ['api', 'manual'], true)) {
            $form['telemost_mode'] = 'api';
        }

        $form['clear_telemost_token'] = in_array($old['clear_telemost_token'] ?? false, [true, 1, '1'], true);
        $form['show_webrtc'] = $form['provider'] === 'webrtc';
        $form['show_telemost'] = $form['provider'] === 'telemost';
        $form['show_api'] = $form['telemost_mode'] === 'api';
        $form['has_telemost_token'] = $profile['has_telemost_token'];
        $form['setup_errors'] = $profile['settings_errors'];
        $form['clinic_name'] = is_string($clinic->name) ? $clinic->name : 'Клиника';
        $form['timezone'] = ClinicAppointmentTime::timezone($clinic);
        $form['submit_url'] = route('doctors.settings.telemedicine.update');
        $form['general_url'] = route('doctors.settings.index');

        return $this->settingsView('doctors.settings.telemedicine', [
            'clinic' => $clinic,
            'telemedicineForm' => $form,
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        // Remove the secret from every input bag before anything can redirect
        // with old input. Validation uses the local value, never request flashing.
        $rawToken = $request->all()['telemost_token'] ?? null;
        $request->replace($request->except('telemost_token'));
        $request->query->remove('telemost_token');
        $request->files->remove('telemost_token');

        ClinicTelemedicineSettingsAccess::current($request);
        $this->requireStorage();
        $input = $request->only(['provider', 'webrtc_url', 'telemost_mode', 'clear_telemost_token']);
        $input['telemost_token'] = $rawToken;
        $input['clear_telemost_token'] ??= '0';

        $validator = Validator::make($input, [
            'provider' => ['bail', 'required', 'string', Rule::in(['webrtc', 'telemost'])],
            'webrtc_url' => [
                'bail', 'nullable', 'string', 'max:2048',
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && $value !== '' && !ClinicTelemedicineSettings::validWebrtcUrl($value)) {
                        $fail('Укажите HTTPS-адрес без логина, пароля, параметров запроса и фрагмента.');
                    }
                },
            ],
            'telemost_mode' => ['bail', 'required', 'string', Rule::in(['api', 'manual'])],
            'clear_telemost_token' => ['required', 'boolean'],
            'telemost_token' => [
                'bail', 'nullable', 'string', 'max:8192',
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && preg_match('/[\x00-\x20\x7F]/', $value) === 1) {
                        $fail('Токен не должен содержать пробелы или управляющие символы.');
                    }
                },
            ],
        ], [
            'provider.required' => 'Выберите способ проведения консультаций.',
            'provider.string' => 'Выберите способ проведения консультаций из списка.',
            'provider.in' => 'Выберите собственную видеосвязь или Яндекс Телемост.',
            'webrtc_url.string' => 'Введите адрес страницы подключения текстом.',
            'webrtc_url.max' => 'Адрес страницы подключения не должен превышать 2048 символов.',
            'telemost_mode.required' => 'Выберите способ создания встреч в Телемосте.',
            'telemost_mode.string' => 'Выберите способ создания встреч из списка.',
            'telemost_mode.in' => 'Выберите автоматическое создание или добавление ссылки вручную.',
            'clear_telemost_token.boolean' => 'Проверьте отметку удаления токена.',
            'telemost_token.string' => 'Введите токен текстом.',
            'telemost_token.max' => 'Токен слишком длинный.',
        ]);

        $validator->after(static function ($validation) use ($input): void {
            if (in_array($input['clear_telemost_token'], [true, 1, '1'], true)
                && is_string($input['telemost_token']) && $input['telemost_token'] !== '') {
                $validation->errors()->add('telemost_token', 'Выберите одно действие: сохранить новый токен или удалить прежний.');
            }
        });

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Проверьте настройки телемедицины.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->route('doctors.settings.telemedicine.index')
                ->withErrors($validator)
                ->withInput($request->only(['provider', 'webrtc_url', 'telemost_mode', 'clear_telemost_token']));
        }

        $data = $validator->validated();

        DB::transaction(static function () use ($request, $data): void {
            // Re-read and lock the clinic: the format may have been switched off
            // after this form was opened or after route middleware ran.
            $clinic = ClinicTelemedicineSettingsAccess::current($request, lock: true);
            ClinicTelemedicineSettings::store($clinic, [
                'provider' => $data['provider'],
                'webrtc_url' => trim($data['webrtc_url'] ?? ''),
                'telemost_mode' => $data['telemost_mode'],
            ], $data['telemost_token'] ?? null,
                in_array($data['clear_telemost_token'], [true, 1, '1'], true));
            $clinic->save();
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Настройки телемедицины сохранены.']);
        }

        return redirect()->route('doctors.settings.telemedicine.index')
            ->with('success', 'Настройки телемедицины сохранены.');
    }

    private function requireStorage(): void
    {
        abort_unless(ClinicTelemedicineSettings::storageReady(), 503,
            'Обновление настроек телемедицины ещё не завершено. Обратитесь к администратору системы.');
    }
}

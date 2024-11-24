<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index() {
        $settingRecords = Setting::all();
        $settings = [];

        foreach ($settingRecords as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        return view('adminpanel.settings');
    }

    public function save(Request $request): RedirectResponse {
        $settings = $request->all();

        foreach ($settings as $key => $value) {
            if (in_array($key, ['_token', 'g-recaptcha-response'])) {
                continue;
            }

            $settings = Setting::where('key', $key);

            if ($settings->get()->isEmpty()) {
                return redirect()->back()->withErrors("Ключа $key не существует в базе.");
            }

            $setting = $settings->first();
            $setting->value = $value;
            $setting->save();
        }

        return redirect()->back()->with([
            'status' => 'settings.success',
            'message' => 'Настройки были успешно сохранены!'
                                        ]);
    }
}

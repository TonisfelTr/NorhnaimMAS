<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

final class TestsImportController extends Controller
{
    public function form()
    {
        /** @var Doctor $doctor */
        $doctor = Auth::user()?->doctor;

        return view('doctors.tests.import', [
            'doctor' => $doctor,
        ]);
    }

    public function importCsv(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:64',
            'description' => 'nullable|string',
            'csv'         => 'required|file|mimes:csv,txt',
        ]);

        /** @var Doctor $doctor */
        $doctor = Auth::user()?->doctor;

        // создаём приватный тест врача
        $test = Test::create([
            'code'             => $data['code'],
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'is_public'        => false,
            'owner_doctor_id'  => $doctor->id,
        ]);

        // сохраним CSV во внутреннее хранилище
        $path = $request->file('csv')->store("private/tests/doctor-{$doctor->id}", 'local');

        // вызов импортёра вопросов
        Artisan::call('tests:import', [
            'code'       => $test->code,
            'file'       => storage_path('app/'.$path),
            '--truncate' => true,
        ]);

        return redirect()
            ->route('doctors.main')
            ->with('ok', 'Структура теста импортирована. При необходимости загрузите ключи.');
    }

    public function importKeys(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|exists:tests,code',
            'json' => 'required|file|mimes:json',
        ]);

        $path = $request->file('json')->store('private/keys', 'local');

        Artisan::call('tests:keys:import', [
            'code' => $data['code'],
            'file' => storage_path('app/'.$path),
        ]);

        return back()->with('ok', 'Ключи и интерпретации загружены.');
    }
}

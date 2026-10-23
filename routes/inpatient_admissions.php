<?php

use App\Http\Controllers\Doctors\InpatientAdmissionController;
use App\Http\Middleware\DoctorHardLockMiddleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

// Подключается из routes/web.php вне других route-групп.
// Сохраняет уже зарегистрированные именованные маршруты госпитализации.
// Имена читаем из объектов: во время загрузки web.php lookup имён может
// ещё не быть обновлён после вызовов ->name() и ->as().
$inpatientAdmissionRegisteredNames = [];
foreach (Route::getRoutes() as $inpatientAdmissionRegisteredRoute) {
    if ($inpatientAdmissionRegisteredRoute->getName() !== null) {
        $inpatientAdmissionRegisteredNames[$inpatientAdmissionRegisteredRoute->getName()] = true;
    }
}

Route::middleware([
    RoleMiddleware::class . (config('app.debug') ? ':admins|doctors' : ':doctors'),
    DoctorHardLockMiddleware::class,
])->prefix('doctors/inpatient/admissions')->as('doctors.inpatient.admissions.')
    ->group(static function () use ($inpatientAdmissionRegisteredNames): void {
        if (!isset($inpatientAdmissionRegisteredNames['doctors.inpatient.admissions.index'])) {
            Route::get('/', [InpatientAdmissionController::class, 'index'])->name('index');
        }
        if (!isset($inpatientAdmissionRegisteredNames['doctors.inpatient.admissions.options'])) {
            Route::get('/options', [InpatientAdmissionController::class, 'options'])->name('options');
        }
        if (!isset($inpatientAdmissionRegisteredNames['doctors.inpatient.admissions.patients.search'])) {
            Route::get('/patients/search', [InpatientAdmissionController::class, 'searchPatients'])
                ->name('patients.search');
        }
        if (!isset($inpatientAdmissionRegisteredNames['doctors.inpatient.admissions.store'])) {
            Route::post('/', [InpatientAdmissionController::class, 'store'])->name('store');
        }
    });

unset($inpatientAdmissionRegisteredNames, $inpatientAdmissionRegisteredRoute);

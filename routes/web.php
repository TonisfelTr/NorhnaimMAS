<?php

use App\Http\Controllers\Doctors\AnamnesisController;
use App\Http\Controllers\Doctors\IndexController;
use App\Http\Controllers\Doctors\MedicalCardController;
use App\Http\Controllers\Doctors\PrescriptionsController;
use App\Http\Controllers\Doctors\ReceptionController;
use App\Http\Controllers\Doctors\ResearchController;
use App\Http\Controllers\PrescriptionController;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::get('/', \App\Http\Controllers\Main\IndexController::class)->name('main.index');
Route::get('/medicines', \App\Http\Controllers\Main\MedicineController::class)->name('main.medicines');
Route::get('/privacy-policy', \App\Http\Controllers\Main\MedicineController::class)->name('main.policy');
Route::get('/feedback', \App\Http\Controllers\Main\FeedbackController::class)->name('main.feedback');

if (config('app.debug')) {
    Route::get('/test-user', function () {
        dd(auth()->user());
    });
}

Route::prefix('/dictionary')->group(function () {
    Route::prefix('/clinics')->group(function () {
        Route::get('/', [\App\Http\Controllers\Main\ClinicController::class, 'list'])->name('main.clinics');
        Route::get('/{clinic_id}', [\App\Http\Controllers\Main\ClinicController::class, 'index'])->name('main.clinics.form');
        Route::post('/feedback/{clinic_id}', [\App\Http\Controllers\Main\ClinicController::class, 'feedback'])->name('main.clinics.form.feedback-create');
        Route::get('/filter/{city_name}', [\App\Http\Controllers\Main\ClinicController::class, 'list'])->name('main.clinics.filters.city');
    });

    Route::prefix('/doctors')->group(function () {
        Route::get('/{clinic_id}', [\App\Http\Controllers\Main\DoctorController::class, 'index'])->name('main.doctors.form')->where('clinic_id', '\d+');
        Route::post('/feedback/{doctor_id}', [\App\Http\Controllers\Main\DoctorController::class, 'feedback'])->name('main.doctors.form.feedback-create');
    });
});

Route::prefix('/blog')->group(function () {
    Route::get('/', [\App\Http\Controllers\Main\BlogController::class, 'list'])->name('main.blog');
    Route::prefix('/category')->group(function () {
        Route::get('/{category_id}', [\App\Http\Controllers\Main\BlogController::class, 'list'])->name('main.blog.category');
    });
    Route::get('/{topic_id}', [\App\Http\Controllers\Main\BlogController::class, 'index'])->name('main.blog.topic');
});

Route::prefix('/articles')->group(function (){
    Route::get('/', [\App\Http\Controllers\Main\ArticleController::class, 'list'])->name('main.articles');
    Route::get('/{article_id}', [\App\Http\Controllers\Main\ArticleController::class, 'index'])->name('main.articles.show');
});

Route::prefix('/jurisprudence')->group(function () {
    Route::get('/', [\App\Http\Controllers\Main\JurisprudenceController::class, 'list'])->name('main.jurisprudence');
});

Route::prefix('/log/')->group(function () {
    Route::get('/out', [\App\Http\Controllers\UserController::class, 'logout'])->name('actions.logout');
    Route::post('/in', [\App\Http\Controllers\UserController::class, 'login'])->name('actions.login');
});

Route::prefix('/api/')->group(function () {
    require_once 'api.php';
});

Route::middleware([RoleMiddleware::class . (config('app.debug') ? ':admins|doctors' : ':doctors')])->group(function () {
    Route::prefix('/doctors')->group(function () {
        Route::get('/', [IndexController::class, 'index'])->name('doctors.main');

        Route::prefix('/prescriptions')->group(function () {
            Route::get('/', [PrescriptionsController::class, 'index'])->name('doctors.prescriptions');
            Route::get('/create', [PrescriptionsController::class, 'create'])->name('doctors.prescriptions.new');
            Route::post('/store', [PrescriptionsController::class, 'store'])->name('doctors.prescriptions.store');
            Route::get('/drugs', [PrescriptionsController::class, 'base'])->name('doctors.prescriptions.base');
            Route::post('/print', [PrescriptionsController::class, 'print'])->name('prescriptions.print');
            Route::get('/print/{id}', [PrescriptionsController::class, 'printForTable'])->name('prescriptions.print.from_table');
        });

        Route::prefix('/reception')->group(function () {
            Route::get('/', [ReceptionController::class, 'index'])->name('doctors.reception');
            Route::get('/create', [ReceptionController::class, 'create'])->name('doctors.reception.create');
            Route::post('/store', [ReceptionController::class, 'store'])->name('doctors.reception.store');
            Route::get('/archives', [ReceptionController::class, 'archives'])->name('doctors.reception.archives');
            Route::get('/edit/{record_id}', [ReceptionController::class, 'edit'])->name('doctors.reception.edit');
            Route::post('/edit/{id}', [ReceptionController::class, 'update'])->name('doctors.reception.update');
            Route::post('/delete/{id}', [ReceptionController::class, 'destroy'])->name('doctors.reception.destroy');
        });

        Route::prefix('/patients')->group(function () {
            Route::prefix('/{patient:id}')->group(function () {
                Route::get('/', MedicalCardController::class)->name('doctors.patients.medical_card');
                Route::post('/anamneses/store', [AnamnesisController::class, 'store'])
                    ->name('doctors.anamneses.store');
                Route::get('/anamneses/{anamnesis:id}', [AnamnesisController::class, 'show'])
                    ->name('doctors.anamneses.show');

                Route::post('/researches/store', [ResearchController::class, 'store'])
                    ->name('doctors.researches.store');
                Route::post('/researches/{labResearch:id}/update', [ResearchController::class, 'update'])
                    ->name('doctors.researches.update');
                Route::post('/researches/{labResearch:id}/delete', [ResearchController::class, 'delete'])
                    ->name('doctors.researches.delete');
                Route::get('/researches/{labResearch:id}/print', [ResearchController::class, 'print']);
           });

           Route::post('/analyze-anamnesis', [\App\Http\Controllers\Doctors\NeuralNetworkController::class, 'analyzeAnamnesis'])
               ->name('analyze.anamnesis');
        });
    });
});


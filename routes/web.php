<?php

use App\Http\Controllers\Doctors\AnamnesisController;
use App\Http\Controllers\Doctors\CabinetController;
use App\Http\Controllers\Doctors\CardMedicalDocumentController;
use App\Http\Controllers\Doctors\ConsiliumController;
use App\Http\Controllers\Doctors\ContactController;
use App\Http\Controllers\Doctors\DoctorDestinationController;
use App\Http\Controllers\Doctors\DriverCommissionController;
use App\Http\Controllers\Doctors\HospitalizationReferralController;
use App\Http\Controllers\Doctors\MedicalComissionController;
use App\Http\Controllers\Doctors\MilitaryCommissionDocumentController;
use App\Http\Controllers\Doctors\MseReferralController;
use App\Http\Controllers\Doctors\PatientTestingController;
use App\Http\Controllers\Doctors\DocumentController;
use App\Http\Controllers\Doctors\EpicrisisController;
use App\Http\Controllers\Doctors\InstrumentalResearchController;
use App\Http\Controllers\Doctors\PrescriptionsController;
use App\Http\Controllers\Doctors\ResearchController;
use App\Http\Controllers\Doctors\SettingsController;
use App\Http\Controllers\Doctors\TestsController;
use App\Http\Controllers\Doctors\TestSessionController;
use App\Http\Controllers\Doctors\MedicalCardController;
use App\Http\Controllers\Doctors\AnalysesPanelController;
use App\Http\Controllers\Doctors\IndexController as DoctorsIndexController;
use App\Http\Controllers\Doctors\ReceptionController;
use App\Http\Controllers\Doctors\TestPanelController;
use App\Http\Controllers\Main\IndexController as MainIndexController;
use App\Http\Middleware\CardDocumentPrivateResponse;
use App\Http\Middleware\DoctorHardLockMiddleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::get('/', MainIndexController::class)->name('main.index');
Route::get('/medicines', \App\Http\Controllers\Main\MedicineController::class)->name('main.medicines');
Route::get('/privacy-policy', \App\Http\Controllers\Main\MedicineController::class)->name('main.policy');
Route::get('/feedback', \App\Http\Controllers\Main\FeedbackController::class)->name('main.feedback');

if (config('app.debug')) {
    Route::get('/test-user', fn () => dd(auth()->user()));
}

Route::prefix('dictionary')->group(function (): void {

    Route::prefix('clinics')->group(function (): void {
        Route::get('/', [\App\Http\Controllers\Main\ClinicController::class, 'list'])->name('main.clinics');
        Route::get('/{clinic_id}', [\App\Http\Controllers\Main\ClinicController::class, 'index'])->name('main.clinics.form');
        Route::post('/feedback/{clinic_id}', [\App\Http\Controllers\Main\ClinicController::class, 'feedback'])->name('main.clinics.form.feedback-create');
        Route::get('/filter/{city_name}', [\App\Http\Controllers\Main\ClinicController::class, 'list'])->name('main.clinics.filters.city');
    });

    Route::prefix('doctors')->group(function (): void {
        Route::get('/{clinic_id}', [\App\Http\Controllers\Main\DoctorController::class, 'index'])->where('clinic_id', '\d+')->name('main.doctors.form');
    });
});

Route::prefix('blog')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Main\BlogController::class, 'list'])->name('main.blog');
    Route::prefix('category')->group(function (): void {
        Route::get('/{category_id}', [\App\Http\Controllers\Main\BlogController::class, 'list'])->name('main.blog.category');
    });

    Route::get('/{topic_id}', [\App\Http\Controllers\Main\BlogController::class, 'index'])->name('main.blog.topic');
});

Route::prefix('articles')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Main\ArticleController::class, 'list'])->name('main.articles');
    Route::get('/{article_id}', [\App\Http\Controllers\Main\ArticleController::class, 'index'])->name('main.articles.show');
});

Route::prefix('jurisprudence')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Main\JurisprudenceController::class, 'list'])->name('main.jurisprudence');
});

Route::prefix('log')->group(function (): void {
    Route::get('out', [\App\Http\Controllers\UserController::class, 'logout'])->name('actions.logout');
    Route::post('in', [\App\Http\Controllers\UserController::class, 'login'])->name('actions.login');
});

Route::prefix('api')->group(function (): void {
    require __DIR__ . '/api.php';
});

Route::middleware([RoleMiddleware::class . (config('app.debug') ? ':admins|doctors' : ':doctors'), DoctorHardLockMiddleware::class])->prefix('doctors')->as('doctors.')->group(function (): void {

    Route::get('/', [DoctorsIndexController::class, 'index'])->name('main');

    Route::prefix('referrals/hospital')->as('referrals.hospital.')->group(function (): void {
        Route::get('/', [HospitalizationReferralController::class, 'index'])->name('index');
        Route::get('/create', [HospitalizationReferralController::class, 'create'])->name('create');
        Route::get('/planned', [HospitalizationReferralController::class, 'planned'])->name('planned');
        Route::get('/emergency', [HospitalizationReferralController::class, 'emergency'])->name('emergency');
        Route::get('/patient/{patient}/context', [HospitalizationReferralController::class, 'patientContext'])->whereNumber('patient')->name('patient_context');
        Route::get('/print/blank', [HospitalizationReferralController::class, 'printBlank'])->name('print_blank');
        Route::post('/print/preview', [HospitalizationReferralController::class, 'printPreview'])->name('print_preview');
        Route::get('/print/{referral}', [HospitalizationReferralController::class, 'printFilled'])->whereNumber('referral')->name('print_filled');
        Route::post('/', [HospitalizationReferralController::class, 'store'])->name('store');
        Route::post('/analyze', [HospitalizationReferralController::class, 'analyze'])->middleware('throttle:6,1')->name('analyze');
    });
    Route::prefix('prescriptions')->as('prescriptions.')->group(function (): void {
        Route::get('/', [PrescriptionsController::class, 'index'])->name('index');
        Route::get('/create', [PrescriptionsController::class, 'create'])->name('new');
        Route::post('/store', [PrescriptionsController::class, 'store'])->name('store');
        Route::get('/drugs', [PrescriptionsController::class, 'base'])->name('base');
        Route::post('/print', [PrescriptionsController::class, 'print'])->name('print');
        Route::get('/print/{id}', [PrescriptionsController::class, 'printForTable'])->name('print.from_table');
        Route::post('/repeat/{prescription}', [PrescriptionsController::class, 'repeatPrescription'])->name('repeat');
    });

    Route::prefix('reception')->as('reception.')->group(function (): void {
        Route::get('/', [ReceptionController::class, 'index'])->name('index');
        Route::get('/create', [ReceptionController::class, 'create'])->name('create');
        Route::post('/store', [ReceptionController::class, 'store'])->name('store');
        Route::get('/archives', [ReceptionController::class, 'archives'])->name('archives');
        Route::get('/edit/{record_id}', [ReceptionController::class, 'edit'])->name('edit');
        Route::post('/edit/{id}', [ReceptionController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [ReceptionController::class, 'destroy'])->name('destroy');
        Route::prefix('tests')->as('tests.')->group(function (): void {
            Route::get('/run/{session}', [TestSessionController::class, 'run'])->name('run');
            Route::get('/{session}/payload', [TestSessionController::class, 'payload'])->name('payload');
            Route::post('/{session}/submit', [TestSessionController::class, 'submit'])->name('submit');
            Route::post('/{session}/finish', [TestSessionController::class, 'finish'])->name('finish');
            Route::post('/{session}/force-finish', [TestSessionController::class, 'forceFinish'])->name('force-finish');
        });
    });

    Route::prefix('patients')->as('patients.')->group(function (): void {

        Route::post('/analyze-anamnesis', [\App\Http\Controllers\Doctors\NeuralNetworkController::class, 'analyzeAnamnesis'])->name('analyze.anamnesis');

        Route::prefix('{patient:id}')->where(['patient' => '\d+'])->group(function (): void {
            Route::get('/', [MedicalCardController::class, 'index'])->name('medical_card');
            Route::post('/', [MedicalCardController::class, 'update'])->name('medical_card.update');

            Route::post('/anamneses/store', [AnamnesisController::class, 'store'])->name('anamneses.store');
            Route::get('/anamneses/{anamnesis:id}', [AnamnesisController::class, 'show'])->name('anamneses.show');

            Route::post('/documents/store', [DocumentController::class, 'upload'])->name('documents.upload');
            Route::get('/documents/{document:id}', [DocumentController::class, 'download'])->name('documents.download');
            Route::post('/documents/{document:id}/delete', [DocumentController::class, 'delete'])->name('documents.delete');

            Route::post('/epicrisis/store', [EpicrisisController::class, 'store'])->name('epicrisis.store');
            Route::get('/epicrisis/{epicrisis:id}', [EpicrisisController::class, 'show'])->name('epicrisis.show');
            Route::post('/epicrisis/{epicrisis:id}/delete', [EpicrisisController::class, 'delete'])->name('epicrisis.delete');
            Route::get('/epicrisis/{epicrisis:id}/edit', [EpicrisisController::class, 'edit'])->name('epicrisis.edit');
            Route::post('/epicrisis/{epicrisis:id}/update', [EpicrisisController::class, 'update'])->name('epicrisis.update');
        });

        Route::post('/researches/{patient:id}/store', [ResearchController::class, 'store'])->name('researches.store');
        Route::post('/researches/{patient:id}/{labResearch:id}/update', [ResearchController::class, 'update'])->name('researches.update');
        Route::post('/researches/{labResearch:id}/delete', [ResearchController::class, 'delete'])->name('researches.delete');
        Route::get('/researches/{labResearch:id}/patient/{patient:id}/print', [ResearchController::class, 'print'])->withoutScopedBindings()->name('researches.print');
        Route::post('/researches/{labResearch:id}/show', [ResearchController::class, 'markAsRead'])->name('researches.show');
        Route::patch('/researches/{labResearch:id}/results/{parameter}/fill-missing', [ResearchController::class, 'fillMissingResultValue'])->whereNumber('parameter')->name('researches.result.fill-missing');
        Route::patch('/researches/{labResearch:id}/results/{parameter}/value', [ResearchController::class, 'changeResultValue'])->whereNumber('parameter')->middleware(PermissionMiddleware::class . ':doctor.analyses.change')->name('researches.result.change');

        Route::prefix('instrumentals')->group(function (): void {
            Route::post('/{patient}/store', [InstrumentalResearchController::class, 'store'])->name('instrumentals.store');
            Route::post('/{instrumentalResearch}/update', [InstrumentalResearchController::class, 'update'])->name('instrumentals.update');
            Route::post('/{patient}/{instrumentalResearch}/result', [InstrumentalResearchController::class, 'result'])->name('instrumentals.result');
            Route::post('/{patient}/{instrumentalResearch}/delete', [InstrumentalResearchController::class, 'destroy'])->name('instrumentals.delete');
            Route::get('/{patient}/{instrumentalResearch}/view', [InstrumentalResearchController::class, 'viewed'])->name('instrumentals.view');
            Route::get('/{patient}/{instrumentalResearch}/media/{media}', [InstrumentalResearchController::class, 'viewMedia'])->scopeBindings()->name('instrumentals.media.view');
        });

        Route::get('/tests/list', [TestsController::class, 'listTests'])->name('tests.list');
        Route::get('/{patient}/tests/assignments', [TestsController::class, 'listAssignments'])->name('tests.assignments');
        Route::post('/{patient}/tests/assign', [TestsController::class, 'assignTest'])->name('tests.assign');

        Route::post('/sessions/{session}/cancel', [TestsController::class, 'cancelSession'])->name('sessions.cancel');
        Route::get('/sessions/{session}/result', [TestsController::class, 'sessionResult'])->name('sessions.result');
        Route::get('/sessions/{session}/results', [TestsController::class, 'resultsPage'])->name('sessions.results.page');
        Route::get('/sessions/{session}', [TestsController::class, 'show'])->name('sessions.show');
        Route::post('/responses/{response}/code', [TestsController::class, 'saveResponseCode'])->name('responses.code');
        Route::post('/responses/{response}/rubric', [TestsController::class, 'saveRubricScore'])->name('responses.rubric');
        Route::post('/sessions/{session}/finish', [TestsController::class, 'finish'])->name('sessions.finish');
        Route::post('/sessions/{session}/submit', [TestsController::class, 'submit'])->name('sessions.session.submit');
        Route::post('/sessions/{session}/restart', [TestsController::class, 'restart'])->name('sessions.restart');

        Route::get('/testing/session/{token}', [PatientTestingController::class, 'form'])->name('session.form');

        Route::post('/tests/{assignment}/start', [TestSessionController::class, 'start'])->name('tests.start');

        Route::post('/ts/{token?}/unlock', [TestSessionController::class, 'unlock'])->name('ts.unlock');

        Route::prefix('contacts')->group(function (): void {
            Route::post('/', [ContactController::class, 'store'])->name('contacts.store');
            Route::put('/{contact}', [ContactController::class, 'update'])->name('contacts.update');
            Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
        });
    });

    Route::prefix('tests')->as('tests.')->group(function (): void {
        Route::get('/', [TestPanelController::class, 'index'])->name('index');
        Route::get('/mine', [TestPanelController::class, 'mine'])->name('mine');
        Route::post('/store/images', [TestPanelController::class, 'storeImageTest'])->name('store-image-test');
        Route::post('/store/questionnaire', [TestPanelController::class, 'storeQuestionnaireTest'])->name('store-questionnaire-test');
        Route::post('/store/cardsort', [TestPanelController::class, 'storeSortTest'])->name('store-card-sort-test');
        Route::post('/delete/{test}', [TestPanelController::class, 'destroy'])->name('destroy');
    });

    Route::get('/tests/{assignment}/pin', [TestSessionController::class, 'form'])->name('tests.pin.form');
    Route::post('/tests/{assignment}/pin', [TestSessionController::class, 'verify'])->name('tests.pin.verify');
    Route::get('/tests/{session}/final-pin', [TestSessionController::class, 'finalPinForm'])->name('tests.final_pin.form');
    Route::post('/tests/{session}/final-pin', [TestSessionController::class, 'finalPinVerify'])->name('tests.final_pin.verify');

    Route::prefix('analyses')->as('analyses.')->group(function (): void {
        Route::get('/', [AnalysesPanelController::class, 'overview'])->name('index');
        Route::get('/journal', [AnalysesPanelController::class, 'journal'])->name('journal');
        Route::get('/critical', [AnalysesPanelController::class, 'critical'])->name('critical');
        Route::get('/dynamics', [AnalysesPanelController::class, 'dynamics'])->name('dynamics');

        Route::get('/assignments/patients/search', [AnalysesPanelController::class, 'assignmentSearchPatients'])->name('assignments.patients.search');
        Route::get('/assignments/parameters/search', [AnalysesPanelController::class, 'assignmentSearchParameters'])->name('assignments.parameters.search');
        Route::get('/assignments', [AnalysesPanelController::class, 'assignments'])->name('assignments');
        Route::post('/assignments', [AnalysesPanelController::class, 'assignmentsStore'])->name('assignments.store');
        Route::get('/assignments/{labResearch}/print', [AnalysesPanelController::class, 'assignmentPrint'])->whereNumber('labResearch')->name('assignments.print');
    });

    Route::prefix('destinations')->as('destinations.')->group(function (): void {
        Route::post('/preview', [DoctorDestinationController::class, 'preview'])->name('destination.preview');
        Route::post('/store', [DoctorDestinationController::class, 'store'])->name('destination.store');
        Route::get('/print/{doctor_destination}', [DoctorDestinationController::class, 'print'])->name('destination.print');
    });

    Route::prefix('consiliums')->as('consiliums.')->group(function (): void {
        Route::post('/store', [ConsiliumController::class, 'store'])->name('store');
    });

    // -------------------- Врачебная комиссия --------------------
    Route::prefix('commissions')->as('commissions.')->group(function (): void {
        Route::get('/', [MedicalComissionController::class, 'index'])->name('index');
        Route::get('/create', [MedicalComissionController::class, 'create'])->name('create');
        Route::post('/', [MedicalComissionController::class, 'store'])->name('store');

        // -------------------- Военкомат / ВВК --------------------
        Route::prefix('military')->as('military.')->group(function (): void {
            Route::get('/', [MilitaryCommissionDocumentController::class, 'index'])->name('index');
            Route::get('/create', [MilitaryCommissionDocumentController::class, 'create'])->name('create');
            Route::post('/', [MilitaryCommissionDocumentController::class, 'store'])->name('store');
            Route::get('/{militaryDocument}', [MilitaryCommissionDocumentController::class, 'show'])
                ->whereNumber('militaryDocument')
                ->name('show');
            Route::get('/{militaryDocument}/edit', [MilitaryCommissionDocumentController::class, 'edit'])
                ->whereNumber('militaryDocument')
                ->name('edit');
            Route::put('/{militaryDocument}', [MilitaryCommissionDocumentController::class, 'update'])
                ->whereNumber('militaryDocument')
                ->name('update');
            Route::delete('/{militaryDocument}', [MilitaryCommissionDocumentController::class, 'destroy'])
                ->whereNumber('militaryDocument')
                ->name('destroy');
            Route::get('/{militaryDocument}/print', [MilitaryCommissionDocumentController::class, 'print'])
                ->whereNumber('militaryDocument')
                ->name('print');
        });

        // -------------------- МСЭ / форма 088/у --------------------
        Route::prefix('mse')->as('mse.')->group(function (): void {
            Route::get('/', [MseReferralController::class, 'index'])->name('index');
            Route::get('/create', [MseReferralController::class, 'create'])->name('create');
            Route::post('/', [MseReferralController::class, 'store'])->name('store');
            Route::post('/consent/preview', [MseReferralController::class, 'consentPreview'])->name('consent-preview');
            Route::get('/{mseReferral}', [MseReferralController::class, 'show'])
                ->whereNumber('mseReferral')
                ->name('show');
            Route::get('/{mseReferral}/edit', [MseReferralController::class, 'edit'])
                ->whereNumber('mseReferral')
                ->name('edit');
            Route::put('/{mseReferral}', [MseReferralController::class, 'update'])
                ->whereNumber('mseReferral')
                ->name('update');
            Route::delete('/{mseReferral}', [MseReferralController::class, 'destroy'])
                ->whereNumber('mseReferral')
                ->name('destroy');
            Route::get('/{mseReferral}/print', [MseReferralController::class, 'print'])
                ->whereNumber('mseReferral')
                ->name('print');
            Route::get('/{mseReferral}/consent/print', [MseReferralController::class, 'consentPrint'])
                ->whereNumber('mseReferral')
                ->name('consent-print');
            Route::get('/{mseReferral}/consent-document', [MseReferralController::class, 'consentDocument'])
                ->whereNumber('mseReferral')
                ->name('consent-document');
        });


        // -------------------- Водительская комиссия / психиатр --------------------
        Route::prefix('driver')->as('driver.')->group(function (): void {
            Route::get('/', [DriverCommissionController::class, 'index'])->name('index');
            Route::get('/create', [DriverCommissionController::class, 'create'])->name('create');
            Route::post('/', [DriverCommissionController::class, 'store'])->name('store');
            Route::get('/print/blank', [DriverCommissionController::class, 'printBlank'])->name('print-blank');
            Route::get('/{driverCommission}', [DriverCommissionController::class, 'show'])
                ->whereNumber('driverCommission')
                ->name('show');
            Route::get('/{driverCommission}/edit', [DriverCommissionController::class, 'edit'])
                ->whereNumber('driverCommission')
                ->name('edit');
            Route::put('/{driverCommission}', [DriverCommissionController::class, 'update'])
                ->whereNumber('driverCommission')
                ->name('update');
            Route::delete('/{driverCommission}', [DriverCommissionController::class, 'destroy'])
                ->whereNumber('driverCommission')
                ->name('destroy');
            Route::get('/{driverCommission}/print', [DriverCommissionController::class, 'print'])
                ->whereNumber('driverCommission')
                ->name('print');
        });
    });

    Route::prefix('settings')->as('settings.')->group(function (): void {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('index.update');
        Route::post('/employees/update', [SettingsController::class, 'updateEmployee'])->name('employees.update');

        Route::prefix('/offices')->group(function (): void {
            Route::get('/', [CabinetController::class, 'index'])->name('offices');
            Route::post('/store', [CabinetController::class, 'store'])->name('offices.store');
            Route::post('/update', [CabinetController::class, 'clinicDoctorUpdate'])->name('offices.update');
            Route::post('/delete', [CabinetController::class, 'deleteCabinet'])->name('offices.delete');
            Route::post('/buildings', [CabinetController::class, 'storeBuilding'])->name('offices.buildings.store');
            Route::post('/buildings/update', [CabinetController::class, 'updateBuilding'])->name('offices.buildings.update');
            Route::post('/buildings/delete', [CabinetController::class, 'deleteBuilding'])->name('offices.buildings.delete');
        });

        Route::get('/districts', [CabinetController::class, 'districts'])->name('districts');
        Route::post('/districts', [CabinetController::class, 'storeDistrict'])->name('districts.store');
        Route::post('/districts/{district}', [CabinetController::class, 'updateDistrict'])->name('districts.update');
        Route::post('/districts/{district}/transfer', [CabinetController::class, 'transferDistrict'])->name('districts.transfer');
        Route::post('/districts/{district}/separate', [CabinetController::class, 'separateDistrict'])->name('districts.separate');
        Route::post('/districts/{district}/delete', [CabinetController::class, 'deleteDistrict'])->name('districts.delete');
        Route::view('/clinic-schedule', 'doctors.settings.clinic_schedule')->name('clinic-schedule');
        Route::view('/doctors-schedule', 'doctors.settings.doctors_schedule')->name('doctors-schedule');
    });
});

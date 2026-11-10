<?php

use App\Http\Controllers\Doctors\AnalysesPanelController;
use App\Http\Controllers\Doctors\AnamnesisController;
use App\Http\Controllers\Doctors\CabinetController;
use App\Http\Controllers\Doctors\CardMedicalDocumentController;
use App\Http\Controllers\Doctors\ClinicAffiliationController;
use App\Http\Controllers\Doctors\ClinicCertificateHeaderController;
use App\Http\Controllers\Doctors\ClinicTelemedicineSettingsController;
use App\Http\Controllers\Doctors\ConsiliumController;
use App\Http\Controllers\Doctors\ContactController;
use App\Http\Controllers\Doctors\DoctorAppointmentTimezoneController;
use App\Http\Controllers\Doctors\DoctorDestinationController;
use App\Http\Controllers\Doctors\DocumentController;
use App\Http\Controllers\Doctors\DriverCommissionController;
use App\Http\Controllers\Doctors\EpicrisisController;
use App\Http\Controllers\Doctors\HospitalDepartmentController;
use App\Http\Controllers\Doctors\HospitalDischargeController;
use App\Http\Controllers\Doctors\HospitalDutyController;
use App\Http\Controllers\Doctors\HospitalImpersonationController;
use App\Http\Controllers\Doctors\HospitalInpatientRoomController;
use App\Http\Controllers\Doctors\HospitalizationReferralController;
use App\Http\Controllers\Doctors\HospitalRoomController;
use App\Http\Controllers\Doctors\IndexController as DoctorsIndexController;
use App\Http\Controllers\Doctors\InpatientAdmissionController;
use App\Http\Controllers\Doctors\InpatientSettingsWorkspaceController;
use App\Http\Controllers\Doctors\InstrumentalResearchController;
use App\Http\Controllers\Doctors\MedicalCardController;
use App\Http\Controllers\Doctors\PatientDoctorTransferController;
use App\Http\Controllers\Doctors\MedicalEncounterController;
use App\Http\Controllers\Doctors\MedicalVisitConfirmationController;
use App\Http\Controllers\Doctors\MedicalComissionController;
use App\Http\Controllers\Doctors\MilitaryCommissionDocumentController;
use App\Http\Controllers\Doctors\MseReferralController;
use App\Http\Controllers\Doctors\NeuralNetworkController;
use App\Http\Controllers\Doctors\PatientDisabilityController;
use App\Http\Controllers\Doctors\PatientDisabilityVerificationController;
use App\Http\Controllers\Doctors\PatientTestingController;
use App\Http\Controllers\Doctors\PrescriptionsController;
use App\Http\Controllers\Doctors\ReceptionController;
use App\Http\Controllers\Doctors\ReceptionArrivalController;
use App\Http\Controllers\Doctors\ResearchController;
use App\Http\Controllers\Doctors\SettingsController;
use App\Http\Controllers\Doctors\TestPanelController;
use App\Http\Controllers\Doctors\TestsController;
use App\Http\Controllers\Doctors\TestSessionController;
use App\Http\Controllers\Doctors\TelemedicineAvailableSlotsController;
use App\Http\Controllers\Doctors\TelemedicineEventIngestController;
use App\Http\Controllers\Doctors\TelemedicineJournalController;
use App\Http\Controllers\Doctors\TelemedicineJournalGateController;
use App\Http\Controllers\Doctors\TelemedicineProviderSessionController;
use App\Http\Controllers\Main\ArticleController;
use App\Http\Controllers\Main\BlogController;
use App\Http\Controllers\Main\ClinicController;
use App\Http\Controllers\Main\DoctorController;
use App\Http\Controllers\Main\FeedbackController;
use App\Http\Controllers\Main\IndexController as MainIndexController;
use App\Http\Controllers\Main\JurisprudenceController;
use App\Http\Controllers\Main\MedicineController;
use App\Http\Controllers\Laboratory\AuthController as LaboratoryAuthController;
use App\Http\Controllers\Laboratory\RealtimeFragmentController;
use App\Http\Controllers\Laboratory\WorkspaceController as LaboratoryWorkspaceController;
use App\Http\Controllers\TelemedicineInvitationController;
use App\Http\Controllers\UserController;

use App\Http\Middleware\CardDocumentPrivateResponse;
use App\Http\Middleware\ClinicSettingsAccessMiddleware;
use App\Http\Middleware\DoctorHardLockMiddleware;
use App\Http\Middleware\DoctorOrHospitalAdmin;
use App\Http\Middleware\EnsureClinicTelemedicineSettings;
use App\Http\Middleware\EnsureLaboratoryAccess;
use App\Http\Middleware\HospitalRoomContextMiddleware;
use App\Http\Middleware\LockClinicTypeMiddleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::get('/', MainIndexController::class)->name('main.index');
Route::get('/medicines', MedicineController::class)->name('main.medicines');
Route::get('/privacy-policy', MedicineController::class)->name('main.policy');
Route::get('/feedback', FeedbackController::class)->name('main.feedback');

if (config('app.debug')) {
    Route::get('/test-user', fn () => dd(auth()->user()));
}

Route::prefix('dictionary')->group(function (): void {

    Route::prefix('clinics')->group(function (): void {
        Route::get('/', [ClinicController::class, 'list'])->name('main.clinics');
        Route::get('/{clinic_id}', [ClinicController::class, 'index'])->name('main.clinics.form');
        Route::post('/feedback/{clinic_id}', [ClinicController::class, 'feedback'])->name('main.clinics.form.feedback-create');
        Route::get('/filter/{city_name}', [ClinicController::class, 'list'])->name('main.clinics.filters.city');
    });

    Route::prefix('doctors')->group(function (): void {
        Route::get('/', [DoctorController::class, 'index'])->where('clinic_id', '\d+')->name('main.doctors.form');
    });
});

Route::prefix('blog')->group(function (): void {
    Route::get('/', [BlogController::class, 'list'])->name('main.blog');
    Route::prefix('category')->group(function (): void {
        Route::get('/{category_id}', [BlogController::class, 'list'])->name('main.blog.category');
    });

    Route::get('/{topic_id}', [BlogController::class, 'index'])->name('main.blog.topic');
});

Route::prefix('articles')->group(function (): void {
    Route::get('/', [ArticleController::class, 'list'])->name('main.articles');
    Route::get('/{article_id}', [ArticleController::class, 'index'])->name('main.articles.show');
});

Route::prefix('jurisprudence')->group(function (): void {
    Route::get('/', [JurisprudenceController::class, 'list'])->name('main.jurisprudence');
});

Route::prefix('log')->group(function (): void {
    Route::get('out', [UserController::class, 'logout'])->name('actions.logout');
    Route::post('in', [UserController::class, 'login'])->name('actions.login');
});

Route::prefix('api')->group(function (): void {
    require __DIR__ . '/api.php';
});

// Shared endpoints for clinical users and authorized admins, including medical encounter routes.
// For doctors, DoctorOrHospitalAdmin invokes the existing DoctorHardLockMiddleware.
// Admins must also have explicit permissions and authorized clinic records.
Route::middleware(['auth', DoctorOrHospitalAdmin::class])->prefix('doctors')->as('doctors.')->group(function (): void {
    Route::get('/', [DoctorsIndexController::class, 'index'])->name('main');
    Route::get('/patients/{patient:id}', [MedicalCardController::class, 'index'])->whereNumber('patient')->name('patients.medical_card');

    // PATIENT_DOCTOR_TRANSFER_ROUTES_START
    Route::get('/patients/{patient:id}/doctor-transfer/candidates', [PatientDoctorTransferController::class, 'candidates'])
        ->whereNumber('patient')->middleware('throttle:60,1')
        ->name('patients.doctor-transfer.candidates');
    Route::post('/patients/{patient:id}/doctor-transfer', [PatientDoctorTransferController::class, 'store'])
        ->whereNumber('patient')->middleware('throttle:10,1')
        ->name('patients.doctor-transfer.store');
    // PATIENT_DOCTOR_TRANSFER_ROUTES_END


    // Отдельные сведения об инвалидности: ручное внесение и проверка документа.
    // Те же auth / DoctorOrHospitalAdmin, что и при открытии медкарты.
    // PatientDisabilityController дополнительно проверяет canRead + canEdit для пациента.
    Route::post('/patients/{patient:id}/disability', [PatientDisabilityController::class, 'save'])
        ->whereNumber('patient')->middleware('throttle:20,1')
        ->name('patients.disability.save');

    Route::post('/patients/{patient:id}/disability/verification', [PatientDisabilityController::class, 'verification'])
        ->whereNumber('patient')->middleware('throttle:10,1')
        ->name('patients.disability.verification');

    // Приёмы и телемедицина: те же auth / DoctorOrHospitalAdmin, что и у медицинской карты.
    // Проверка доступа к конкретному пациенту и клинике выполняется в MedicalEncounterController.
    Route::post('/patients/{patient:id}/visits/{registryRecord}/encounter', [MedicalEncounterController::class, 'save'])
        ->whereNumber('registryRecord')
        ->middleware('throttle:30,1')
        ->name('patients.visits.encounter.save');

    Route::get('/patients/{patient:id}/visits/{registryRecord}/protocol', [MedicalEncounterController::class, 'printProtocol'])
        ->whereNumber('registryRecord')
        ->name('patients.visits.protocol.print');

    // Подтверждение документа МСЭ: тот же входной guard, что и у медкарты.
    Route::post(
        '/patients/{patient:id}/disability/verify-document',
        [PatientDisabilityVerificationController::class, 'verify']
    )
        ->whereNumber('patient')
        ->middleware('throttle:10,1')
        ->name('patients.disability.verify-document');

    // Свободные слоты телемедицины для выбранного пациента.
    Route::get(
        '/patients/{patient:id}/telemedicine/available-slots',
        TelemedicineAvailableSlotsController::class
    )
        ->whereNumber('patient')
        ->middleware('throttle:60,1')
        ->name('patients.telemedicine.available-slots');

    Route::post('/workbench/assignments/{assignment}/attending', [DoctorsIndexController::class, 'updateAttendingDoctor'])->whereNumber('assignment')->name('workbench.attending.update');
});

Route::middleware(['auth', ClinicSettingsAccessMiddleware::class, DoctorHardLockMiddleware::class])->group(function (): void {
    Route::get('doctors/settings/clinic-affiliations', [ClinicAffiliationController::class, 'index'])->name('doctors.settings.clinic-affiliations.index');

    Route::post('doctors/settings/clinic-affiliations', [ClinicAffiliationController::class, 'update'])->name('doctors.settings.clinic-affiliations.update');

    // Новый стационар: доступ проверяется в ClinicAffiliationController::store.
    Route::post('doctors/settings/clinic-affiliations/create', [ClinicAffiliationController::class, 'store'])->name('doctors.settings.clinic-affiliations.store');
});

Route::middleware([RoleMiddleware::class . (config('app.debug') ? ':admins|doctors' : ':doctors'), DoctorHardLockMiddleware::class])->prefix('doctors')->as('doctors.')->group(function (): void {

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
        Route::get('/availability', [ReceptionController::class, 'availability'])->name('availability');
        Route::post('/store', [ReceptionController::class, 'store'])->name('store');
        Route::get('/archives', [ReceptionController::class, 'archives'])->name('archives');
        Route::get('/edit/{registryRecord}', [ReceptionController::class, 'edit'])->name('edit');
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

        Route::post('/analyze-anamnesis', [NeuralNetworkController::class, 'analyzeAnamnesis'])->name('analyze.anamnesis');

        Route::prefix('{patient:id}')->where(['patient' => '\d+'])->group(function (): void {
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
            Route::get('/{militaryDocument}', [MilitaryCommissionDocumentController::class, 'show'])->whereNumber('militaryDocument')->name('show');
            Route::get('/{militaryDocument}/edit', [MilitaryCommissionDocumentController::class, 'edit'])->whereNumber('militaryDocument')->name('edit');
            Route::put('/{militaryDocument}', [MilitaryCommissionDocumentController::class, 'update'])->whereNumber('militaryDocument')->name('update');
            Route::delete('/{militaryDocument}', [MilitaryCommissionDocumentController::class, 'destroy'])->whereNumber('militaryDocument')->name('destroy');
            Route::get('/{militaryDocument}/print', [MilitaryCommissionDocumentController::class, 'print'])->whereNumber('militaryDocument')->name('print');
        });

        // -------------------- МСЭ / форма 088/у --------------------
        Route::prefix('mse')->as('mse.')->group(function (): void {
            Route::get('/', [MseReferralController::class, 'index'])->name('index');
            Route::get('/create', [MseReferralController::class, 'create'])->name('create');
            Route::post('/', [MseReferralController::class, 'store'])->name('store');
            Route::post('/consent/preview', [MseReferralController::class, 'consentPreview'])->name('consent-preview');
            Route::get('/{mseReferral}', [MseReferralController::class, 'show'])->whereNumber('mseReferral')->name('show');
            Route::get('/{mseReferral}/edit', [MseReferralController::class, 'edit'])->whereNumber('mseReferral')->name('edit');
            Route::put('/{mseReferral}', [MseReferralController::class, 'update'])->whereNumber('mseReferral')->name('update');
            Route::delete('/{mseReferral}', [MseReferralController::class, 'destroy'])->whereNumber('mseReferral')->name('destroy');
            Route::get('/{mseReferral}/print', [MseReferralController::class, 'print'])->whereNumber('mseReferral')->name('print');
            Route::get('/{mseReferral}/consent/print', [MseReferralController::class, 'consentPrint'])->whereNumber('mseReferral')->name('consent-print');
            Route::get('/{mseReferral}/consent-document', [MseReferralController::class, 'consentDocument'])->whereNumber('mseReferral')->name('consent-document');
        });

        // -------------------- Водительская комиссия / психиатр --------------------
        Route::prefix('driver')->as('driver.')->group(function (): void {
            Route::get('/', [DriverCommissionController::class, 'index'])->name('index');
            Route::get('/create', [DriverCommissionController::class, 'create'])->name('create');
            Route::post('/', [DriverCommissionController::class, 'store'])->name('store');
            Route::get('/print/blank', [DriverCommissionController::class, 'printBlank'])->name('print-blank');
            Route::get('/{driverCommission}', [DriverCommissionController::class, 'show'])->whereNumber('driverCommission')->name('show');
            Route::get('/{driverCommission}/edit', [DriverCommissionController::class, 'edit'])->whereNumber('driverCommission')->name('edit');
            Route::put('/{driverCommission}', [DriverCommissionController::class, 'update'])->whereNumber('driverCommission')->name('update');
            Route::delete('/{driverCommission}', [DriverCommissionController::class, 'destroy'])->whereNumber('driverCommission')->name('destroy');
            Route::get('/{driverCommission}/print', [DriverCommissionController::class, 'print'])->whereNumber('driverCommission')->name('print');
        });
    });

    // Госпитализация: список размещений, варианты и сохранение переноса пациента.
    // Наследует middleware и префикс doctors. от родительской группы.
    Route::prefix('inpatient/admissions')->as('inpatient.admissions.')->group(function (): void {
        Route::get('/', [InpatientAdmissionController::class, 'index'])->name('index');
        Route::get('/options', [InpatientAdmissionController::class, 'options'])->name('options');
        Route::get('/patients/search', [InpatientAdmissionController::class, 'searchPatients'])->name('patients.search');
        Route::post('/', [InpatientAdmissionController::class, 'store'])->name('store');
    });

    Route::get('/inpatient/discharges', [HospitalDischargeController::class, 'index'])->name('inpatient.discharges.index');

    Route::get('/inpatient/rooms', [HospitalInpatientRoomController::class, 'index'])->name('inpatient.rooms.index');

    Route::get('/inpatient/rooms/patients/search', [HospitalRoomController::class, 'patientSearch'])->name('inpatient.rooms.patients.search');

    Route::post('/inpatient/rooms/beds/{bed}/assign', [HospitalRoomController::class, 'assignBed'])->whereNumber('bed')->name('inpatient.rooms.beds.assign');

    Route::post('/inpatient/rooms/beds/{bed}/release', [HospitalRoomController::class, 'releaseBed'])->whereNumber('bed')->name('inpatient.rooms.beds.release');

});

// Временное администрирование стационара под текущим clinic_admin (v3).
// Авторизация пользователя не переключается; контекст и права проверяют контроллер и web guard.
Route::middleware(['auth', ClinicSettingsAccessMiddleware::class, DoctorHardLockMiddleware::class])->post('doctors/settings/clinic-affiliations/hospitals/{hospital}/login-as', [HospitalImpersonationController::class, 'enter'])->whereNumber('hospital')->name('doctors.hospital-impersonation.enter');

Route::middleware('auth')->group(function (): void {
    Route::get('doctors/hospital-impersonation/bridge', [HospitalImpersonationController::class, 'bridge'])->name('doctors.hospital-impersonation.bridge');
    Route::post('doctors/hospital-impersonation/leave', [HospitalImpersonationController::class, 'leave'])->name('doctors.hospital-impersonation.leave');
    Route::get('doctors/hospital-impersonation/hospitals/{hospital}/settings', [InpatientSettingsWorkspaceController::class, 'show'])->whereNumber('hospital')->name('doctors.hospital-impersonation.workspace');
    Route::post('doctors/hospital-impersonation/hospitals/{hospital}/settings', [InpatientSettingsWorkspaceController::class, 'save'])->whereNumber('hospital')->name('doctors.hospital-impersonation.workspace.save');
});

// ПОЛНЫЙ конструктор палат выбранного стационара, отдельно от настроек амбулатории.
// Не требует второго администратора; каждый запрос заново проверяет госпитальный контекст.
Route::middleware(['auth', ClinicSettingsAccessMiddleware::class, DoctorHardLockMiddleware::class, HospitalRoomContextMiddleware::class])->prefix('doctors/hospital-impersonation/hospitals/{hospital}/rooms')->as('doctors.hospital-impersonation.rooms.')->group(function (): void {
    Route::get('/', [HospitalRoomController::class, 'index'])->name('index');
    Route::get('/data', [HospitalRoomController::class, 'data'])->name('data');
    Route::post('/', [HospitalRoomController::class, 'store'])->name('store');
    Route::post('/{room}/layout', [HospitalRoomController::class, 'saveLayout'])->whereNumber('room')->name('layout');
    Route::post('/{room}/delete', [HospitalRoomController::class, 'destroy'])->whereNumber('room')->name('delete');
    Route::post('/{room}', [HospitalRoomController::class, 'update'])->whereNumber('room')->name('update');
});

Route::middleware(['auth', ClinicSettingsAccessMiddleware::class, DoctorHardLockMiddleware::class])->prefix('doctors')->as('doctors.')->group(function (): void {
    Route::prefix('settings')->as('settings.')->group(function (): void {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->middleware(LockClinicTypeMiddleware::class)->name('index.update');
        Route::post('/employees/update', [SettingsController::class, 'updateEmployee'])->name('employees.update');

        // Единая шапка документов клиники и всей её группы учреждений.
        Route::prefix('/certificate-header')->as('certificate-header.')->group(function (): void {
            Route::get('/', [ClinicCertificateHeaderController::class, 'index'])->name('index');
            Route::post('/', [ClinicCertificateHeaderController::class, 'store'])->name('store');
            Route::delete('/', [ClinicCertificateHeaderController::class, 'destroy'])->name('destroy');
            Route::get('/image', [ClinicCertificateHeaderController::class, 'settingsImage'])->name('image');
        });

        // Настройки телемедицины клиники. Базовые auth/settings middleware уже у родительской группы.
        Route::middleware(EnsureClinicTelemedicineSettings::class)
            ->prefix('/telemedicine')
            ->as('telemedicine.')
            ->group(function (): void {
                Route::get('/', [ClinicTelemedicineSettingsController::class, 'index'])->name('index');
                Route::post('/', [ClinicTelemedicineSettingsController::class, 'update'])->name('update');
            });


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
        Route::post('/districts/{district}', [CabinetController::class, 'updateDistrict'])->whereNumber('district')->name('districts.update');
        Route::post('/districts/{district}/transfer', [CabinetController::class, 'transferDistrict'])->whereNumber('district')->name('districts.transfer');
        Route::post('/districts/{district}/separate', [CabinetController::class, 'separateDistrict'])->whereNumber('district')->name('districts.separate');
        Route::post('/districts/{district}/delete', [CabinetController::class, 'deleteDistrict'])->whereNumber('district')->name('districts.delete');
        Route::get('/districts/clinic-schedule', [CabinetController::class, 'clinicSchedule'])->name('clinic-schedule');
        Route::post('/districts/clinic-schedule/update', [CabinetController::class, 'clinicScheduleUpdate'])->name('clinic-schedule.update');

        Route::post('/districts/clinic-schedule/exceptions', [CabinetController::class, 'clinicScheduleExceptionStore'])->name('clinic-schedule.exceptions.store');

        Route::post('/districts/clinic-schedule/exceptions/{exception}', [CabinetController::class, 'clinicScheduleExceptionUpdate'])->whereNumber('exception')->name('clinic-schedule.exceptions.update');

        Route::post('/districts/clinic-schedule/exceptions/{exception}/delete', [CabinetController::class, 'clinicScheduleExceptionDelete'])->whereNumber('exception')->name('clinic-schedule.exceptions.delete');

        Route::get('/districts/doctors', [CabinetController::class, 'indexDoctorScheduleTable'])->name('doctors-schedule');

        Route::get('/vacation-schedule', [CabinetController::class, 'vacationSchedule'])->name('vacation-schedule');
        Route::post('/vacation-schedule', [CabinetController::class, 'vacationScheduleStore'])->name('vacation-schedule.store');
        Route::post('/vacation-schedule/{vacation}', [CabinetController::class, 'vacationScheduleUpdate'])->whereNumber('vacation')->name('vacation-schedule.update');
        Route::post('/vacation-schedule/{vacation}/delete', [CabinetController::class, 'vacationScheduleDelete'])->whereNumber('vacation')->name('vacation-schedule.delete');

        Route::get('/duty-schedule', [HospitalDutyController::class, 'index'])->name('duty-schedule');
        Route::post('/duty-schedule', [HospitalDutyController::class, 'store'])->name('duty-schedule.store');
        Route::post('/duty-schedule/{duty}', [HospitalDutyController::class, 'update'])->whereNumber('duty')->name('duty-schedule.update');
        Route::post('/duty-schedule/{duty}/delete', [HospitalDutyController::class, 'destroy'])->whereNumber('duty')->name('duty-schedule.delete');

        Route::prefix('/departments')->group(function (): void {
            Route::get('/', [HospitalDepartmentController::class, 'index'])->name('departments');

            Route::post('/buildings', [HospitalDepartmentController::class, 'storeBuilding'])->name('departments.buildings.store');

            Route::post('/buildings/update', [HospitalDepartmentController::class, 'updateBuilding'])->name('departments.buildings.update');

            Route::post('/buildings/delete', [HospitalDepartmentController::class, 'deleteBuilding'])->name('departments.buildings.delete');

            Route::post('/', [HospitalDepartmentController::class, 'store'])->name('departments.store');

            Route::post('/{department}', [HospitalDepartmentController::class, 'update'])->whereNumber('department')->name('departments.update');

            Route::post('/{department}/delete', [HospitalDepartmentController::class, 'destroy'])->whereNumber('department')->name('departments.delete');
        });

        Route::prefix('/rooms')->group(function (): void {
            Route::get('/', [HospitalRoomController::class, 'index'])->name('rooms');

            Route::get('/data', [HospitalRoomController::class, 'data'])->name('rooms.data');

            Route::get('/patients/search', [HospitalRoomController::class, 'patientSearch'])->name('rooms.patients.search');

            Route::post('/', [HospitalRoomController::class, 'store'])->name('rooms.store');

            Route::post('/beds/{bed}/assign', [HospitalRoomController::class, 'assignBed'])->whereNumber('bed')->name('rooms.beds.assign');

            Route::post('/beds/{bed}/release', [HospitalRoomController::class, 'releaseBed'])->whereNumber('bed')->name('rooms.beds.release');

            Route::post('/{room}/layout', [HospitalRoomController::class, 'saveLayout'])->whereNumber('room')->name('rooms.layout');

            Route::post('/{room}/delete', [HospitalRoomController::class, 'destroy'])->whereNumber('room')->name('rooms.delete');

            Route::post('/{room}', [HospitalRoomController::class, 'update'])->whereNumber('room')->name('rooms.update');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Дополнительные маршруты, ранее вынесенные в отдельные routes/*.php
|--------------------------------------------------------------------------
|
| web.php уже загружается с middleware-группой "web", поэтому здесь не
| дублируем middleware "web". Специальные auth/guard/throttle оставлены
| только там, где они действительно нужны по бизнес-логике.
|
*/

// Льготные рецепты. Тонкая проверка пациента/права выполняется контроллером.
Route::middleware('auth')
    ->prefix('doctors/patients/{patient}/benefit-prescriptions')
    ->name('doctors.prescriptions.benefit.')
    ->group(function (): void {
        Route::get('drug', [PrescriptionsController::class, 'benefitDrug'])->name('drug');
        Route::post('/', [PrescriptionsController::class, 'storeBenefit'])->name('store');
        Route::get('{benefit}/edit', [PrescriptionsController::class, 'editBenefit'])
            ->whereUuid('benefit')
            ->name('edit');
        Route::get('{benefit}/print', [PrescriptionsController::class, 'printBenefit'])
            ->whereUuid('benefit')
            ->name('print');
        Route::post('{benefit}/void', [PrescriptionsController::class, 'voidBenefit'])
            ->whereUuid('benefit')
            ->name('void');
    });

// Факт явки пациента. Оставлен auth-only: доступ к конкретной записи проверяет контроллер.
Route::middleware('auth')
    ->post(
        '/doctors/reception/records/{registryRecord}/arrived',
        [ReceptionArrivalController::class, 'confirm']
    )
    ->whereNumber('registryRecord')
    ->name('doctors.reception.arrival.confirm');

// Подтверждение результата медицинского визита.
Route::middleware('auth')
    ->post(
        '/doctors/patients/{patient}/visits/{registryRecord}/confirm',
        [MedicalVisitConfirmationController::class, 'store']
    )
    ->whereNumber('registryRecord')
    ->name('doctors.patients.visits.confirm');

// Журнал телемедицины: отдельная политика TelemedicineJournalAccess внутри сервиса.
Route::middleware(['auth', 'throttle:30,1'])->group(function (): void {
    Route::post(
        '/doctors/patients/{patient}/telemedicine-journal',
        [TelemedicineJournalController::class, 'store']
    )
        ->whereNumber('patient')
        ->name('doctors.patients.telemedicine-journal.store');

    Route::delete(
        '/doctors/patients/{patient}/telemedicine-journal/{registryRecord}',
        [TelemedicineJournalController::class, 'destroy']
    )
        ->whereNumber(['patient', 'registryRecord'])
        ->name('doctors.patients.telemedicine-journal.destroy');
});

// Часовой пояс врача: clinic_admin либо сам врач — проверяется контроллером.
Route::middleware('auth')
    ->put(
        '/doctors/settings/doctors/{doctor}/appointment-timezone',
        [DoctorAppointmentTimezoneController::class, 'update']
    )
    ->whereNumber('doctor')
    ->middleware('throttle:30,1')
    ->name('doctors.settings.doctor-appointment-timezone.update');

// Изображение шапки печатной формы пациента.
// HospitalPatientAccess/clinic_admin проверяются в ClinicCertificateHeaderController.
Route::middleware('auth')
    ->get(
        '/doctors/patients/{patient:id}/certificate-header',
        [ClinicCertificateHeaderController::class, 'patientImage']
    )
    ->whereNumber('patient')
    ->name('doctors.patients.certificate-header.image');

// WebRTC / Яндекс.Телемост: действия врача.
Route::middleware(['auth', DoctorHardLockMiddleware::class, 'throttle:30,1'])
    ->group(function (): void {
        $telemedicineConnectionPath =
            '/doctors/patients/{patient}/telemedicine-connection/{recordId}';

        Route::get(
            $telemedicineConnectionPath,
            [TelemedicineProviderSessionController::class, 'show']
        )
            ->whereNumber(['patient', 'recordId'])
            ->name('doctors.telemedicine-session.show');

        Route::post(
            $telemedicineConnectionPath . '/prepare',
            [TelemedicineProviderSessionController::class, 'prepare']
        )
            ->whereNumber(['patient', 'recordId'])
            ->name('doctors.telemedicine-session.prepare');

        Route::post(
            $telemedicineConnectionPath . '/recover',
            [TelemedicineProviderSessionController::class, 'recover']
        )
            ->whereNumber(['patient', 'recordId'])
            ->name('doctors.telemedicine-session.recover');

        Route::post(
            $telemedicineConnectionPath . '/doctor-join',
            [TelemedicineProviderSessionController::class, 'doctorJoin']
        )
            ->whereNumber(['patient', 'recordId'])
            ->name('doctors.telemedicine-session.doctor-join');
    });

// Подписанная пациентская ссылка на телемедицинский сеанс.
Route::middleware(['signed', 'throttle:30,1'])->group(function (): void {
    Route::get(
        '/telemedicine/invitation/{meeting}',
        [TelemedicineInvitationController::class, 'show']
    )
        ->whereUuid('meeting')
        ->name('telemedicine.invitation.show');

    Route::post(
        '/telemedicine/invitation/{meeting}/join',
        [TelemedicineInvitationController::class, 'join']
    )
        ->whereUuid('meeting')
        ->name('telemedicine.invitation.join');
});

// Signaling -> Laravel: HMAC-аутентификация выполняется контроллером по raw body.
Route::post(
    '/internal/telemedicine/audit/events',
    [TelemedicineEventIngestController::class, 'store']
)
    ->middleware('throttle:120,1')
    ->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ])
    ->name('internal.telemedicine.audit.events');

// Signaling -> Laravel: проверка допуска к комнате телемедицины.
Route::post(
    '/internal/telemedicine/journal/authorize',
    [TelemedicineJournalGateController::class, 'check']
)
    ->middleware('throttle:120,1')
    ->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ])
    ->name('internal.telemedicine.journal.authorize');

/*
|--------------------------------------------------------------------------
| Лаборатория
|--------------------------------------------------------------------------
|
| Это обычные web-маршруты. Broadcast channel authorization остаётся
| в routes/channels.php -> routes/laboratory-channels.php.
|
*/
Route::prefix('laboratory')->as('laboratory.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LaboratoryAuthController::class, 'create'])
            ->name('login');

        Route::post('/login', [LaboratoryAuthController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('login.submit');
    });

    Route::middleware(['auth', EnsureLaboratoryAccess::class])
        ->group(function (): void {
            Route::get('/', [LaboratoryWorkspaceController::class, 'index'])
                ->name('index');

            Route::get('/researches/fragment', RealtimeFragmentController::class)
                ->middleware('throttle:120,1')
                ->name('researches.fragment');

            Route::post('/broadcasting/auth', static function (Request $request) {
                return Broadcast::auth($request);
            })
                ->middleware('throttle:120,1')
                ->name('broadcasting.auth');

            Route::get(
                '/researches/{research}',
                [LaboratoryWorkspaceController::class, 'edit']
            )
                ->whereNumber('research')
                ->name('researches.edit');

            Route::put(
                '/researches/{research}',
                [LaboratoryWorkspaceController::class, 'update']
            )
                ->whereNumber('research')
                ->middleware('throttle:30,1')
                ->name('researches.update');

            Route::post('/logout', [LaboratoryAuthController::class, 'destroy'])
                ->name('logout');
        });
});

// NORHNAIM_AI_MEDICAL_DOCUMENT_QUEUE_V1
Route::middleware([
    'auth',
    \App\Http\Middleware\DoctorOrHospitalAdmin::class,
])
    ->prefix('doctors/patients/{patient:id}/ai-document-tasks')
    ->name('doctors.patients.ai-document-tasks.')
    ->group(function (): void {
        Route::post(
            '/',
            [\App\Http\Controllers\Doctors\MedicalDocumentAiTaskController::class, 'store']
        )->name('store');

        Route::get(
            '/{task}',
            [\App\Http\Controllers\Doctors\MedicalDocumentAiTaskController::class, 'show']
        )->whereUuid('task')->name('show');

        Route::post(
            '/{task}/cancel',
            [\App\Http\Controllers\Doctors\MedicalDocumentAiTaskController::class, 'cancel']
        )->whereUuid('task')->name('cancel');

        Route::post(
            '/{task}/anamneses/confirm',
            [\App\Http\Controllers\Doctors\MedicalDocumentAiTaskController::class, 'confirmAnamneses']
        )->whereUuid('task')->name('anamneses.confirm');
    });

// NORHNAIM_EPICRISIS_FORM_CONTEXT_V1
Route::middleware([
    'auth',
    \App\Http\Middleware\DoctorOrHospitalAdmin::class,
])
    ->get(
        '/doctors/patients/{patient:id}/epicrisis-form-context',
        [\App\Http\Controllers\Doctors\EpicrisisFormContextController::class, 'show']
    )
    ->whereNumber('patient')
    ->name('doctors.patients.epicrisis.form-context');

// NORHNAIM_CERTIFICATE_PRINT_CONTEXT_V1
Route::middleware([
    'auth',
    \App\Http\Middleware\DoctorOrHospitalAdmin::class,
])
    ->get(
        '/doctors/patients/{patient:id}/certificate/print-context',
        [\App\Http\Controllers\Doctors\CertificatePrintContextController::class, 'show']
    )
    ->whereNumber('patient')
    ->name('doctors.patients.certificate.print-context');

// NORHNAIM_DOCTOR_PERSONAL_CABINET_V2
Route::middleware([
    'auth',
    \App\Http\Middleware\DoctorOrHospitalAdmin::class,
])
    ->prefix('doctors/profile')
    ->name('doctors.profile.')
    ->group(function (): void {
        Route::get(
            '/',
            [\App\Http\Controllers\Doctors\DoctorProfileController::class, 'index']
        )->name('index');

        Route::post(
            '/photo',
            [\App\Http\Controllers\Doctors\DoctorProfileController::class, 'photo']
        )->name('photo');

        Route::post(
            '/credentials',
            [\App\Http\Controllers\Doctors\DoctorProfileController::class, 'credentialStore']
        )->name('credentials.store');

        Route::post(
            '/credentials/{credential}/verify',
            [\App\Http\Controllers\Doctors\DoctorProfileController::class, 'credentialVerify']
        )
            ->whereNumber('credential')
            ->name('credentials.verify');

        Route::get(
            '/credentials/{credential}/download',
            [\App\Http\Controllers\Doctors\DoctorProfileController::class, 'credentialDownload']
        )
            ->whereNumber('credential')
            ->name('credentials.download');
    });


// NORHNAIM_DOCTOR_PROFILE_UPDATE_ROUTE_V2
Route::patch(
    '/doctors/profile',
    [\App\Http\Controllers\Doctors\DoctorProfileController::class, 'update']
)
    ->middleware([
        'auth',
        \App\Http\Middleware\DoctorOrHospitalAdmin::class,
    ])
    ->name('doctors.profile.update');

// NORHNAIM_DOCTOR_PROFILE_PHOTO_DELETE_ROUTE_V21
Route::delete(
    '/doctors/profile/photo',
    [\App\Http\Controllers\Doctors\DoctorProfileController::class, 'photoDestroy']
)
    ->middleware([
        'auth',
        \App\Http\Middleware\DoctorOrHospitalAdmin::class,
    ])
    ->name('doctors.profile.photo.destroy');

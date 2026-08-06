<?php

use App\Http\Controllers\Doctors\AnamnesisController;
use App\Http\Controllers\Doctors\ContactController;
use App\Http\Controllers\Doctors\Doctors\PatientTestingController;
use App\Http\Controllers\Doctors\DocumentController;
use App\Http\Controllers\Doctors\EpicrisisController;
use App\Http\Controllers\Doctors\InstrumentalResearchController;
use App\Http\Controllers\Doctors\PrescriptionsController;
use App\Http\Controllers\Doctors\ResearchController;
use App\Http\Controllers\Doctors\TestsController;
use App\Http\Controllers\Doctors\TestSessionController;
use App\Http\Controllers\MedicalCardController;
use App\Http\Controllers\Doctors\AnalysesPanelController;
use App\Http\Controllers\Doctors\IndexController as DoctorsIndexController;
use App\Http\Controllers\Doctors\ReceptionController;
use App\Http\Controllers\Doctors\TestPanelController;
use App\Http\Controllers\Main\IndexController as MainIndexController;
use App\Http\Middleware\DoctorHardLockMiddleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

// ---------- Main (public) ----------
Route::get('/', MainIndexController::class)
    ->name('main.index');

Route::get(
    '/medicines',
    \App\Http\Controllers\Main\MedicineController::class
)->name('main.medicines');

Route::get(
    '/privacy-policy',
    \App\Http\Controllers\Main\MedicineController::class
)->name('main.policy');

Route::get(
    '/feedback',
    \App\Http\Controllers\Main\FeedbackController::class
)->name('main.feedback');

if (config('app.debug')) {
    Route::get('/test-user', fn () => dd(auth()->user()));
}

// ---------- Dictionaries ----------
Route::prefix('dictionary')->group(function (): void {

    // Clinics
    Route::prefix('clinics')->group(function (): void {
        Route::get(
            '/',
            [\App\Http\Controllers\Main\ClinicController::class, 'list']
        )->name('main.clinics');

        Route::get(
            '/{clinic_id}',
            [\App\Http\Controllers\Main\ClinicController::class, 'index']
        )->name('main.clinics.form');

        Route::post(
            '/feedback/{clinic_id}',
            [\App\Http\Controllers\Main\ClinicController::class, 'feedback']
        )->name('main.clinics.form.feedback-create');

        Route::get(
            '/filter/{city_name}',
            [\App\Http\Controllers\Main\ClinicController::class, 'list']
        )->name('main.clinics.filters.city');
    });

    // Doctors
    Route::prefix('doctors')->group(function (): void {
        Route::get(
            '/{clinic_id}',
            [\App\Http\Controllers\Main\DoctorController::class, 'index']
        )
            ->where('clinic_id', '\d+')
            ->name('main.doctors.form');

//        Route::post(
//            '/feedback/{doctor_id}',
//            [\App\Http\Controllers\Main\DoctorController::class, 'feedback']
//        )->name('main.doctors.form.feedback-create');
//    });
    });
});

// ---------- Blog / Articles / Jurisprudence ----------
Route::prefix('blog')->group(function (): void {
    Route::get(
        '/',
        [\App\Http\Controllers\Main\BlogController::class, 'list']
    )->name('main.blog');

    Route::prefix('category')->group(function (): void {
        Route::get(
            '/{category_id}',
            [\App\Http\Controllers\Main\BlogController::class, 'list']
        )->name('main.blog.category');
    });

    Route::get(
        '/{topic_id}',
        [\App\Http\Controllers\Main\BlogController::class, 'index']
    )->name('main.blog.topic');
});

Route::prefix('articles')->group(function (): void {
    Route::get(
        '/',
        [\App\Http\Controllers\Main\ArticleController::class, 'list']
    )->name('main.articles');

    Route::get(
        '/{article_id}',
        [\App\Http\Controllers\Main\ArticleController::class, 'index']
    )->name('main.articles.show');
});

Route::prefix('jurisprudence')->group(function (): void {
    Route::get(
        '/',
        [\App\Http\Controllers\Main\JurisprudenceController::class, 'list']
    )->name('main.jurisprudence');
});

// ---------- Auth ----------
Route::prefix('log')->group(function (): void {
    Route::get(
        'out',
        [\App\Http\Controllers\UserController::class, 'logout']
    )->name('actions.logout');

    Route::post(
        'in',
        [\App\Http\Controllers\UserController::class, 'login']
    )->name('actions.login');
});

// ---------- API ----------
Route::prefix('api')->group(function (): void {
    require __DIR__ . '/api.php';
});

// =====================================================================
//                          DOCTORS PANEL (secured)
// =====================================================================

// Доступ только врачам (в дебаге — админам тоже)
Route::middleware([
    RoleMiddleware::class
    . (config('app.debug') ? ':admins|doctors' : ':doctors'),

    // Жёсткий замок панели во время теста
    DoctorHardLockMiddleware::class,
])
    ->prefix('doctors')
    ->as('doctors.')
    ->group(function (): void {

        // Главная панели
        Route::get(
            '/',
            [DoctorsIndexController::class, 'index']
        )->name('main');

        // -------------------- Prescriptions --------------------
        Route::prefix('prescriptions')
            ->as('prescriptions.')
            ->group(function (): void {
                Route::get(
                    '/',
                    [PrescriptionsController::class, 'index']
                )->name('index');

                Route::get(
                    '/create',
                    [PrescriptionsController::class, 'create']
                )->name('new');

                Route::post(
                    '/store',
                    [PrescriptionsController::class, 'store']
                )->name('store');

                Route::get(
                    '/drugs',
                    [PrescriptionsController::class, 'base']
                )->name('base');

                Route::post(
                    '/print',
                    [PrescriptionsController::class, 'print']
                )->name('print');

                Route::get(
                    '/print/{id}',
                    [PrescriptionsController::class, 'printForTable']
                )->name('print.from_table');

                Route::post(
                    '/repeat/{prescription}',
                    [PrescriptionsController::class, 'repeatPrescription']
                )->name('repeat');
            });

        // -------------------- Reception --------------------
        Route::prefix('reception')
            ->as('reception.')
            ->group(function (): void {
                Route::get(
                    '/',
                    [ReceptionController::class, 'index']
                )->name('index');

                Route::get(
                    '/create',
                    [ReceptionController::class, 'create']
                )->name('create');

                Route::post(
                    '/store',
                    [ReceptionController::class, 'store']
                )->name('store');

                Route::get(
                    '/archives',
                    [ReceptionController::class, 'archives']
                )->name('archives');

                Route::get(
                    '/edit/{record_id}',
                    [ReceptionController::class, 'edit']
                )->name('edit');

                Route::post(
                    '/edit/{id}',
                    [ReceptionController::class, 'update']
                )->name('update');

                Route::post(
                    '/delete/{id}',
                    [ReceptionController::class, 'destroy']
                )->name('destroy');

                Route::prefix('tests')
                    ->as('tests.')
                    ->group(function (): void {
                        Route::get(
                            '/run/{session}',
                            [TestSessionController::class, 'run']
                        )->name('run');

                        Route::get(
                            '/{session}/payload',
                            [TestSessionController::class, 'payload']
                        )->name('payload');

                        Route::post(
                            '/{session}/submit',
                            [TestSessionController::class, 'submit']
                        )->name('submit');

                        Route::post(
                            '/{session}/finish',
                            [TestSessionController::class, 'finish']
                        )->name('finish');

                        Route::post(
                            '/{session}/force-finish',
                            [TestSessionController::class, 'forceFinish']
                        )->name('force-finish');
                    });
            });

        // -------------------- Patients --------------------
        Route::prefix('patients')
            ->as('patients.')
            ->group(function (): void {

                // Карта конкретного пациента
                Route::prefix('{patient:id}')
                    ->group(function (): void {
                        Route::get(
                            '/',
                            [MedicalCardController::class, 'index']
                        )->name('medical_card');

                        Route::post(
                            '/',
                            [MedicalCardController::class, 'update']
                        )->name('medical_card.update');

                        // Anamneses
                        Route::post(
                            '/anamneses/store',
                            [AnamnesisController::class, 'store']
                        )->name('anamneses.store');

                        Route::get(
                            '/anamneses/{anamnesis:id}',
                            [AnamnesisController::class, 'show']
                        )->name('anamneses.show');

                        // Документы
                        Route::post(
                            '/documents/store',
                            [DocumentController::class, 'upload']
                        )->name('documents.upload');

                        Route::get(
                            '/documents/{document:id}',
                            [DocumentController::class, 'download']
                        )->name('documents.download');

                        Route::post(
                            '/documents/{document:id}/delete',
                            [DocumentController::class, 'delete']
                        )->name('documents.delete');

                        // Эпикризы
                        Route::post(
                            '/epicrisis/store',
                            [EpicrisisController::class, 'store']
                        )->name('epicrisis.store');

                        Route::get(
                            '/epicrisis/{epicrisis:id}',
                            [EpicrisisController::class, 'show']
                        )->name('epicrisis.show');

                        Route::post(
                            '/epicrisis/{epicrisis:id}/delete',
                            [EpicrisisController::class, 'delete']
                        )->name('epicrisis.delete');

                        Route::get(
                            '/epicrisis/{epicrisis:id}/edit',
                            [EpicrisisController::class, 'edit']
                        )->name('epicrisis.edit');

                        Route::post(
                            '/epicrisis/{epicrisis:id}/update',
                            [EpicrisisController::class, 'update']
                        )->name('epicrisis.update');
                    });

                // Анализы
                Route::post(
                    '/researches/{patient:id}/store',
                    [ResearchController::class, 'store']
                )->name('researches.store');

                Route::post(
                    '/researches/{patient:id}/{labResearch:id}/update',
                    [ResearchController::class, 'update']
                )->name('researches.update');

                Route::post(
                    '/researches/{labResearch:id}/delete',
                    [ResearchController::class, 'delete']
                )->name('researches.delete');

                Route::get(
                    '/researches/{labResearch:id}/patient/{patient:id}/print',
                    [ResearchController::class, 'print']
                )
                    ->withoutScopedBindings()
                    ->name('researches.print');

                Route::post(
                    '/researches/{labResearch:id}/show',
                    [ResearchController::class, 'markAsRead']
                )->name('researches.show');

                // Исследования
                Route::prefix('instrumentals')
                    ->group(function (): void {
                        Route::post(
                            '/{patient}/store',
                            [InstrumentalResearchController::class, 'store']
                        )->name('instrumentals.store');

                        Route::post(
                            '/{instrumentalResearch}/update',
                            [
                                InstrumentalResearchController::class,
                                'update',
                            ]
                        )->name('instrumentals.update');

                        Route::post(
                            '/{patient}/{instrumentalResearch}/result',
                            [
                                InstrumentalResearchController::class,
                                'result',
                            ]
                        )->name('instrumentals.result');

                        Route::post(
                            '/{patient}/{instrumentalResearch}/delete',
                            [
                                InstrumentalResearchController::class,
                                'destroy',
                            ]
                        )->name('instrumentals.delete');

                        Route::get(
                            '/{patient}/{instrumentalResearch}/view',
                            [
                                InstrumentalResearchController::class,
                                'viewed',
                            ]
                        )->name('instrumentals.view');

                        Route::get(
                            '/{patient}/{instrumentalResearch}/media/{media}',
                            [
                                InstrumentalResearchController::class,
                                'viewMedia',
                            ]
                        )
                            ->scopeBindings()
                            ->name('instrumentals.media.view');
                    });

                // Нейросеть
                Route::post(
                    '/analyze-anamnesis',
                    [
                        \App\Http\Controllers\Doctors\NeuralNetworkController::class,
                        'analyzeAnamnesis',
                    ]
                )->name('analyze.anamnesis');

                // -------------------- Tests --------------------
                Route::get(
                    '/tests/list',
                    [TestsController::class, 'listTests']
                )->name('tests.list');

                Route::get(
                    '/{patient}/tests/assignments',
                    [TestsController::class, 'listAssignments']
                )->name('tests.assignments');

                Route::post(
                    '/{patient}/tests/assign',
                    [TestsController::class, 'assignTest']
                )->name('tests.assign');

                // Управление сессиями тестов
                Route::post(
                    '/sessions/{session}/cancel',
                    [TestsController::class, 'cancelSession']
                )->name('sessions.cancel');

                Route::get(
                    '/sessions/{session}/result',
                    [TestsController::class, 'sessionResult']
                )->name('sessions.result');

                Route::get(
                    '/sessions/{session}/results',
                    [TestsController::class, 'resultsPage']
                )->name('sessions.results.page');

                Route::get(
                    '/sessions/{session}',
                    [TestsController::class, 'show']
                )->name('sessions.show');

                Route::post(
                    '/responses/{response}/code',
                    [TestsController::class, 'saveResponseCode']
                )->name('responses.code');

                Route::post(
                    '/responses/{response}/rubric',
                    [TestsController::class, 'saveRubricScore']
                )->name('responses.rubric');

                Route::post(
                    '/sessions/{session}/finish',
                    [TestsController::class, 'finish']
                )->name('sessions.finish');

                Route::post(
                    '/sessions/{session}/submit',
                    [TestsController::class, 'submit']
                )->name('sessions.session.submit');

                Route::post(
                    '/sessions/{session}/restart',
                    [TestsController::class, 'restart']
                )->name('sessions.restart');

                // Тестирование пациента по токену
                Route::get(
                    '/testing/session/{token}',
                    [PatientTestingController::class, 'form']
                )->name('session.form');

                // Запуск теста врачом
                Route::post(
                    '/tests/{assignment}/start',
                    [TestSessionController::class, 'start']
                )->name('tests.start');

                // Старый unlock по токену
                Route::post(
                    '/ts/{token?}/unlock',
                    [TestSessionController::class, 'unlock']
                )->name('ts.unlock');

                // Контакты пациента
                Route::prefix('contacts')
                    ->group(function (): void {
                        Route::post(
                            '/',
                            [ContactController::class, 'store']
                        )->name('contacts.store');

                        Route::put(
                            '/{contact}',
                            [ContactController::class, 'update']
                        )->name('contacts.update');

                        Route::delete(
                            '/{contact}',
                            [ContactController::class, 'destroy']
                        )->name('contacts.destroy');
                    });
            });

        // -------------------- Tests panel --------------------
        Route::prefix('tests')
            ->as('tests.')
            ->group(function (): void {
                Route::get(
                    '/',
                    [TestPanelController::class, 'index']
                )->name('index');

                Route::get(
                    '/mine',
                    [TestPanelController::class, 'mine']
                )->name('mine');

                Route::post(
                    '/store/images',
                    [TestPanelController::class, 'storeImageTest']
                )->name('store-image-test');

                Route::post(
                    '/store/questionnaire',
                    [TestPanelController::class, 'storeQuestionnaireTest']
                )->name('store-questionnaire-test');

                Route::post(
                    '/store/cardsort',
                    [TestPanelController::class, 'storeSortTest']
                )->name('store-card-sort-test');

                Route::post(
                    '/delete/{test}',
                    [TestPanelController::class, 'destroy']
                )->name('destroy');
            });

        Route::get(
            '/tests/{assignment}/pin',
            [TestSessionController::class, 'form']
        )->name('tests.pin.form');

        Route::post(
            '/tests/{assignment}/pin',
            [TestSessionController::class, 'verify']
        )->name('tests.pin.verify');

        Route::get(
            '/tests/{session}/final-pin',
            [TestSessionController::class, 'finalPinForm']
        )->name('tests.final_pin.form');

        Route::post(
            '/tests/{session}/final-pin',
            [TestSessionController::class, 'finalPinVerify']
        )->name('tests.final_pin.verify');

        // -------------------- Analyses panel --------------------
        Route::prefix('analyses')
            ->as('analyses.')
            ->group(function (): void {
                Route::get(
                    '/',
                    [AnalysesPanelController::class, 'overview']
                )->name('index');

                Route::get(
                    '/journal',
                    [AnalysesPanelController::class, 'journal']
                )->name('journal');

                Route::get(
                    '/critical',
                    [AnalysesPanelController::class, 'critical']
                )->name('critical');

                Route::get(
                    '/dynamics',
                    [AnalysesPanelController::class, 'dynamics']
                )->name('dynamics');

                /*
                 * Поиск располагается раньше маршрута печати,
                 * чтобы строки patients/parameters
                 * не воспринимались как ID.
                 */
                Route::get(
                    '/assignments/patients/search',
                    [
                        AnalysesPanelController::class,
                        'assignmentSearchPatients',
                    ]
                )->name('assignments.patients.search');

                Route::get(
                    '/assignments/parameters/search',
                    [
                        AnalysesPanelController::class,
                        'assignmentSearchParameters',
                    ]
                )->name('assignments.parameters.search');

                Route::get(
                    '/assignments',
                    [AnalysesPanelController::class, 'assignments']
                )->name('assignments');

                Route::post(
                    '/assignments',
                    [AnalysesPanelController::class, 'assignmentsStore']
                )->name('assignments.store');

                Route::get(
                    '/assignments/{labResearch}/print',
                    [
                        AnalysesPanelController::class,
                        'assignmentPrint',
                    ]
                )
                    ->whereNumber('labResearch')
                    ->name('assignments.print');
            });

        Route::get('doctors/analyses/assignments', [AnalysesPanelController::class, 'assignments'])->name('doctors.analyses.assignments');
    });

<?php

use App\Http\Controllers\Doctors\PrescriptionsController;
use App\Http\Controllers\Main\DoctorController;
use App\Http\Controllers\PrescriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\Main\IndexController::class)->name('main.index');
Route::get('/medicines', \App\Http\Controllers\Main\MedicineController::class)->name('main.medicines');
Route::get('/privacy-policy', \App\Http\Controllers\Main\MedicineController::class)->name('main.policy');
Route::get('/feedback', \App\Http\Controllers\Main\FeedbackController::class)->name('main.feedback');

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

Route::prefix('/admin/')->middleware(\App\Http\Middleware\AdminGroupMiddleware::class)->group(function () {
   Route::get('/', \App\Http\Controllers\Adminpanel\IndexController::class)->name('admin.main');

   Route::prefix('/settings/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\SettingController::class, 'index'])->name('admin.settings');
       Route::post('/save', [\App\Http\Controllers\Adminpanel\SettingController::class, 'save'])->name('admin.settings.save');
   });

   Route::prefix('/users/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\UsersController::class, 'list'])->name('admin.users');
       Route::get('/create', [\App\Http\Controllers\Adminpanel\UsersController::class, 'create'])->name('admin.users.create');
       Route::post('/store', [\App\Http\Controllers\Adminpanel\UsersController::class, 'store'])->name('admin.users.store');
       Route::get('/edit/{user_id}', [\App\Http\Controllers\Adminpanel\UsersController::class, 'edit'])->name('admin.users.edit')->where('user_id', '\d+');
       Route::post('/save/{user_id}', [\App\Http\Controllers\Adminpanel\UsersController::class, 'save'])->name('admin.users.save');
       Route::post('/delete/{user_id}', [\App\Http\Controllers\Adminpanel\UsersController::class, 'delete'])->name('admin.users.delete')->where('user_id', '\d+');
       Route::post('/delete', [\App\Http\Controllers\Adminpanel\UsersController::class, 'massDelete'])->name('admin.users.mass-delete');
       Route::post('/edit/{user_id}/password', [\App\Http\Controllers\Adminpanel\UsersController::class, 'changePassword'])->name('admin.users.edit.password');

       Route::prefix('/patients')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\PatientController::class, 'index'])->name('admin.users.patients');
           Route::match(['get', 'post'], '/create', [\App\Http\Controllers\Adminpanel\PatientController::class, 'store'])->name('admin.users.patients.new');
           Route::get('/edit/{patient_id}', [\App\Http\Controllers\Adminpanel\PatientController::class, 'edit'])->name('admin.users.patients.edit');
           Route::post('/save/{patient_id}', [\App\Http\Controllers\Adminpanel\PatientController::class, 'save'])->name('admin.users.patients.save');
           Route::post('/mass-delete', [\App\Http\Controllers\Adminpanel\PatientController::class, 'massDelete'])->name('admin.users.patients.mass-delete');
           Route::post('/delete/{patient_id}', [\App\Http\Controllers\Adminpanel\PatientController::class, 'delete'])->name('admin.users.patients.delete')->where('patient_id', '\d+');
       });

       Route::prefix('/doctors')->group(function () {
           Route::get('/', [ \App\Http\Controllers\Adminpanel\DoctorController::class, 'index'])->name('admin.users.doctors');
           Route::get('/create', [ \App\Http\Controllers\Adminpanel\DoctorController::class, 'create'])->name('admin.users.doctors.new');
           Route::post('/store', [\App\Http\Controllers\Adminpanel\DoctorController::class, 'store'])->name('admin.users.doctors.store');
           Route::get('/edit/{doctor_id}', [\App\Http\Controllers\Adminpanel\DoctorController::class, 'edit'])->name('admin.users.doctors.edit');
           Route::post('/update/{doctor_id}', [\App\Http\Controllers\Adminpanel\DoctorController::class, 'update'])->name('admin.users.doctors.update');
           Route::get('/delete/{patient_id}', [ \App\Http\Controllers\Adminpanel\DoctorController::class, 'delete'])->name('admin.users.doctors.delete');
           Route::post('/mass-delete', [\App\Http\Controllers\Adminpanel\DoctorController::class, 'massDelete'])->name('admin.users.doctors.mass-delete');
       });

       Route::prefix('/banned')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\BannedController::class, 'index'])->name('admin.users.banned');
           Route::get('/create', [\App\Http\Controllers\Adminpanel\BannedController::class, 'create'])->name('admin.users.banned.create');
           Route::post('/store', [\App\Http\Controllers\Adminpanel\BannedController::class, 'store'])->name('admin.users.banned.store');
           Route::get('/edit', [\App\Http\Controllers\Adminpanel\BannedController::class, 'edit'])->name('admin.users.banned.edit');
           Route::post('/save', [\App\Http\Controllers\Adminpanel\BannedController::class, 'update'])->name('admin.users.banned.save');
           Route::get('/delete/{banned_id}', [\App\Http\Controllers\Adminpanel\BannedController::class, 'delete'])->name('admin.users.banned.delete');
           Route::post('/delete', [\App\Http\Controllers\Adminpanel\BannedController::class, 'massDelete'])->name('admin.users.banned.mass-delete');
       });
   });

   Route::prefix('/groups/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\GroupController::class, 'index'])->name('admin.groups');
       Route::get('/create', [\App\Http\Controllers\Adminpanel\GroupController::class, 'create'])->name('admin.groups.new');
       Route::post('/store', [\App\Http\Controllers\Adminpanel\GroupController::class, 'store'])->name('admin.groups.store');
       Route::get('/edit/{group}', [\App\Http\Controllers\Adminpanel\GroupController::class, 'edit'])->name('admin.groups.edit');
       Route::post('/save/{group}', [\App\Http\Controllers\Adminpanel\GroupController::class, 'save'])->name('admin.groups.save');
       Route::post('/delete/{group_id}', [\App\Http\Controllers\Adminpanel\GroupController::class, 'delete'])->name('admin.groups.delete')->where('group_id', '\d+');
       Route::post('/delete/', [\App\Http\Controllers\Adminpanel\GroupController::class, 'massDelete'])->name('admin.groups.mass-delete');
   });

   Route::prefix('/dictionary/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'index'])->name('admin.dictionary.registration');
       Route::get('/create', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'create'])->name('admin.dictionary.registration.create');
       Route::get('/edit/{registry_id}', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'edit'])->name('admin.dictionary.registration.edit');
       Route::get('/delete/{registry_id}', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'delete'])->name('admin.dictionary.registration.delete')->where('registry_id', '\d+');
       Route::post('/delete', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'massDelete'])->name('admin.dictionary.registration.mass-delete');
       Route::post('/store', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'store'])->name('admin.dictionary.registration.store');
       Route::post('/save/{registry_id}', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'save'])->name('admin.dictionary.registration.save');

       Route::get('/check-availability', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'checkAvailability'])->name('appointments.check');

       Route::prefix('/clinics/')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'index'])->name('admin.dictionary.clinics');
           Route::get('/create', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'create'])->name('admin.dictionary.clinics.create');
           Route::post('/store', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'create'])->name('admin.dictionary.clinics.new');
           Route::get('/delete/{clinicId}', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'delete'])->name('admin.dictionary.clinics.delete');
           Route::post('/mass-delete', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'mass-delete'])->name('admin.dictionary.clinics.mass-delete');
           Route::get('/edit/{clinic}', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'edit'])->name('admin.dictionary.clinics.edit');
           Route::post('/save/{clinic}', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'save'])->name('admin.dictionary.clinics.save');
       });

       Route::prefix('/drugs/')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\DrugController::class, 'index'])->name('admin.dictionary.drugs');
           Route::match(['get', 'post'], '/create', [\App\Http\Controllers\Adminpanel\DrugController::class, 'store'])->name('admin.dictionary.drugs.new');
           Route::post('/delete/mass', [\App\Http\Controllers\Adminpanel\DrugController::class, 'massDelete'])->name('admin.dictionary.drugs.delete.mass');
           Route::post('/delete/{drugID}', [\App\Http\Controllers\Adminpanel\DrugController::class, 'delete'])->name('admin.dictionary.drugs.delete');
           Route::match(['get', 'post'], 'edit/{drug}', [\App\Http\Controllers\Adminpanel\DrugController::class, 'edit'])->name('admin.dictionary.drugs.edit');
           Route::post('/save/{drug}', [\App\Http\Controllers\Adminpanel\DrugController::class, 'save'])->name('admin.dictionary.drugs.save');
       });

       Route::prefix('/diagnoses/')->group(function () {
          Route::get('/', [\App\Http\Controllers\Adminpanel\DiagnoseController::class, 'index'])->name('admin.dictionary.diagnoses');
          Route::match(['get', 'post'], '/create', [])->name('admin.dictionary.diagnoses.new');
          Route::get('/delete/{diagnoseId}', [\App\Http\Controllers\Adminpanel\DiagnoseController::class, 'delete'])->name('admin.dictionary.diagnoses.delete');
          Route::get('/mass-delete', [\App\Http\Controllers\Adminpanel\DiagnoseController::class, 'mass-delete'])->name('admin.dictionary.diagnoses.delete.mass');
          Route::get('/edit/{diagnose}', [\App\Http\Controllers\Adminpanel\DiagnoseController::class, 'edit'])->name('admin.dictionary.diagnoses.edit');
          Route::post('/edit/{diagnose}', [\App\Http\Controllers\Adminpanel\DiagnoseController::class, 'save'])->name('admin.dictionary.diagnoses.save');
       });
   });

   Route::get('/feedbacks', \App\Http\Controllers\Adminpanel\IndexController::class)->name('admin.feedbacks');

   Route::prefix('/blog/')->group(function () {
       Route::prefix('/categories')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'list'])->name('admin.blog.categories');
           Route::get('/edit/{category_id}', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'edit'])->name('admin.blog.categories.edit');
           Route::post('/save/{category_id}', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'save'])->name('admin.blog.categories.save');
           Route::get('/new', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'create'])->name('admin.blog.categories.new');
           Route::post('/store', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'store'])->name('admin.blog.categories.create');
           Route::post('/delete/{category_id}', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'delete'])->name('admin.blog.categories.delete');
           Route::post('/delete', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'massDelete'])->name('admin.blog.categories.mass-delete');
       });

       Route::prefix('/topics')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\TopicController::class, 'list'])->name('admin.blog.topics');
           Route::get('/edit/{category_id}', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'edit'])->name('admin.blog.topics.edit');
           Route::post('/save/{category_id}', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'save'])->name('admin.blog.topics.save');
           Route::get('/new', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'create'])->name('admin.blog.topics.new');
           Route::post('/store', [\App\Http\Controllers\Adminpanel\CategoryController::class, 'store'])->name('admin.blog.topics.create');
       });
   });

   Route::prefix('/lawyers')->group(function () {
      Route::get('/', [\App\Http\Controllers\Adminpanel\LawyerController::class, 'index'])->name('admin.jurisprudence.lawyers');
      Route::get('/create', [\App\Http\Controllers\Adminpanel\LawyerController::class, 'create'])->name('admin.jurisprudence.lawyers.create');
      Route::post('/store', [\App\Http\Controllers\Adminpanel\LawyerController::class, 'store'])->name('admin.jurisprudence.lawyers.store');
      Route::get('/delete/{lawyer_id}', [\App\Http\Controllers\Adminpanel\LawyerController::class, 'delete'])->name('admin.jurisprudence.lawyers.delete');
      Route::post('/delete/', [\App\Http\Controllers\Adminpanel\LawyerController::class, 'massDelete'])->name('admin.jurisprudence.lawyers.mass-delete');
      Route::get('/edit/{lawyer_id}', [\App\Http\Controllers\Adminpanel\LawyerController::class, 'edit'])->name('admin.jurisprudence.lawyers.edit');
      Route::post('/update/{lawyer_id}', [\App\Http\Controllers\Adminpanel\LawyerController::class, 'update'])->name('admin.jurisprudence.lawyers.update');
   });
});

Route::prefix('/doctors')->group(function () {
   Route::get('/', [\App\Http\Controllers\Doctors\IndexController::class, 'index'])->name('doctors.main');

   Route::prefix('/prescriptions')->group(function () {
       Route::get('/', [\App\Http\Controllers\Doctors\PrescriptionsController::class, 'index'])->name('doctors.prescriptions');
       Route::get('/create', [\App\Http\Controllers\Doctors\PrescriptionsController::class, 'create'])->name('doctors.prescriptions.new');
       Route::post('/store', [\App\Http\Controllers\Doctors\PrescriptionsController::class, 'store'])->name('doctors.prescriptions.store');
   });


});
Route::get('/generate-prescription', [\App\Http\Controllers\Doctors\PrescriptionsController::class, 'generatePrescription']);
Route::post('/prescriptions/print', [PrescriptionsController::class, 'print'])->name('prescriptions.print');
Route::get('/prescriptions/print/{id}', [PrescriptionsController::class, 'printFromTable'])->name('prescriptions.print.from_table');



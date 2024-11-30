<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\Main\IndexController::class)->name('main.index');
Route::get('/medicines', \App\Http\Controllers\Main\MedicineController::class)->name('main.medicines');
Route::get('/privacy-policy', \App\Http\Controllers\Main\MedicineController::class)->name('main.policy');
Route::get('/feedback', \App\Http\Controllers\Main\FeedbackController::class)->name('main.feedback');

Route::prefix('/clinics')->group(function () {
    Route::get('/', [\App\Http\Controllers\Main\ClinicController::class, 'list'])->name('main.clinics');
    Route::get('/{clinic_id}', [\App\Http\Controllers\Main\ClinicController::class, 'index'])->name('main.clinics.form');
    Route::post('/feedback/{clinic_id}', [\App\Http\Controllers\Main\ClinicController::class, 'feedback'])->name('main.clinics.form.feedback-create');
    Route::get('/filter/{city_name}', [\App\Http\Controllers\Main\ClinicController::class, 'list'])->name('main.clinics.filters.city');
});

Route::prefix('/doctors')->group(function () {
    Route::get('/{clinic_id}', [\App\Http\Controllers\Main\DoctorController::class, 'index'])->name('main.doctors.form');
    Route::post('/feedback/{doctor_id}', [\App\Http\Controllers\Main\DoctorController::class, 'feedback'])->name('main.doctors.form.feedback-create');
});

Route::prefix('/blog')->group(function () {
    Route::get('/', [\App\Http\Controllers\Main\BlogController::class, 'list'])->name('main.blog');
    Route::prefix('/category')->group(function () {
        Route::get('/{category_id}', [\App\Http\Controllers\Main\BlogController::class, 'list'])->name('main.blog.category');
    });
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
       Route::get('/edit/{user_id}', [\App\Http\Controllers\Adminpanel\UsersController::class, 'edit'])->name('admin.user_edit');
       Route::post('/edit/{user_id}', [\App\Http\Controllers\Adminpanel\UsersController::class, 'save'])->name('admin.user_changes_save');
       Route::post('/delete/{user_id}', [\App\Http\Controllers\Adminpanel\UsersController::class, 'delete'])->name('admin.user_delete');

       Route::prefix('/patients')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\PatientController::class, 'index'])->name('admin.users.patients');
           Route::match(['get', 'post'], '/create', [\App\Http\Controllers\Adminpanel\PatientController::class, 'store'])->name('admin.users.patients.new');
           Route::post('/delete', [\App\Http\Controllers\Adminpanel\PatientController::class, 'delete'])->name('admin.users.patients.delete');
       });

       Route::prefix('/doctors')->group(function () {
           Route::get('/', [ \App\Http\Controllers\Adminpanel\DoctorController::class, 'index'])->name('admin.users.doctors');
           Route::match(['get', 'post'], '/create', [ \App\Http\Controllers\Adminpanel\DoctorController::class, 'store'])->name('admin.users.doctors.new');
           Route::get('/delete', [ \App\Http\Controllers\Adminpanel\DoctorController::class, 'delete'])->name('admin.users.doctors.delete');
           Route::get('/edit/{doctor_id}', [\App\Http\Controllers\Adminpanel\DoctorController::class, 'edit'])->name('admin.users.doctors.edit');
           Route::post('/save/{doctor_id}', [\App\Http\Controllers\Adminpanel\DoctorController::class, 'save'])->name('admin.users.doctors.save');
       });

       Route::prefix('/banned')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\BannedController::class, 'index'])->name('admin.users.banned');
       });
   });

   Route::prefix('/groups/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\GroupController::class, 'index'])->name('admin.groups');
       Route::get('/create', [\App\Http\Controllers\Adminpanel\GroupController::class, 'create'])->name('admin.groups.new');
       Route::post('/store', [\App\Http\Controllers\Adminpanel\GroupController::class, 'create'])->name('admin.groups.store');
       Route::get('/edit/{group}', [\App\Http\Controllers\Adminpanel\GroupController::class, 'edit'])->name('admin.groups.edit');
       Route::post('/save/{group}', [\App\Http\Controllers\Adminpanel\GroupController::class, 'save'])->name('admin.groups.save');
   });

   Route::prefix('/dictionary/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'index'])->name('admin.dictionary');

       Route::prefix('/clinics/')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'index'])->name('admin.dictionary.clinics');
           Route::match(['get', 'post'], '/create', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'store'])->name('admin.dictionary.clinics.new');
           Route::get('/delete', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'store'])->name('admin.dictionary.clinics.delete');
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
          Route::get('/delete', [])->name('admin.dictionary.diagnoses.delete');
          Route::get('/edit/{diagnose}', [\App\Http\Controllers\Adminpanel\DiagnoseController::class, 'edit'])->name('admin.dictionary.diagnoses.edit');
          Route::post('/edit/{diagnose}', [\App\Http\Controllers\Adminpanel\DiagnoseController::class, 'save'])->name('admin.dictionary.diagnoses.save');
       });
   });

   Route::get('/feedbacks', \App\Http\Controllers\Adminpanel\IndexController::class)->name('admin.feedbacks');

   Route::prefix('/blog/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\BlogController::class, 'listCategories'])->name('admin.blog.categories');
       Route::get('/edit/{category_id}', [\App\Http\Controllers\Adminpanel\BlogController::class, 'edit'])->name('admin.blog.categories.edit');
       Route::post('/save/{category_id}', [\App\Http\Controllers\Adminpanel\BlogController::class, 'save'])->name('admin.blog.categories.save');
       Route::get('/new', [\App\Http\Controllers\Adminpanel\BlogController::class, 'create'])->name('admin.blog.categories.new');
       Route::post('/store', [\App\Http\Controllers\Adminpanel\BlogController::class, 'store'])->name('admin.blog.categories.create');
   });
});

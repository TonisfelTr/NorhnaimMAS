<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\Main\IndexController::class)->name('main.index');
Route::get('/clinics', [\App\Http\Controllers\Main\ClinicController::class, 'index'])->name('main.clinics');
Route::get('/medicines', \App\Http\Controllers\Main\MedicineController::class)->name('main.medicines');
Route::get('/privacy-policy', \App\Http\Controllers\Main\MedicineController::class)->name('main.policy');
Route::get('/feedback', \App\Http\Controllers\Main\FeedbackController::class)->name('main.feedback');

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
       });

       Route::prefix('/banned')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\BannedController::class, 'index'])->name('admin.users.banned');
       });
   });

   Route::prefix('/groups/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\GroupController::class, 'index'])->name('admin.groups');
       Route::get('/create', [\App\Http\Controllers\Adminpanel\GroupController::class, 'index'])->name('admin.groups.new');
       Route::get('/edit/{group}', [\App\Http\Controllers\Adminpanel\GroupController::class, 'index'])->name('admin.groups.edit');
   });

   Route::prefix('/dictionary/')->group(function () {
       Route::get('/', [\App\Http\Controllers\Adminpanel\RegistryRecordController::class, 'index'])->name('admin.dictionary');

       Route::prefix('/clinics/')->group(function () {
           Route::get('/', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'index'])->name('admin.dictionary.clinics');
           Route::match(['get', 'post'], '/create', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'store'])->name('admin.dictionary.clinics.new');
           Route::get('/delete', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'store'])->name('admin.dictionary.clinics.delete');
           Route::match(['get', 'post'], 'edit/{clinic}', [\App\Http\Controllers\Adminpanel\ClinicController::class, 'edit'])->name('admin.dictionary.clinics.edit');
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
});

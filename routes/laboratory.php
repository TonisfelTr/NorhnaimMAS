<?php
use App\Http\Controllers\Laboratory\AuthController;
use App\Http\Controllers\Laboratory\WorkspaceController;
use App\Http\Middleware\EnsureLaboratoryAccess;
use Illuminate\Support\Facades\Route;

// Include outside the doctors route group.
Route::prefix('laboratory')->as('laboratory.')->group(function(){
    Route::middleware('guest')->group(function(){
        Route::get('/login',[AuthController::class,'create'])->name('login');
        Route::post('/login',[AuthController::class,'store'])->middleware('throttle:5,1')->name('login.submit');
    });
    Route::middleware(['auth',EnsureLaboratoryAccess::class])->group(function(){
        Route::get('/',[WorkspaceController::class,'index'])->name('index');
        Route::get('/researches/fragment', \App\Http\Controllers\Laboratory\RealtimeFragmentController::class)
            ->middleware('throttle:120,1')
            ->name('researches.fragment');
        Route::post('/broadcasting/auth', static function (\Illuminate\Http\Request $request) {
            return \Illuminate\Support\Facades\Broadcast::auth($request);
        })->middleware('throttle:120,1')->name('broadcasting.auth');
        Route::get('/researches/{research}',[WorkspaceController::class,'edit'])->whereNumber('research')->name('researches.edit');
        Route::put('/researches/{research}',[WorkspaceController::class,'update'])->whereNumber('research')->middleware('throttle:30,1')->name('researches.update');
        Route::post('/logout',[AuthController::class,'destroy'])->name('logout');
    });
});

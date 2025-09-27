<?php

use App\Http\Controllers\Dashboard\AreaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\AnnountcementController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DeviceController;
use App\Http\Controllers\Dashboard\DeviceFaqController;
use App\Http\Controllers\Dashboard\MemberController;
use App\Http\Controllers\Dashboard\OwnerController;
use App\Http\Controllers\Dashboard\TeknisiController;
use App\Http\Controllers\Dashboard\WebConfigController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// AUTH
Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
});

Route::group(['middleware' => 'auth:web'], function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('dashboard.admin');
    Route::get('/teknisi', [DashboardController::class, 'index'])->name('dashboard.teknisi');
    Route::get('/member', [DashboardController::class, 'index'])->name('dashboard.member');

    // ONLY ADMIN ACCESS
    Route::group(['middleware' => 'web.check.role:admin'], function () {
        Route::get('/web-config', [WebConfigController::class, 'index'])->name('web-config');

        // PREFIX MASTER
        Route::group(['prefix' => 'master'], function () {
            Route::get('/area', [AreaController::class, 'index'])->name('area');
            Route::get('/owner', [OwnerController::class, 'index'])->name('owner');
            Route::get('/teknisi', [TeknisiController::class, 'index'])->name('teknisi');
            Route::get('/member', [MemberController::class, 'index'])->name('member');
            Route::get("/device", action: [DeviceController::class, 'index'])->name('device');
            Route::get("/faq", action: [DeviceFaqController::class, 'index'])->name('faq');
        });

        // PREFIX MANAGE
        Route::group(['prefix' => 'manage'], function () {
            Route::get('/announcement', [AnnountcementController::class, 'index'])->name('announcement');
        });
    });
    // ADMIN AND TEKNISI ACCESS

    // GLOBAL ACCESS
});

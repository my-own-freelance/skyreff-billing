<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\WebConfigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'guest'], function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'validateLogin']);
});

Route::group(['middleware' => 'check.auth'], function () {
    // ONLY ADMIN ACCESS
    Route::group(['middleware' => 'api.check.role:admin'], function () {
        Route::get('/custom-template/detail', [WebConfigController::class, 'detail'])->name('web-config.detail');
        Route::post('/config/create-update', [WebConfigController::class, 'saveUpdateData'])->name('web-config.update');
    });
});

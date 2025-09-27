<?php

use App\Http\Controllers\Dashboard\AreaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\AnnountcementController;
use App\Http\Controllers\Dashboard\OwnerController;
use App\Http\Controllers\Dashboard\TeknisiController;
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

        // PREFIX MASTER
        Route::group(['prefix' => 'master'], function () {
            // AREA
            Route::group(['prefix' => 'area'], function () {
                Route::get('datatable', [\App\Http\Controllers\Dashboard\AreaController::class, 'dataTable'])->name('area.datatable');
                Route::get('{id}/detail', [AreaController::class, 'getDetail'])->name('area.detail');
                Route::post('create', [AreaController::class, 'create'])->name('area.create');
                Route::post('update', [AreaController::class, 'update'])->name('area.update');
                Route::post('update-status', [AreaController::class, 'updateStatus'])->name('area.change-status');
                Route::delete('delete', [AreaController::class, 'destroy'])->name('area.destroy');
            });

            // OWNER
            Route::group(['prefix' => 'owner'], function () {
                Route::get('datatable', [OwnerController::class, 'dataTable'])->name('owner.datatable');
                Route::get('{id}/detail', [OwnerController::class, 'getDetail'])->name('owner.detail');
                Route::post('create', [OwnerController::class, 'create'])->name('owner.create');
                Route::post('update', [OwnerController::class, 'update'])->name('owner.update');
                Route::post('update-status', [OwnerController::class, 'updateStatus'])->name('owner.change-status');
                Route::post('delete', [OwnerController::class, 'destroy'])->name('owner.destroy');
            });

            // TEKNISI
            Route::group(['prefix' => 'teknisi'], function () {
                Route::get('datatable', [TeknisiController::class, 'dataTable'])->name('teknisi.datatable');
                Route::get('{id}/detail', [TeknisiController::class, 'getDetail'])->name('teknisi.detail');
                Route::post('create', [TeknisiController::class, 'create'])->name('teknisi.create');
                Route::post('update', [TeknisiController::class, 'update'])->name('teknisi.update');
                Route::post('update-status', [TeknisiController::class, 'updateStatus'])->name('teknisi.change-status');
                Route::post('delete', [TeknisiController::class, 'destroy'])->name('teknisi.destroy');
            });
        });

        // PREFIX MANAGE
        Route::group(['prefix' => 'manage'], function () {
            // ANNOUNCEMENT
            Route::group(['prefix' => 'announcement'], function () {
                Route::get('datatable', [AnnountcementController::class, 'dataTable'])->name('announcement.datatable');
                Route::get('{id}/detail', [AnnountcementController::class, 'getDetail'])->name('announcement.detail');
                Route::post('create', [AnnountcementController::class, 'create'])->name('announcement.create');
                Route::post('update', [AnnountcementController::class, 'update'])->name('announcement.update');
                Route::post('update-status', [AnnountcementController::class, 'updateStatus'])->name('announcement.change-status');
                Route::delete('delete', [AnnountcementController::class, 'destroy'])->name('announcement.destroy');
            });
        });
    });
});

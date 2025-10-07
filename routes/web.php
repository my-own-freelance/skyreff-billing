<?php

use App\Http\Controllers\Dashboard\AreaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\AnnountcementController;
use App\Http\Controllers\Dashboard\BroadcastController;
use App\Http\Controllers\Dashboard\BroadcastTemplateController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DeviceController;
use App\Http\Controllers\Dashboard\DeviceFaqController;
use App\Http\Controllers\Dashboard\InvoiceController;
use App\Http\Controllers\Dashboard\MemberController;
use App\Http\Controllers\Dashboard\MutationController;
use App\Http\Controllers\Dashboard\OwnerController;
use App\Http\Controllers\Dashboard\PlanController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\TeknisiController;
use App\Http\Controllers\Dashboard\TicketController;
use App\Http\Controllers\Dashboard\UserController;
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


Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// AUTH
Route::group(['middleware' => 'guest'], function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
});

Route::group(['middleware' => 'auth:web'], function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('dashboard.admin');
    Route::get('/teknisi', [DashboardController::class, 'index'])->name('dashboard.teknisi');
    Route::get('/member', [DashboardController::class, 'index'])->name('dashboard.member');
    Route::get("/account", [UserController::class, "account"])->name("user.account");
    Route::get('/manage/ticket', [TicketController::class, 'index'])->name('ticket');
    Route::get('/master/device', action: [DeviceController::class, 'index'])->name('device');
    Route::get("/master/device/{id}/spesific", [DeviceController::class, "getDetailSpesific"])->name("device.detail-spesific");

    // ONLY ADMIN ACCESS
    Route::group(['middleware' => 'web.check.role:admin'], function () {
        Route::get('/web-config', [WebConfigController::class, 'index'])->name('web-config');

        // PREFIX MASTER
        Route::group(['prefix' => 'master'], function () {
            Route::get('/area', [AreaController::class, 'index'])->name('area');
            Route::get('/owner', [OwnerController::class, 'index'])->name('owner');
            Route::get('/teknisi', [TeknisiController::class, 'index'])->name('teknisi');
            Route::get('/member', [MemberController::class, 'index'])->name('member');
            Route::get('/plan', action: [PlanController::class, 'index'])->name('plan');
        });

        // PREFIX MANAGE
        Route::group(['prefix' => 'manage'], function () {
            Route::get('/announcement', [AnnountcementController::class, 'index'])->name('announcement');
            Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast');
            Route::get('/broadcast-template', [BroadcastTemplateController::class, 'index'])->name('broadcast-template');
        });
    });
    // ADMIN AND TEKNISI
    Route::group(['middleware' => 'web.check.role:admin,teknisi'], function () {
        // PREFIX MASTER
        Route::group(['prefix' => 'master'], function () {
            Route::get('/faq', action: [DeviceFaqController::class, 'index'])->name('faq');
        });

        // TRANSACTION
        Route::group(['prefix' => 'transaction'], function () {
            Route::get("/mutation", [MutationController::class, 'index'])->name('mutation');
        });
    });

    // ADMIN AND MEMBER
    Route::group(['middleware' => 'web.check.role:admin,member'], function () {
        // PREFIX MANAGE
        Route::group(['prefix' => 'manage'], function () {
            Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription');
        });

        // TRANSACTION
        Route::group(['prefix' => 'transaction'], function () {
            Route::get("/invoice", [InvoiceController::class, 'index'])->name('invoice');
            Route::get('/invoice/print/{id}', [InvoiceController::class, 'print'])->name('invoice.print');
        });
    });

    // ONLY TEKNISI
    Route::group(["middleware" => "web.check.role:teknisi"], function () {
        Route::group(['prefix' => 'transaction'], function () {
            Route::get("/commission/request-wd", [MutationController::class, "requestWithdraw"])->name("commission.request-wd");
        });
    });
});

<?php

use App\Http\Controllers\Dashboard\AreaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\AnnountcementController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DeviceController;
use App\Http\Controllers\Dashboard\DeviceFaqController;
use App\Http\Controllers\Dashboard\InvoiceController;
use App\Http\Controllers\Dashboard\MemberController;
use App\Http\Controllers\Dashboard\OwnerController;
use App\Http\Controllers\Dashboard\PlanController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\TeknisiController;
use App\Http\Controllers\Dashboard\TicketController;
use App\Http\Controllers\Dashboard\UserController;
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
    Route::get("/account/detail", [UserController::class, "getDetailAccount"])->name("user.detail-account");
    Route::post("/account/update", [UserController::class, "updateAccountReseller"])->name("user.update-account");

    // ONLY ADMIN ACCESS
    Route::group(['middleware' => 'api.check.role:admin'], function () {
        Route::get("/statistic-chart", [DashboardController::class, "getStatisticChart"])->name("statistic-chart");
        Route::get('/custom-template/detail', [WebConfigController::class, 'detail'])->name('web-config.detail');
        Route::post('/config/create-update', [WebConfigController::class, 'saveUpdateData'])->name('web-config.update');

        // PREFIX MASTER
        Route::group(['prefix' => 'master'], function () {
            // AREA
            Route::group(['prefix' => 'area'], function () {
                Route::get('datatable', [AreaController::class, 'dataTable'])->name('area.datatable');
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
                Route::delete('delete', [OwnerController::class, 'destroy'])->name('owner.destroy');
            });

            // TEKNISI
            Route::group(['prefix' => 'teknisi'], function () {
                Route::get('datatable', [TeknisiController::class, 'dataTable'])->name('teknisi.datatable');
                Route::get('{id}/detail', [TeknisiController::class, 'getDetail'])->name('teknisi.detail');
                Route::post('create', [TeknisiController::class, 'create'])->name('teknisi.create');
                Route::post('update', [TeknisiController::class, 'update'])->name('teknisi.update');
                Route::post('update-status', [TeknisiController::class, 'updateStatus'])->name('teknisi.change-status');
                Route::delete('delete', [TeknisiController::class, 'destroy'])->name('teknisi.destroy');
            });

            // MEMBER
            Route::group(['prefix' => 'member'], function () {
                Route::get('datatable', [MemberController::class, 'dataTable'])->name('member.datatable');
                Route::get('{id}/detail', [MemberController::class, 'getDetail'])->name('member.detail');
                Route::post('create', [MemberController::class, 'create'])->name('member.create');
                Route::post('update', [MemberController::class, 'update'])->name('member.update');
                Route::post('update-status', [MemberController::class, 'updateStatus'])->name('member.change-status');
                Route::delete('delete', [MemberController::class, 'destroy'])->name('member.destroy');
            });

            // DEVICE
            Route::group(['prefix' => 'device'], function () {
                Route::get('datatable', [DeviceController::class, 'dataTable'])->name('device.datatable');
                Route::get('{id}/detail', [DeviceController::class, 'getDetail'])->name('device.detail');
                Route::post('create', [DeviceController::class, 'create'])->name('device.create');
                Route::post('update', [DeviceController::class, 'update'])->name('device.update');
                Route::post('update-status', [DeviceController::class, 'updateStatus'])->name('device.change-status');
                Route::delete('delete', [DeviceController::class, 'destroy'])->name('device.destroy');
            });

            // DEVICE-FAQ
            Route::group(['prefix' => 'faq'], function () {
                Route::get('datatable', [DeviceFaqController::class, 'dataTable'])->name('faq.datatable');
                Route::get('{id}/detail', [DeviceFaqController::class, 'getDetail'])->name('faq.detail');
                Route::post('create', [DeviceFaqController::class, 'create'])->name('faq.create');
                Route::post('update', [DeviceFaqController::class, 'update'])->name('faq.update');
                Route::post('update-status', [DeviceFaqController::class, 'updateStatus'])->name('faq.change-status');
                Route::delete('delete', [DeviceFaqController::class, 'destroy'])->name('faq.destroy');
            });

            // PLAN
            Route::group(['prefix' => 'plan'], function () {
                Route::get('datatable', [PlanController::class, 'dataTable'])->name('plan.datatable');
                Route::get('{id}/detail', [PlanController::class, 'getDetail'])->name('plan.detail');
                Route::post('create', [PlanController::class, 'create'])->name('plan.create');
                Route::post('update', [PlanController::class, 'update'])->name('plan.update');
                Route::post('update-status', [PlanController::class, 'updateStatus'])->name('plan.change-status');
                Route::delete('delete', [PlanController::class, 'destroy'])->name('plan.destroy');
            });
        });

        // PREFIX MANAGE
        Route::group(['prefix' => 'manage'], function () {
            // SUBSCRIPTION
            Route::group(['prefix' => 'subscription'], function () {
                Route::get('datatable', [SubscriptionController::class, 'dataTable'])->name('subscription.datatable');
                Route::post('create', [SubscriptionController::class, 'create'])->name('subscription.create');
                Route::post('update', [SubscriptionController::class, 'update'])->name('subscription.update');
                Route::post('update-status', [SubscriptionController::class, 'updateStatus'])->name('subscription.change-status');
                Route::post('generate-invoice', [SubscriptionController::class, 'generateInvoice'])->name('subscription.generate-invoice');
                Route::get('{id}/detail', [SubscriptionController::class, 'getDetail'])->name('subscription.detail');
                Route::get('/{id}/devices', [SubscriptionController::class, 'subscriptionDevices'])->name('subscription.device');
                Route::post('/{id}/devices', [SubscriptionController::class, 'addDevice'])->name('subscription.add-device');
                Route::delete('/{id}/devices/{deviceId}', [SubscriptionController::class, 'removeDevice'])->name('subscription.remove-device');
            });

            // ANNOUNCEMENT
            Route::group(['prefix' => 'announcement'], function () {
                Route::get('datatable', [AnnountcementController::class, 'dataTable'])->name('announcement.datatable');
                Route::get('{id}/detail', [AnnountcementController::class, 'getDetail'])->name('announcement.detail');
                Route::post('create', [AnnountcementController::class, 'create'])->name('announcement.create');
                Route::post('update', [AnnountcementController::class, 'update'])->name('announcement.update');
                Route::post('update-status', [AnnountcementController::class, 'updateStatus'])->name('announcement.change-status');
                Route::delete('delete', [AnnountcementController::class, 'destroy'])->name('announcement.destroy');
            });

            // TICKET
            Route::group(['prefix' => 'ticket'], function () {
                Route::get('datatable', [TicketController::class, 'dataTable'])->name('ticket.datatable');
                Route::get('{id}/detail', [TicketController::class, 'getDetail'])->name('ticket.detail');
                Route::post('create', [TicketController::class, 'create'])->name('ticket.create');
                Route::post('update', [TicketController::class, 'update'])->name('ticket.update');
                Route::post('update-status', [TicketController::class, 'updateStatus'])->name('ticket.change-status');
                Route::delete('delete', [TicketController::class, 'destroy'])->name('ticket.destroy');
            });
        });

        // PREFIX TRANSACTION
        Route::group(['prefix' => 'transaction'], function () {
            // INVOICE
            Route::group(['prefix' => 'invoice'], function () {
                Route::get('datatable', [InvoiceController::class, 'dataTable'])->name('invoice.datatable');
                Route::get('{id}/detail', [InvoiceController::class, 'getDetail'])->name('invoice.detail');
                Route::post('update-status', [InvoiceController::class, 'updateStatus'])->name('invoice.change-status');
            });
        });
    });
});

<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
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

Route::get("/logout", [AuthController::class, "logout"])->name("logout");

// AUTH
Route::group(["middleware" => "guest"], function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
});

Route::group(["middleware" => "auth:web"], function () {
    Route::get("/admin", [DashboardController::class, "index"])->name("dashboard.admin");
    Route::get("/teknisi", [DashboardController::class, "index"])->name("dashboard.teknisi");
    Route::get("/member", [DashboardController::class, "index"])->name("dashboard.member");
});

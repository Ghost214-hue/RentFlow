<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Auth\SetupPasswordController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\CsrfCookieController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\HouseController;
use App\Http\Controllers\Api\V1\RenterController as ApiRenterController;
use App\Http\Controllers\Api\V1\BillController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\MaintenanceController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\TerminationController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\CaretakerController;
use App\Http\Controllers\Api\V1\CaretakerPermissionController;
use App\Http\Controllers\Api\V1\EmailLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->name('api.v1.')
    ->middleware(['web', 'sanctum', 'throttle:api'])
    ->group(function () {
        Route::get('/csrf-cookie', [CsrfCookieController::class, 'show'])->name('csrf.cookie');
        Route::post('/login', [LoginController::class, 'store'])->name('login');
        Route::post('/logout', [LogoutController::class, 'destroy'])->middleware('auth:sanctum')->name('logout');
        Route::get('/profiles', [ProfileController::class, 'index'])->middleware('auth:sanctum')->name('profiles.index');
        Route::post('/profiles/select', [ProfileController::class, 'select'])->middleware('auth:sanctum')->name('profiles.select');
        Route::get('/user', [ProfileController::class, 'me'])->middleware('auth:sanctum')->name('user');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
        Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.reset');
        Route::post('/setup-password', [SetupPasswordController::class, 'store'])->name('setup.password');

        Route::middleware(['auth:sanctum', 'profile:owner'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'owner'])->name('dashboard');
            Route::apiResource('/properties', PropertyController::class);
            Route::apiResource('/houses', HouseController::class);
            Route::apiResource('/renters', ApiRenterController::class);
            Route::apiResource('/bills', BillController::class);
            Route::post('/bills/generate', [BillController::class, 'generate'])->name('bills.generate');
            Route::apiResource('/payments', PaymentController::class);
            Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
            Route::apiResource('/complaints', ComplaintController::class);
            Route::apiResource('/maintenance', MaintenanceController::class);
            Route::apiResource('/documents', DocumentController::class);
            Route::apiResource('/terminations', TerminationController::class);
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::apiResource('/caretakers', CaretakerController::class);
            Route::apiResource('/caretaker-permissions', CaretakerPermissionController::class);
            Route::get('/email-logs', [EmailLogController::class, 'index'])->name('email-logs.index');
        });

        Route::middleware(['auth:sanctum', 'profile:caretaker'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'caretaker'])->name('dashboard');
            Route::get('/properties', [PropertyController::class, 'assigned'])->name('properties.assigned');
            Route::get('/houses', [HouseController::class, 'assigned'])->name('houses.assigned');
            Route::get('/renters', [ApiRenterController::class, 'assigned'])->name('renters.assigned');
            Route::apiResource('/payments', PaymentController::class)->only(['index', 'store']);
            Route::apiResource('/complaints', ComplaintController::class);
            Route::apiResource('/maintenance', MaintenanceController::class);
        });

        Route::middleware(['auth:sanctum', 'profile:tenant'])->group(function () {
            Route::get('/profile', [ApiRenterController::class, 'me'])->name('renters.me');
            Route::get('/bills', [BillController::class, 'mine'])->name('bills.mine');
            Route::get('/payments', [PaymentController::class, 'mine'])->name('payments.mine');
            Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
            Route::apiResource('/complaints', ComplaintController::class);
            Route::apiResource('/maintenance', MaintenanceController::class);
            Route::apiResource('/documents', DocumentController::class);
            Route::post('/termination/request', [TerminationController::class, 'request'])->name('terminations.request');
        });
    });
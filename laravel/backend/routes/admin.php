<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OwnerController as AdminOwnerController;
use App\Http\Controllers\Admin\SupportAccessController;
use App\Http\Controllers\Admin\EmailHealthController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Platform Routes
|--------------------------------------------------------------------------
|
| These routes are isolated from the owner app. They live under /admin,
| use their own guard (admin), their own session cookie, and their own
| middleware group. Controllers are never shared with the owner app.
|
| All admin routes require two-factor authentication before any page loads.
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'admin.throttle'])
    ->group(function () {

        // Login is public under admin guard.
        Route::middleware('admin.guest')->group(function () {
            Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
            Route::post('/login', [AdminAuthController::class, 'store'])
                ->middleware('throttle:admin-login')
                ->name('login.store');
        });

        // Authenticated admin routes require 2FA.
        Route::middleware(['admin.auth', 'admin.2fa'])->group(function () {
            Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

            // Dashboard / overview.
            Route::get('/', [AdminDashboardController::class, '__invoke'])->name('dashboard');

            // Owners.
            Route::get('/owners', [AdminOwnerController::class, 'index'])->name('owners.index');
            Route::get('/owners/{owner}', [AdminOwnerController::class, 'show'])->name('owners.show');
            Route::post('/owners/{owner}/activate', [AdminOwnerController::class, 'activate'])->name('owners.activate');
            Route::post('/owners/{owner}/suspend', [AdminOwnerController::class, 'suspend'])->name('owners.suspend');
            Route::post('/owners/{owner}/reset', [AdminOwnerController::class, 'sendReset'])->name('owners.reset');

            // Support access grants.
            Route::get('/support/access', [SupportAccessController::class, 'index'])->name('support.index');
            Route::post('/support/access', [SupportAccessController::class, 'store'])->name('support.store');
            Route::delete('/support/access/{grant}', [SupportAccessController::class, 'destroy'])->name('support.destroy');

            // Email & queue health.
            Route::get('/email-health', [EmailHealthController::class, '__invoke'])->name('email.health');

            // Security.
            Route::get('/security', [SecurityController::class, '__invoke'])->name('security');

            // System.
            Route::get('/system', [SystemController::class, '__invoke'])->name('system');
        });
    });

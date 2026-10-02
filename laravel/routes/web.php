<?php

declare(strict_types=1);

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\RenterController;
use Illuminate\Support\Facades\Route;

/*
| RentFlow routes.
|
| Every authenticated route runs through the legacy JWT bridge so a user
| signed in to the old PHP app is also signed in here. That middleware is
| removed at cutover.
*/

/*
 * Login is public: the JWT bridge middleware must NOT run here, or the
 * form could never be submitted by a signed-out visitor.
 */
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['legacy.jwt'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/houses', [HouseController::class, 'index'])->name('houses.index');
    Route::post('/houses', [HouseController::class, 'store'])->name('houses.store');
    Route::put('/houses/{house}', [HouseController::class, 'update'])->name('houses.update');
    Route::delete('/houses/{house}', [HouseController::class, 'destroy'])->name('houses.destroy');

    // Renters. NOTE: the parameter is {renter}, not {tenant}, to match the
    // model name; the URL is /renters in the new app while the DB table
    // stays `tenants`.
    Route::get('/renters', [RenterController::class, 'index'])->name('renters.index');
    Route::post('/renters', [RenterController::class, 'store'])->name('renters.store');
    Route::get('/renters/{renter}', [RenterController::class, 'show'])->name('renters.show');
    Route::put('/renters/{renter}', [RenterController::class, 'update'])->name('renters.update');
    Route::delete('/renters/{renter}', [RenterController::class, 'destroy'])->name('renters.destroy');
});

<?php

declare(strict_types=1);

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CaretakerController;
use App\Http\Controllers\CaretakerDashboardController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\MaintenanceRecordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertyDocumentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\RenterDashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RenterProfileController;
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
    Route::get('/houses/{house}', [HouseController::class, 'show'])->name('houses.show');
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

    // Bills. Figures come from BillSnapshot on every surface.
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::post('/bills/generate', [BillController::class, 'generate'])->name('bills.generate');

    // Payments. All money enters through RecordPayment.
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');

    // Properties. Unit counts are recomputed from the house rows on every read.
    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');

    // The caretaker's own landing page, scoped to their assigned properties.
    // The controller aborts anyone who is not a Caretaker, so no extra `can:`
    // gate is needed here (and CaretakerPolicy::viewAny is owner-only, which
    // would have blocked the very actor this page is for).
    Route::get('/caretaker/dashboard', CaretakerDashboardController::class)
        ->name('caretaker.dashboard');

    // Caretakers — staff accounts scoped to assigned properties.
    Route::get('/caretakers', [CaretakerController::class, 'index'])->name('caretakers.index');
    Route::post('/caretakers', [CaretakerController::class, 'store'])->name('caretakers.store');
    Route::put('/caretakers/{caretaker}', [CaretakerController::class, 'update'])->name('caretakers.update');
    Route::delete('/caretakers/{caretaker}', [CaretakerController::class, 'destroy'])->name('caretakers.destroy');

    // Complaints — two-way communication. Visibility follows the recipient list.
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::post('/complaints/{complaint}/advance', [ComplaintController::class, 'advance'])->name('complaints.advance');
    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');

    // Maintenance requests — renters raise them, staff advance them.
    Route::get('/maintenance', [MaintenanceRecordController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceRecordController::class, 'store'])->name('maintenance.store');
    Route::post('/maintenance/{record}/advance', [MaintenanceRecordController::class, 'advance'])->name('maintenance.advance');
    Route::delete('/maintenance/{record}', [MaintenanceRecordController::class, 'destroy'])->name('maintenance.destroy');

    // Rules and documents. Renters read the active ones; only owners author them.
    Route::get('/documents', [PropertyDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [PropertyDocumentController::class, 'store'])->name('documents.store');
    Route::put('/documents/{document}', [PropertyDocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [PropertyDocumentController::class, 'destroy'])->name('documents.destroy');

    // Reports. Computed from the ledger at read time; there is no reports table.
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Email delivery log. Read only: the mail queue writes it.
    Route::get('/email-logs', [EmailLogController::class, 'index'])->name('email-logs.index');

    // Renter-facing pages. Each aborts unless the actor is a Renter, so an
    // owner or caretaker reaching these gets a 403 rather than empty data.
    Route::get('/renter/dashboard', RenterDashboardController::class)->name('renter.dashboard');
    Route::get('/renter/profile', [RenterProfileController::class, 'show'])->name('renter.profile.show');
    Route::put('/renter/profile', [RenterProfileController::class, 'update'])->name('renter.profile.update');
});

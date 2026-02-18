<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OpnameSessionController;
use App\Http\Controllers\OpnameEntryController;
use App\Http\Controllers\VarianceReviewController;
use App\Http\Controllers\CsvImportController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Opname Sessions
    Route::resource('opname-sessions', OpnameSessionController::class);
    Route::post('opname-sessions/{opname_session}/start', [OpnameSessionController::class, 'start'])->name('opname-sessions.start');
    Route::post('opname-sessions/{opname_session}/process', [OpnameSessionController::class, 'process'])->name('opname-sessions.process');

    // Opname Entries (nested under sessions)
    Route::post('opname-sessions/{opname_session}/entries', [OpnameEntryController::class, 'store'])->name('entries.store');
    Route::put('opname-sessions/{opname_session}/entries/{entry}', [OpnameEntryController::class, 'update'])->name('entries.update');
    Route::delete('opname-sessions/{opname_session}/entries/{entry}', [OpnameEntryController::class, 'destroy'])->name('entries.destroy');
    Route::post('opname-sessions/{opname_session}/entries/bulk', [OpnameEntryController::class, 'bulkStore'])->name('entries.bulk');

    // Variance Reviews
    Route::get('/variances', [VarianceReviewController::class, 'index'])->name('variances.index');
    Route::post('/variances/{review}/approve', [VarianceReviewController::class, 'approve'])->name('variances.approve');
    Route::post('/variances/{review}/reject', [VarianceReviewController::class, 'reject'])->name('variances.reject');

    // CSV Import
    Route::get('/import', [CsvImportController::class, 'index'])->name('import.index');
    Route::post('/import/upload', [CsvImportController::class, 'upload'])->name('import.upload');
    Route::post('/import/process', [CsvImportController::class, 'process'])->name('import.process');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});

// Webhook (no web auth — token-based)
Route::post('/api/webhook/opname', [WebhookController::class, 'receive'])->name('webhook.opname');
Route::get('/api/webhook/system-stock', [WebhookController::class, 'systemStock'])->name('webhook.system-stock');

require __DIR__.'/auth.php';

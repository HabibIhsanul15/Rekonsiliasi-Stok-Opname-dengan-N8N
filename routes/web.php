<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OpnameSessionController;
use App\Http\Controllers\ItemController;

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
    
    // Redirect old session index to import page
    Route::redirect('/opname-sessions', '/import');

    // Items CRUD
    Route::resource('items', ItemController::class);

    // Opname Sessions
    Route::get('/opname-sessions/{opnameSession}', [OpnameSessionController::class, 'show'])->name('opname-sessions.show');
    Route::post('/opname-sessions/{opnameSession}/complete', [OpnameSessionController::class, 'complete'])->name('opname-sessions.complete');
    Route::delete('/opname-sessions/{opnameSession}', [OpnameSessionController::class, 'destroy'])->name('opname-sessions.destroy');

    // Variance Reviews
    Route::get('/variances', [VarianceReviewController::class, 'index'])->name('variances.index');
    Route::post('/variances/{review}/approve', [VarianceReviewController::class, 'approve'])->name('variances.approve');
    Route::post('/variances/{review}/reject', [VarianceReviewController::class, 'reject'])->name('variances.reject');

    // CSV Import
    Route::get('/import', [CsvImportController::class, 'index'])->name('import.index');
    Route::post('/import/upload', [CsvImportController::class, 'upload'])->name('import.upload');
    Route::get('/import/preview', [CsvImportController::class, 'preview'])->name('import.preview');
    Route::post('/import/process', [CsvImportController::class, 'process'])->name('import.process');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});

// Webhook (no web auth — token-based)
Route::post('/api/webhook/opname', [WebhookController::class, 'receive'])->name('webhook.opname');
Route::get('/api/webhook/opname/{opnameSession}', [WebhookController::class, 'sessionEntries'])->name('webhook.opname.entries');
Route::get('/api/webhook/system-stock', [WebhookController::class, 'systemStock'])->name('webhook.system-stock');

require __DIR__.'/auth.php';

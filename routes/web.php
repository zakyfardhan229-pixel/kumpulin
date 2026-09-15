<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSubmissionController;
use App\Http\Controllers\Admin\AssignmentSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SubmissionStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($code = trim((string) $request->query('session', ''))) {
        return redirect()->route('student.submit', strtoupper($code));
    }

    return view('welcome');
})->name('home');

Route::get('/submit/{sessionCode}', [SubmissionController::class, 'create'])->name('student.submit');
Route::post('/submit/{sessionCode}', [SubmissionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('student.submit.store');
Route::get('/submission/{submissionCode}', [SubmissionController::class, 'success'])->name('student.success');
Route::get('/check', [SubmissionStatusController::class, 'index'])->name('student.check');
Route::get('/check/{submissionCode}', [SubmissionStatusController::class, 'show'])
    ->middleware('throttle:20,1')
    ->name('student.check.show');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Phase 7: submissions review routes.
    Route::get('/submissions', [AdminSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [AdminSubmissionController::class, 'show'])->name('submissions.show');
    Route::post('/submissions/{submission}/review', [AdminReviewController::class, 'store'])->name('submissions.review');
    Route::post('/submissions/{submission}/retry', [AdminSubmissionController::class, 'retry'])->name('submissions.retry');
    Route::get('/files/{file}', [AdminSubmissionController::class, 'file'])->name('files.show');

    Route::get('/sessions', [AssignmentSessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/create', [AssignmentSessionController::class, 'create'])->name('sessions.create');
    Route::post('/sessions', [AssignmentSessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/{session}', [AssignmentSessionController::class, 'show'])->name('sessions.show');
    Route::get('/sessions/{session}/edit', [AssignmentSessionController::class, 'edit'])->name('sessions.edit');
    Route::put('/sessions/{session}', [AssignmentSessionController::class, 'update'])->name('sessions.update');
    Route::patch('/sessions/{session}/close', [AssignmentSessionController::class, 'close'])->name('sessions.close');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

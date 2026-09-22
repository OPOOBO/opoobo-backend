<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\MiniAppController;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\DeveloperAuth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'OPOOBO Backend',
        'status' => 'running',
        'version' => '1.0.0',
    ]);
});

Route::get('/up', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// ── Developer Portal ───────────────────────────────────────────────
Route::prefix('developer')->group(function () {
    Route::get('/register', [DeveloperController::class, 'showRegister'])->name('developer.register');
    Route::post('/register', [DeveloperController::class, 'register']);
    Route::get('/login', [DeveloperController::class, 'showLogin'])->name('developer.login');
    Route::post('/login', [DeveloperController::class, 'login']);
    Route::post('/logout', [DeveloperController::class, 'logout'])->name('developer.logout');

    Route::middleware(DeveloperAuth::class)->group(function () {
        Route::get('/', [DeveloperController::class, 'dashboard'])->name('developer.dashboard');
        Route::get('/docs', function () { return view('developer.docs'); })->name('developer.docs');
        Route::get('/demo', function () { return view('developer.demo'); })->name('developer.demo');
        Route::get('/submit', [DeveloperController::class, 'showSubmit'])->name('developer.submit');
        Route::post('/submit', [DeveloperController::class, 'submit']);
        Route::get('/{module}/edit', [DeveloperController::class, 'showEdit'])->name('developer.edit');
        Route::put('/{module}', [DeveloperController::class, 'update'])->name('developer.update');
        Route::delete('/{module}', [MiniAppController::class, 'destroy'])->name('developer.withdraw');
        Route::get('/account/profile', [DeveloperController::class, 'profile'])->name('developer.profile');
        Route::put('/account/profile', [DeveloperController::class, 'updateProfile'])->name('developer.profile.update');
        Route::put('/account/password', [DeveloperController::class, 'updatePassword'])->name('developer.password.update');
    });
});

// ── Admin Dashboard ────────────────────────────────────────────────
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login']);
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::middleware(AdminAuth::class)->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
        Route::post('/approve/{module}', [AdminController::class, 'approve'])->name('admin.approve');
        Route::post('/reject/{module}', [AdminController::class, 'reject'])->name('admin.reject');
        Route::post('/feature/{module}', [AdminController::class, 'feature'])->name('admin.feature');
        Route::delete('/delete/{module}', [AdminController::class, 'destroy'])->name('admin.delete');
        Route::post('/reorder', [AdminController::class, 'reorder'])->name('admin.reorder');
        Route::post('/update/{module}', [AdminController::class, 'updateModule'])->name('admin.updateModule');
        Route::post('/preflight/{module}', [AdminController::class, 'preflight'])->name('admin.preflight');
        Route::post('/tested/{module}', [AdminController::class, 'markTested'])->name('admin.markTested');
        Route::post('/review-notes/{module}', [AdminController::class, 'updateReviewNotes'])->name('admin.updateReviewNotes');
    });
});

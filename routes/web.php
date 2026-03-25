<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(User::needsInitialSuperadmin() ? 'register' : 'login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('measurements', MeasurementController::class)->except(['edit', 'update']);
    Route::post('/measurements/predict', [MeasurementController::class, 'predict'])->name('measurements.predict');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

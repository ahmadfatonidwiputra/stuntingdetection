<?php

use App\Http\Controllers\AnakController;
use App\Http\Controllers\AntropometriController;
use App\Http\Controllers\Auth\OrangTuaRegisterController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\OrangTuaDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\VerifikasiOrangTuaController;
use Illuminate\Support\Facades\Route;

// ── Public Landing Pages ───────────────────────────
// Konten murni statis (tanpa query DB, tanpa form) & jarang berubah, jadi
// browser boleh menyimpan cache-nya sendiri selama 1 jam sebelum revalidasi.
// "private" (bukan "public") karena layout ikut menyisipkan csrf-token &
// tautan navigasi yang beda untuk user login, jadi tidak boleh dibagi ke
// cache bersama/CDN.
Route::middleware('cache.headers:private;max_age=3600;etag;must_revalidate')->group(function () {
    Route::get('/', [LandingController::class, 'home'])->name('home');
    Route::get('/tentang-stunting', [LandingController::class, 'tentangStunting'])->name('tentang-stunting');
    Route::get('/layanan', [LandingController::class, 'layanan'])->name('layanan');
});
Route::get('/kalkulator-antropometri', [LandingController::class, 'kalkulatorAntropometri'])->name('kalkulator-antropometri');

// ── Petugas Registration (public) ──────────────────
Route::middleware('guest')->group(function () {
    Route::get('/registrasi-petugas', [RegisteredUserController::class, 'createPetugas'])->name('register.petugas');
    Route::post('/registrasi-petugas', [RegisteredUserController::class, 'storePetugas']);

    // Orang Tua Registration
    Route::get('/registrasi-orang-tua', [OrangTuaRegisterController::class, 'create'])->name('register.orang-tua');
    Route::post('/registrasi-orang-tua', [OrangTuaRegisterController::class, 'store']);
});

// ── Status Pages (petugas pending/rejected/suspended) ───
Route::middleware('auth')->group(function () {
    Route::get('/status/pending', function () {
        return view('petugas.pending');
    })->name('petugas.pending');

    Route::get('/status/rejected', function () {
        $user = auth()->user();
        $reason = $user->petugasProfile?->rejection_reason;
        return view('petugas.rejected', compact('reason'));
    })->name('petugas.rejected');

    Route::get('/status/suspended', function () {
        return view('petugas.suspended');
    })->name('petugas.suspended');
});

// ── Orang Tua Status Pages ─────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/orang-tua/pending', [OrangTuaDashboardController::class, 'pending'])
        ->name('orang-tua.pending');
    Route::get('/orang-tua/ditolak', [OrangTuaDashboardController::class, 'rejected'])
        ->name('orang-tua.rejected');
});

// ── Orang Tua Dashboard ────────────────────────────
Route::middleware(['auth', 'orang.tua'])
    ->prefix('orang-tua')
    ->name('orang-tua.')
    ->group(function () {
        Route::get('/dashboard', [OrangTuaDashboardController::class, 'index'])->name('dashboard');
        Route::get('/anak/{id}', [OrangTuaDashboardController::class, 'showAnak'])->name('anak.show');
        Route::get('/antropometri', [AntropometriController::class, 'index'])->name('antropometri.index');
    });

// ── Super Admin Routes ─────────────────────────────
Route::middleware(['auth', 'verified', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/petugas', [SuperAdminController::class, 'petugasList'])->name('petugas.index');
        Route::get('/petugas/{user}', [SuperAdminController::class, 'petugasShow'])->name('petugas.show');
        Route::post('/petugas/{user}/approve', [SuperAdminController::class, 'approve'])->name('petugas.approve');
        Route::post('/petugas/{user}/reject', [SuperAdminController::class, 'reject'])->name('petugas.reject');
        Route::post('/petugas/{user}/suspend', [SuperAdminController::class, 'suspend'])->name('petugas.suspend');
        Route::post('/petugas/{user}/reactivate', [SuperAdminController::class, 'reactivate'])->name('petugas.reactivate');
        Route::delete('/petugas/{user}', [SuperAdminController::class, 'destroy'])->name('petugas.destroy');

        // CRUD Posyandu
        Route::get('/posyandu', [SuperAdminController::class, 'posyanduIndex'])->name('posyandu.index');
        Route::get('/posyandu/tambah', [SuperAdminController::class, 'posyanduCreate'])->name('posyandu.create');
        Route::post('/posyandu', [SuperAdminController::class, 'posyanduStore'])->name('posyandu.store');
        Route::get('/posyandu/{posyandu}/edit', [SuperAdminController::class, 'posyanduEdit'])->name('posyandu.edit');
        Route::put('/posyandu/{posyandu}', [SuperAdminController::class, 'posyanduUpdate'])->name('posyandu.update');
        Route::delete('/posyandu/{posyandu}', [SuperAdminController::class, 'posyanduDestroy'])->name('posyandu.destroy');

        // Manajemen Laporan
        Route::get('/laporan', [SuperAdminController::class, 'laporanIndex'])->name('laporan.index');
        Route::get('/laporan/{posyandu}', [SuperAdminController::class, 'laporanShow'])->name('laporan.show');
        Route::get('/laporan/{posyandu}/download', [SuperAdminController::class, 'laporanDownload'])->name('laporan.download');

        // Antropometri Anak (acuan Permenkes No. 2 Tahun 2020)
        Route::get('/antropometri', [AntropometriController::class, 'index'])->name('antropometri.index');
    });

// ── Petugas Dashboard & Measurements ───────────────
Route::middleware(['auth', 'verified', 'active.petugas'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/measurements/anak/{anak}', [MeasurementController::class, 'showAnak'])->name('measurements.anak.show');
    Route::get('/measurements/anak/{anak}/download', [MeasurementController::class, 'downloadAnak'])->name('measurements.anak.download');
    Route::resource('measurements', MeasurementController::class)->except(['edit', 'update']);
    Route::get('/measurements-search-anak', [MeasurementController::class, 'searchAnak'])->name('measurements.search-anak');
    Route::post('/measurements/predict', [MeasurementController::class, 'predict'])->name('measurements.predict');
    Route::post('/measurements/warmup', [MeasurementController::class, 'warmup'])->name('measurements.warmup');
    Route::post('/measurements/antropometri', [MeasurementController::class, 'antropometri'])->name('measurements.antropometri');

    // Manajemen Anak
    Route::resource('anak', AnakController::class);
    Route::get('/anak-search-orang-tua', [AnakController::class, 'searchOrangTua'])->name('anak.search-orang-tua');

    // Antropometri Anak (acuan Permenkes No. 2 Tahun 2020)
    Route::get('/antropometri', [AntropometriController::class, 'index'])->name('antropometri.index');

    // Laporan Pengukuran
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/download/bulanan', [LaporanController::class, 'downloadBulanan'])->name('laporan.download.bulanan');
    Route::get('/laporan/download/tahunan', [LaporanController::class, 'downloadTahunan'])->name('laporan.download.tahunan');

    // Verifikasi Orang Tua
    Route::get('/verifikasi-orang-tua', [VerifikasiOrangTuaController::class, 'index'])->name('verifikasi-orang-tua.index');
    Route::post('/verifikasi-orang-tua/{id}/approve', [VerifikasiOrangTuaController::class, 'approve'])->name('verifikasi-orang-tua.approve');
    Route::post('/verifikasi-orang-tua/{id}/reject', [VerifikasiOrangTuaController::class, 'reject'])->name('verifikasi-orang-tua.reject');
    Route::get('/verifikasi-orang-tua/{user}/edit', [VerifikasiOrangTuaController::class, 'edit'])->name('verifikasi-orang-tua.edit');
    Route::put('/verifikasi-orang-tua/{user}', [VerifikasiOrangTuaController::class, 'update'])->name('verifikasi-orang-tua.update');
    Route::post('/verifikasi-orang-tua/{user}/suspend', [VerifikasiOrangTuaController::class, 'suspend'])->name('verifikasi-orang-tua.suspend');
    Route::delete('/verifikasi-orang-tua/{user}', [VerifikasiOrangTuaController::class, 'destroy'])->name('verifikasi-orang-tua.destroy');
});

// ── Profile (all authenticated) ────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/orang-tua', [ProfileController::class, 'updateOrangTuaProfile'])->name('profile.orang-tua.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\OrangTuaAnak;
use App\Models\OrangTuaProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrangTuaDashboardController extends Controller
{
    /**
     * Dashboard utama orang tua.
     */
    public function index()
    {
        $user = auth()->user();
        $profile = $user->orangTuaProfile;

        $anakList = Anak::forOrangTua($user->id)
            ->with(['measurements' => fn($q) => $q->latest('measured_at')->limit(1), 'posyandu'])
            ->get()
            ->map(function ($anak) {
                $lastMeasurement = $anak->measurements->first();
                return [
                    'id'                  => $anak->id,
                    'nama'                => $anak->nama,
                    'jenis_kelamin'       => $anak->jenis_kelamin,
                    'umur'                => $anak->umur,
                    'posyandu'            => $anak->posyandu?->nama,
                    'last_measurement'    => $lastMeasurement,
                    'status'              => $lastMeasurement?->stunting_category ?? 'Belum ada data',
                ];
            });

        $totalAnak   = $anakList->count();
        $lastCheck   = Anak::forOrangTua($user->id)
            ->with(['measurements' => fn($q) => $q->latest('measured_at')])
            ->get()
            ->pluck('measurements')
            ->flatten()
            ->sortByDesc('measured_at')
            ->first();

        return view('orang-tua.dashboard', compact('user', 'profile', 'anakList', 'totalAnak', 'lastCheck'));
    }

    /**
     * Halaman pending (belum diverifikasi).
     */
    public function pending()
    {
        return view('orang-tua.pending');
    }

    /**
     * Halaman ditolak.
     */
    public function rejected()
    {
        return view('orang-tua.rejected');
    }

    /**
     * Detail tumbuh kembang anak.
     */
    public function showAnak(int $id)
    {
        $user = auth()->user();

        $anak = Anak::forOrangTua($user->id)
            ->with(['posyandu', 'petugas', 'measurements' => fn($q) => $q->with('anak')->orderBy('measured_at')])
            ->findOrFail($id);

        $measurements = $anak->measurements;

        // Data grafik
        $grafikBB = $measurements->map(fn($m) => [
            'x' => $anak->umurBulan - now()->diffInMonths($m->measured_at),
            'y' => $m->weight_kg,
            'tgl' => $m->measured_at?->format('d/m/Y'),
        ]);
        $grafikTB = $measurements->map(fn($m) => [
            'x' => $anak->umurBulan - now()->diffInMonths($m->measured_at),
            'y' => $m->height_cm,
            'tgl' => $m->measured_at?->format('d/m/Y'),
        ]);

        return view('orang-tua.anak-detail', compact('anak', 'measurements', 'grafikBB', 'grafikTB'));
    }
}

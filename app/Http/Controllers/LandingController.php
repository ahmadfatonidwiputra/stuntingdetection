<?php

namespace App\Http\Controllers;

use App\Services\AntropometriService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LandingController extends Controller
{

    public function home()
    {
        return view('landing.home');
    }

    public function tentangStunting()
    {
        return view('landing.tentang-stunting');
    }

    public function layanan()
    {
        return view('landing.layanan');
    }

    /**
     * Kalkulator publik Status Gizi Anak (BB/U, PB/U-TB/U, BB/PB-BB/TB, IMT/U)
     * berdasarkan Standar Antropometri Anak - Permenkes RI No. 2 Tahun 2020.
     * Tidak memerlukan login; tidak menyimpan data apa pun ke database.
     */
    public function kalkulatorAntropometri(Request $request)
    {
        $hasil = null;

        if ($request->filled('hitung')) {
            $validated = $request->validate([
                'jenis_kelamin' => ['required', 'in:L,P'],
                'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
                'tanggal_ukur' => ['nullable', 'date', 'after_or_equal:tanggal_lahir', 'before_or_equal:today'],
                'berat_kg' => ['required', 'numeric', 'min:1', 'max:50'],
                'tinggi_cm' => ['required', 'numeric', 'min:30', 'max:150'],
            ]);

            $lahir = Carbon::parse($validated['tanggal_lahir'])->startOfDay();
            $ukur = ! empty($validated['tanggal_ukur'])
                ? Carbon::parse($validated['tanggal_ukur'])->startOfDay()
                : Carbon::today();

            $umurBulan = min(60, max(0, $lahir->diffInDays($ukur) / AntropometriService::HARI_PER_BULAN));

            $gender = $validated['jenis_kelamin'];
            $beratKg = (float) $validated['berat_kg'];
            $tinggiCm = (float) $validated['tinggi_cm'];

            $hasil = AntropometriService::hitungLengkap($gender, $beratKg, $tinggiCm, $umurBulan);
        }

        return view('landing.kalkulator-antropometri', [
            'hasil' => $hasil,
            'kategori' => AntropometriService::KATEGORI,
            'old' => $request->only(['jenis_kelamin', 'tanggal_lahir', 'tanggal_ukur', 'berat_kg', 'tinggi_cm']),
            'tabelBoys' => AntropometriService::tabelLengkap('L'),
            'tabelGirls' => AntropometriService::tabelLengkap('P'),
            'kenaikanBb' => AntropometriService::KENAIKAN_BB_MINIMUM,
            'kenaikanBbDiatas12' => AntropometriService::KENAIKAN_BB_MINIMUM_DIATAS_12_BULAN,
        ]);
    }
}

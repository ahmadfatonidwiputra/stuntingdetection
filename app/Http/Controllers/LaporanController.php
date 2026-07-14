<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsMeasurementsCsv;
use App\Models\Measurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    use ExportsMeasurementsCsv;

    public function index()
    {
        $user = Auth::user();
        $posyanduName = $user->petugasProfile?->posyandu?->nama
            ?? $user->petugasProfile?->posyandu_name
            ?? '-';

        $currentYear = now()->year;
        $years = range($currentYear, max($currentYear - 5, 2020));

        return view('laporan.index', compact('posyanduName', 'years', 'currentYear'));
    }

    public function downloadBulanan(Request $request): StreamedResponse
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020|max:' . now()->year,
        ]);

        $user = Auth::user();
        $posyanduId = $user->petugasProfile?->posyandu_id;

        if (! $posyanduId) {
            abort(403, 'Posyandu tidak ditemukan.');
        }

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;

        $measurements = Measurement::query()
            ->with('anak')
            ->whereHas('anak', fn ($q) => $q->where('posyandu_id', $posyanduId))
            ->whereYear('measured_at', $tahun)
            ->whereMonth('measured_at', $bulan)
            ->orderBy('measured_at')
            ->get();

        $namaBulan = $this->namaBulan($bulan);
        $posyanduName = $user->petugasProfile?->posyandu?->nama
            ?? $user->petugasProfile?->posyandu_name
            ?? 'Posyandu';
        $filename = "laporan_pengukuran_{$namaBulan}_{$tahun}.csv";

        return $this->streamMeasurementsCsv($measurements, $filename, $posyanduName, "Laporan Bulan {$namaBulan} {$tahun}");
    }

    public function downloadTahunan(Request $request): StreamedResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:' . now()->year,
        ]);

        $user = Auth::user();
        $posyanduId = $user->petugasProfile?->posyandu_id;

        if (! $posyanduId) {
            abort(403, 'Posyandu tidak ditemukan.');
        }

        $tahun = (int) $request->tahun;

        $measurements = Measurement::query()
            ->with('anak')
            ->whereHas('anak', fn ($q) => $q->where('posyandu_id', $posyanduId))
            ->whereYear('measured_at', $tahun)
            ->orderBy('measured_at')
            ->get();

        $posyanduName = $user->petugasProfile?->posyandu?->nama
            ?? $user->petugasProfile?->posyandu_name
            ?? 'Posyandu';
        $filename = "laporan_pengukuran_tahun_{$tahun}.csv";

        return $this->streamMeasurementsCsv($measurements, $filename, $posyanduName, "Laporan Tahun {$tahun}");
    }

    private function namaBulan(int $bulan): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$bulan] ?? 'Bulan';
    }

}

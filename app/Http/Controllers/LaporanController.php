<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
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

        return $this->streamCsv($measurements, $filename, $posyanduName, "Laporan Bulan {$namaBulan} {$tahun}");
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

        return $this->streamCsv($measurements, $filename, $posyanduName, "Laporan Tahun {$tahun}");
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

    private function streamCsv($measurements, string $filename, string $posyanduName, string $periodLabel): StreamedResponse
    {
        return response()->streamDownload(function () use ($measurements, $posyanduName, $periodLabel) {
            $output = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header info
            fputcsv($output, ["Laporan Pengukuran Posyandu"]);
            fputcsv($output, ["Posyandu", $posyanduName]);
            fputcsv($output, ["Periode", $periodLabel]);
            fputcsv($output, ["Dicetak pada", now()->format('d/m/Y H:i')]);
            fputcsv($output, ["Total Data", $measurements->count() . " pengukuran"]);
            fputcsv($output, []);

            // Column headers
            fputcsv($output, [
                'No',
                'Tanggal Pengukuran',
                'Nama Anak',
                'NIK Anak',
                'Tanggal Lahir',
                'Jenis Kelamin',
                'Nama Ibu',
                'Nama Ayah',
                'Usia (Bulan)',
                'Tinggi Badan (cm)',
                'Berat Badan (kg)',
                'Z-Score',
                'Kategori Stunting',
                'Catatan',
            ]);

            foreach ($measurements as $i => $m) {
                $anak = $m->anak;
                $ageMonths = $anak && $anak->tanggal_lahir
                    ? (int) $anak->tanggal_lahir->diffInMonths($m->measured_at)
                    : '-';

                fputcsv($output, [
                    $i + 1,
                    $m->measured_at->format('d/m/Y'),
                    $anak?->nama ?? $m->child_name ?? '-',
                    $anak?->nik_anak ?? '-',
                    $anak?->tanggal_lahir?->format('d/m/Y') ?? '-',
                    $anak?->jenis_kelamin === 'L' ? 'Laki-laki' : ($anak?->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                    $anak?->nama_ibu ?? '-',
                    $anak?->nama_ayah ?? '-',
                    $ageMonths,
                    number_format((float) $m->height_cm, 1),
                    number_format((float) $m->weight_kg, 1),
                    number_format((float) $m->z_score, 2),
                    $m->stunting_category,
                    $m->notes ?? '',
                ]);
            }

            // Summary
            if ($measurements->count() > 0) {
                fputcsv($output, []);
                fputcsv($output, ['--- Ringkasan ---']);
                $normal = $measurements->where('stunting_category', 'Normal')->count();
                $stunting = $measurements->where('stunting_category', 'Stunting')->count();
                $sangatStunting = $measurements->where('stunting_category', 'Sangat Stunting')->count();
                fputcsv($output, ['Normal', $normal]);
                fputcsv($output, ['Stunting', $stunting]);
                fputcsv($output, ['Sangat Stunting', $sangatStunting]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

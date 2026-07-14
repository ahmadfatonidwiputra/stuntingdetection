<?php

namespace App\Http\Controllers\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsMeasurementsCsv
{
    protected function streamMeasurementsCsv($measurements, string $filename, string $posyanduName, string $periodLabel): StreamedResponse
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

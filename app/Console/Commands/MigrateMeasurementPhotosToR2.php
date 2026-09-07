<?php

namespace App\Console\Commands;

use App\Models\Measurement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateMeasurementPhotosToR2 extends Command
{
    protected $signature = 'photos:migrate-to-r2';

    protected $description = 'Upload foto pengukuran lama yang masih tersimpan di disk lokal "public" (sebelum migrasi ke Cloudflare R2) ke disk "r2", agar tidak hilang di halaman profil anak.';

    public function handle(): int
    {
        $local = Storage::disk('public');
        $r2 = Storage::disk('r2');

        $paths = Measurement::query()
            ->whereNotNull('photo_path')
            ->pluck('photo_path')
            ->merge(
                Measurement::query()->whereNotNull('pose_photo_path')->pluck('pose_photo_path')
            )
            ->unique()
            ->values();

        if ($paths->isEmpty()) {
            $this->info('Tidak ada foto pengukuran yang perlu diperiksa.');

            return self::SUCCESS;
        }

        $migrated = 0;
        $skippedMissingLocal = 0;
        $skippedAlreadyOnR2 = 0;
        $failed = 0;

        foreach ($paths as $path) {
            if ($r2->exists($path)) {
                $skippedAlreadyOnR2++;

                continue;
            }

            if (! $local->exists($path)) {
                $skippedMissingLocal++;

                continue;
            }

            try {
                $r2->put($path, $local->readStream($path));
                $migrated++;
                $this->line("Diunggah: {$path}");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Gagal mengunggah {$path}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Selesai. Diunggah: {$migrated}, sudah ada di R2: {$skippedAlreadyOnR2}, tidak ditemukan lokal: {$skippedMissingLocal}, gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

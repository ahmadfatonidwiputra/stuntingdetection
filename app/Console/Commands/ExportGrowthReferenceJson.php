<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportGrowthReferenceJson extends Command
{
    protected $signature = 'growth:export-json';

    protected $description = 'Ekspor tabel rujukan WHO (config/growth/*.php) ke aset JSON statis di public/data/growth, agar bisa dimuat & di-cache terpisah dari HTML.';

    private const CONFIG_TO_FILE = [
        'wfa' => 'wfa.json',
        'lhfa' => 'lhfa.json',
        'bmifa' => 'bmifa.json',
        'wfh' => 'wfh.json',
        'wfl' => 'wfl.json',
    ];

    public function handle(): int
    {
        $targetDir = public_path('data/growth');
        File::ensureDirectoryExists($targetDir);

        foreach (self::CONFIG_TO_FILE as $configKey => $fileName) {
            $data = config("growth.{$configKey}");

            if (! $data) {
                $this->warn("Lewati {$configKey}: config growth.{$configKey} tidak ditemukan.");
                continue;
            }

            File::put(
                "{$targetDir}/{$fileName}",
                json_encode($data, JSON_UNESCAPED_SLASHES)
            );

            $this->info("Ditulis: data/growth/{$fileName}");
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Services;

/**
 * Perhitungan dan acuan Standar Antropometri Anak
 * sesuai Peraturan Menteri Kesehatan RI No. 2 Tahun 2020.
 *
 * Data tabel (L, M, S, SD3neg..SD3) bersumber dari WHO Child Growth
 * Standards (2006) / WHO Growth Reference (2007) yang menjadi dasar
 * penyusunan Permenkes No. 2 Tahun 2020 — lihat config/growth/*.php.
 */
class AntropometriService
{
    /** Rata-rata jumlah hari per bulan, dipakai untuk mengonversi rentang tanggal ke umur dalam bulan. */
    public const HARI_PER_BULAN = 30.4375;

    /**
     * Empat indeks yang dinilai, dengan label dan cara membaca umur/ukurannya.
     */
    public const INDIKATOR = [
        'bb_u' => [
            'label' => 'BB/U (Berat Badan menurut Umur)',
            'unit' => 'kg',
            'sumbu' => 'Umur (bulan)',
            'rentang' => '0–60 bulan',
        ],
        'pb_tb_u' => [
            'label' => 'PB/U atau TB/U (Panjang/Tinggi Badan menurut Umur)',
            'unit' => 'cm',
            'sumbu' => 'Umur (bulan)',
            'rentang' => '0–60 bulan',
        ],
        'bb_pb_tb' => [
            'label' => 'BB/PB atau BB/TB (Berat Badan menurut Panjang/Tinggi Badan)',
            'unit' => 'kg',
            'sumbu' => 'Panjang/Tinggi Badan (cm)',
            'rentang' => 'PB 45–110 cm, TB 65–120 cm',
        ],
        'imt_u' => [
            'label' => 'IMT/U (Indeks Massa Tubuh menurut Umur)',
            'unit' => 'kg/m²',
            'sumbu' => 'Umur (bulan)',
            'rentang' => '0–60 bulan',
        ],
    ];

    /**
     * Kategori dan ambang batas status gizi (Permenkes No. 2 Tahun 2020, Lampiran).
     */
    public const KATEGORI = [
        'bb_u' => [
            'indeks' => 'Berat Badan menurut Umur (BB/U)',
            'usia' => '0–60 bulan',
            'baris' => [
                ['label' => 'Berat badan sangat kurang', 'z' => '< -3 SD', 'severity' => 'severe'],
                ['label' => 'Berat badan kurang', 'z' => '-3 SD s.d. < -2 SD', 'severity' => 'moderate'],
                ['label' => 'Berat badan normal', 'z' => '-2 SD s.d. +1 SD', 'severity' => 'normal'],
                ['label' => 'Risiko berat badan lebih', 'z' => '> +1 SD', 'severity' => 'watch'],
            ],
        ],
        'pb_tb_u' => [
            'indeks' => 'Panjang Badan menurut Umur (PB/U) atau Tinggi Badan menurut Umur (TB/U)',
            'usia' => '0–60 bulan',
            'baris' => [
                ['label' => 'Sangat pendek (severely stunted)', 'z' => '< -3 SD', 'severity' => 'severe'],
                ['label' => 'Pendek (stunted)', 'z' => '-3 SD s.d. < -2 SD', 'severity' => 'moderate'],
                ['label' => 'Normal', 'z' => '-2 SD s.d. +3 SD', 'severity' => 'normal'],
                ['label' => 'Tinggi', 'z' => '> +3 SD', 'severity' => 'watch'],
            ],
        ],
        'bb_pb_tb' => [
            'indeks' => 'Berat Badan menurut Panjang Badan (BB/PB) atau Berat Badan menurut Tinggi Badan (BB/TB)',
            'usia' => '0–60 bulan',
            'baris' => [
                ['label' => 'Gizi buruk (severely wasted)', 'z' => '< -3 SD', 'severity' => 'severe'],
                ['label' => 'Gizi kurang (wasted)', 'z' => '-3 SD s.d. < -2 SD', 'severity' => 'moderate'],
                ['label' => 'Gizi baik (normal)', 'z' => '-2 SD s.d. +1 SD', 'severity' => 'normal'],
                ['label' => 'Berisiko gizi lebih (possible risk of overweight)', 'z' => '> +1 SD s.d. +2 SD', 'severity' => 'watch'],
                ['label' => 'Gizi lebih (overweight)', 'z' => '> +2 SD s.d. +3 SD', 'severity' => 'high'],
                ['label' => 'Obesitas (obese)', 'z' => '> +3 SD', 'severity' => 'severe-high'],
            ],
        ],
        'imt_u' => [
            'indeks' => 'Indeks Massa Tubuh menurut Umur (IMT/U)',
            'usia' => '0–60 bulan',
            'baris' => [
                ['label' => 'Gizi buruk (severely wasted)', 'z' => '< -3 SD', 'severity' => 'severe'],
                ['label' => 'Gizi kurang (wasted)', 'z' => '-3 SD s.d. < -2 SD', 'severity' => 'moderate'],
                ['label' => 'Gizi baik (normal)', 'z' => '-2 SD s.d. +1 SD', 'severity' => 'normal'],
                ['label' => 'Berisiko gizi lebih (possible risk of overweight)', 'z' => '> +1 SD s.d. +2 SD', 'severity' => 'watch'],
                ['label' => 'Gizi lebih (overweight)', 'z' => '> +2 SD s.d. +3 SD', 'severity' => 'high'],
                ['label' => 'Obesitas (obese)', 'z' => '> +3 SD', 'severity' => 'severe-high'],
            ],
        ],
    ];

    /**
     * Tabel referensi Kenaikan Berat Badan Minimum (KBM) tahun pertama,
     * sebagaimana lazim tercantum pada KMS/Buku KIA (Kemenkes RI).
     * Umur 12–24 bulan menggunakan estimasi rata-rata karena laju kenaikan
     * jauh lebih lambat dan bervariasi antar anak.
     */
    public const KENAIKAN_BB_MINIMUM = [
        ['bulan' => '0–1', 'minimum_gram' => 800],
        ['bulan' => '1–2', 'minimum_gram' => 900],
        ['bulan' => '2–3', 'minimum_gram' => 800],
        ['bulan' => '3–4', 'minimum_gram' => 600],
        ['bulan' => '4–5', 'minimum_gram' => 500],
        ['bulan' => '5–6', 'minimum_gram' => 400],
        ['bulan' => '6–7', 'minimum_gram' => 400],
        ['bulan' => '7–8', 'minimum_gram' => 300],
        ['bulan' => '8–9', 'minimum_gram' => 300],
        ['bulan' => '9–10', 'minimum_gram' => 300],
        ['bulan' => '10–11', 'minimum_gram' => 200],
        ['bulan' => '11–12', 'minimum_gram' => 200],
    ];

    /** Rata-rata kenaikan minimum per bulan untuk usia >= 12 bulan (indikatif). */
    public const KENAIKAN_BB_MINIMUM_DIATAS_12_BULAN = 200;

    private static function configKey(string $indikator): string
    {
        return match ($indikator) {
            'bb_u' => 'growth.wfa',
            'pb_tb_u' => 'growth.lhfa',
            'imt_u' => 'growth.bmifa',
            default => throw new \InvalidArgumentException("Indikator {$indikator} tidak memakai tabel umur bulanan."),
        };
    }

    /**
     * Ambil daftar baris tabel standar (umur 0..60 bulan) untuk BB/U, PB/U-TB/U, atau IMT/U.
     */
    public static function tabelUmurBulanan(string $indikator, string $gender): array
    {
        $data = config(self::configKey($indikator));
        $genderKey = strtoupper($gender) === 'P' ? 'girls' : 'boys';

        return $data[$genderKey];
    }

    /**
     * Ambil daftar baris tabel standar BB/PB (0-24 bln, panjang 45-110cm)
     * atau BB/TB (24-60 bln, tinggi 65-120cm).
     */
    public static function tabelPanjangTinggi(string $gender, bool $pakaiTinggiBerdiri): array
    {
        $data = config($pakaiTinggiBerdiri ? 'growth.wfh' : 'growth.wfl');
        $genderKey = strtoupper($gender) === 'P' ? 'girls' : 'boys';

        return $data[$genderKey];
    }

    /**
     * Bangun seluruh tabel standar (BB/U, PB/U-TB/U, BB/PB, BB/TB, IMT/U) untuk satu gender.
     */
    public static function tabelLengkap(string $gender): array
    {
        return [
            'bb_u' => self::tabelUmurBulanan('bb_u', $gender),
            'pb_tb_u' => self::tabelUmurBulanan('pb_tb_u', $gender),
            'bb_pb' => self::tabelPanjangTinggi($gender, false),
            'bb_tb' => self::tabelPanjangTinggi($gender, true),
            'imt_u' => self::tabelUmurBulanan('imt_u', $gender),
        ];
    }

    /**
     * Hitung Z-score memakai rumus LMS WHO:
     * Z = ((X/M)^L - 1) / (L*S)  jika L != 0
     * Z = ln(X/M) / S            jika L == 0
     */
    private static function zFromLms(float $x, float $l, float $m, float $s): float
    {
        if (abs($l) < 1e-9) {
            return log($x / $m) / $s;
        }

        return ((($x / $m) ** $l) - 1) / ($l * $s);
    }

    /**
     * Z-score untuk indikator berbasis umur (BB/U, PB/U-TB/U, IMT/U).
     * Umur boleh pecahan bulan; parameter LMS diinterpolasi linear
     * antara dua baris bulan bulat terdekat.
     */
    public static function zScoreUmur(string $indikator, string $gender, float $nilai, float $umurBulan): ?float
    {
        $umurBulan = max(0, min(60, $umurBulan));
        $rows = self::tabelUmurBulanan($indikator, $gender);

        $bawah = (int) floor($umurBulan);
        $atas = (int) ceil($umurBulan);

        if (! isset($rows[$bawah]) || ! isset($rows[$atas])) {
            return null;
        }

        if ($bawah === $atas) {
            $lms = $rows[$bawah];
        } else {
            $ratio = $umurBulan - $bawah;
            $lms = [
                'L' => $rows[$bawah]['L'] + ($rows[$atas]['L'] - $rows[$bawah]['L']) * $ratio,
                'M' => $rows[$bawah]['M'] + ($rows[$atas]['M'] - $rows[$bawah]['M']) * $ratio,
                'S' => $rows[$bawah]['S'] + ($rows[$atas]['S'] - $rows[$bawah]['S']) * $ratio,
            ];
        }

        return round(self::zFromLms($nilai, $lms['L'], $lms['M'], $lms['S']), 2);
    }

    /**
     * Z-score untuk BB/PB atau BB/TB. Pilih tabel otomatis dari umur anak:
     * < 24 bulan pakai BB/PB (panjang, telentang), >= 24 bulan pakai BB/TB (tinggi, berdiri).
     */
    public static function zScoreBeratMenurutPanjangTinggi(string $gender, float $beratKg, float $panjangTinggiCm, float $umurBulan): ?float
    {
        $pakaiTinggi = $umurBulan >= 24;
        $rows = self::tabelPanjangTinggi($gender, $pakaiTinggi);

        // Bulatkan ke kelipatan 0.5 cm terdekat sesuai baris tabel yang tersedia.
        $key = self::formatKeyCm($panjangTinggiCm);

        if (! isset($rows[$key])) {
            // clamp ke rentang tabel yang tersedia
            $keys = array_map('floatval', array_keys($rows));
            $min = min($keys);
            $max = max($keys);
            $clamped = min($max, max($min, $panjangTinggiCm));
            $key = self::formatKeyCm($clamped);
        }

        if (! isset($rows[$key])) {
            return null;
        }

        $lms = $rows[$key];

        return round(self::zFromLms($beratKg, $lms['L'], $lms['M'], $lms['S']), 2);
    }

    private static function formatKeyCm(float $cm): string
    {
        $rounded = round($cm * 2) / 2;

        return $rounded == (int) $rounded ? (string) (int) $rounded : (string) $rounded;
    }

    /**
     * Klasifikasikan Z-score menjadi kategori status gizi sesuai indikator.
     * Mengembalikan ['label' => ..., 'severity' => ...].
     */
    public static function klasifikasi(string $indikator, ?float $z): array
    {
        if ($z === null) {
            return ['label' => 'Tidak dapat dihitung', 'severity' => 'unknown'];
        }

        return match ($indikator) {
            'bb_u' => match (true) {
                $z < -3 => ['label' => 'Berat badan sangat kurang', 'severity' => 'severe'],
                $z < -2 => ['label' => 'Berat badan kurang', 'severity' => 'moderate'],
                $z <= 1 => ['label' => 'Berat badan normal', 'severity' => 'normal'],
                default => ['label' => 'Risiko berat badan lebih', 'severity' => 'watch'],
            },
            'pb_tb_u' => match (true) {
                $z < -3 => ['label' => 'Sangat pendek (severely stunted)', 'severity' => 'severe'],
                $z < -2 => ['label' => 'Pendek (stunted)', 'severity' => 'moderate'],
                $z <= 3 => ['label' => 'Normal', 'severity' => 'normal'],
                default => ['label' => 'Tinggi', 'severity' => 'watch'],
            },
            'bb_pb_tb', 'imt_u' => match (true) {
                $z < -3 => ['label' => 'Gizi buruk (severely wasted)', 'severity' => 'severe'],
                $z < -2 => ['label' => 'Gizi kurang (wasted)', 'severity' => 'moderate'],
                $z <= 1 => ['label' => 'Gizi baik (normal)', 'severity' => 'normal'],
                $z <= 2 => ['label' => 'Berisiko gizi lebih (possible risk of overweight)', 'severity' => 'watch'],
                $z <= 3 => ['label' => 'Gizi lebih (overweight)', 'severity' => 'high'],
                default => ['label' => 'Obesitas (obese)', 'severity' => 'severe-high'],
            },
            default => ['label' => 'Indikator tidak dikenal', 'severity' => 'unknown'],
        };
    }

    /**
     * Cari batas minimum kenaikan berat badan (gram) untuk rentang umur tertentu (awal interval, dalam bulan).
     */
    public static function kenaikanBbMinimum(int $umurBulanAwal): ?int
    {
        if ($umurBulanAwal < 0) {
            return null;
        }

        if ($umurBulanAwal >= 12) {
            return self::KENAIKAN_BB_MINIMUM_DIATAS_12_BULAN;
        }

        foreach (self::KENAIKAN_BB_MINIMUM as $row) {
            [$awal] = array_map('intval', explode('–', $row['bulan']));
            if ($awal === $umurBulanAwal) {
                return $row['minimum_gram'];
            }
        }

        return null;
    }

    /**
     * Nilai apakah kenaikan berat badan aktual (gram) mencukupi standar minimum KBM untuk umur awal interval.
     */
    public static function nilaiKenaikanBerat(int $umurBulanAwal, float $kenaikanGram): array
    {
        $minimum = self::kenaikanBbMinimum($umurBulanAwal);

        if ($minimum === null) {
            return ['status' => 'Di luar cakupan tabel', 'minimum' => null];
        }

        return [
            'status' => $kenaikanGram >= $minimum ? 'Naik (N)' : 'Tidak Naik (T)',
            'minimum' => $minimum,
        ];
    }

    /**
     * Hitung status gizi lengkap (BB/U, PB/U-TB/U, BB/PB-BB/TB, IMT/U) untuk satu titik pengukuran.
     * Dipakai bersama oleh dashboard, kalkulator publik, dan riwayat/laporan pengukuran.
     */
    public static function hitungLengkap(string $gender, float $beratKg, float $tinggiCm, float $umurBulan): array
    {
        $imt = $tinggiCm > 0 ? $beratKg / (($tinggiCm / 100) ** 2) : null;

        $zBbU = self::zScoreUmur('bb_u', $gender, $beratKg, $umurBulan);
        $zPbTbU = self::zScoreUmur('pb_tb_u', $gender, $tinggiCm, $umurBulan);
        $zBbPbTb = self::zScoreBeratMenurutPanjangTinggi($gender, $beratKg, $tinggiCm, $umurBulan);
        $zImtU = $imt ? self::zScoreUmur('imt_u', $gender, $imt, $umurBulan) : null;

        return [
            'umur_bulan' => round($umurBulan, 1),
            'pakai_tb' => $umurBulan >= 24,
            'imt' => $imt ? round($imt, 2) : null,
            'bb_u' => ['z' => $zBbU, ...self::klasifikasi('bb_u', $zBbU)],
            'pb_tb_u' => ['z' => $zPbTbU, ...self::klasifikasi('pb_tb_u', $zPbTbU)],
            'bb_pb_tb' => ['z' => $zBbPbTb, ...self::klasifikasi('bb_pb_tb', $zBbPbTb)],
            'imt_u' => ['z' => $zImtU, ...self::klasifikasi('imt_u', $zImtU)],
        ];
    }
}

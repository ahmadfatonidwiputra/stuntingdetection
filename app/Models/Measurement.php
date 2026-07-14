<?php

namespace App\Models;

use App\Services\AntropometriService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    protected $fillable = [
        'user_id',
        'anak_id',
        'child_name',
        'parent_name',
        'posyandu_name',
        'address',
        'birth_date',
        'gender',
        'height_cm',
        'weight_kg',
        'z_score',
        'stunting_category',
        'photo_path',
        'pose_photo_path',
        'measured_at',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'height_cm' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'z_score' => 'decimal:2',
        'measured_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    public static function calculateZScore(float $heightCm, int $ageMonths, string $gender): float
    {
        $ageMonths = min(60, max(0, $ageMonths));
        $genderKey = $gender === 'P' ? 'girls' : 'boys';

        $config = config("stunting.{$genderKey}");
        $keys = config('stunting.key_months');

        // Find interpolation range
        $m1 = 0;
        $m2 = 60;
        for ($i = 0; $i < count($keys) - 1; $i++) {
            if ($ageMonths >= $keys[$i] && $ageMonths <= $keys[$i + 1]) {
                $m1 = $keys[$i];
                $m2 = $keys[$i + 1];
                break;
            }
        }

        if ($m1 == $m2) {
            $ref = $config[$m1];
        } else {
            // Linear Interpolation
            $ratio = ($ageMonths - $m1) / ($m2 - $m1);
            $ref = [
                'median' => $config[$m1]['median'] + ($config[$m2]['median'] - $config[$m1]['median']) * $ratio,
                'sd2neg' => $config[$m1]['sd2neg'] + ($config[$m2]['sd2neg'] - $config[$m1]['sd2neg']) * $ratio,
                'sd3neg' => $config[$m1]['sd3neg'] + ($config[$m2]['sd3neg'] - $config[$m1]['sd3neg']) * $ratio,
            ];
        }

        $median = $ref['median'];
        // Kemenkes/WHO z-score standard deviation approximation for left tail
        $sd = ($median - $ref['sd2neg']) / 2;

        if ($sd <= 0) {
            $sd = 1;
        } // prevent division by zero

        $zScore = ($heightCm - $median) / $sd;

        return round($zScore, 2);
    }

    public static function getStuntingCategory(float $zScore): string
    {
        if ($zScore < -3.0) {
            return 'Sangat Stunting';
        }
        if ($zScore < -2.0) {
            return 'Stunting';
        }

        return 'Normal';
    }

    /**
     * Status gizi lengkap (BB/U, PB/U-TB/U, BB/PB-BB/TB, IMT/U) sesuai Standar Antropometri
     * Anak - Permenkes RI No. 2 Tahun 2020, dihitung dari data pengukuran ini secara langsung
     * (tidak disimpan di kolom manapun). Null jika data umur/tinggi/berat tidak lengkap.
     */
    public function antropometriLengkap(): ?array
    {
        $gender = $this->anak?->jenis_kelamin ?? $this->gender;
        $lahir = $this->anak?->tanggal_lahir ?? $this->birth_date;

        if (! $gender || ! $lahir || ! $this->measured_at || ! $this->height_cm || ! $this->weight_kg) {
            return null;
        }

        $umurBulan = $lahir->diffInDays($this->measured_at) / AntropometriService::HARI_PER_BULAN;

        return AntropometriService::hitungLengkap($gender, (float) $this->weight_kg, (float) $this->height_cm, $umurBulan);
    }
}

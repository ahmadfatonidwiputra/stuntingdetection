<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    protected $fillable = [
        'user_id',
        'height_cm',
        'weight_kg',
        'bmi',
        'bmi_category',
        'photo_path',
        'measured_at',
        'notes',
    ];

    protected $casts = [
        'height_cm' => 'decimal:1',
        'weight_kg' => 'decimal:1',
        'bmi' => 'decimal:1',
        'measured_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function calculateBmi(float $heightCm, float $weightKg): float
    {
        $heightM = $heightCm / 100;
        return round($weightKg / ($heightM * $heightM), 1);
    }

    public static function getBmiCategory(float $bmi): string
    {
        if ($bmi < 18.5) return 'Kurus';
        if ($bmi < 25.0) return 'Normal';
        if ($bmi < 30.0) return 'Gemuk';
        return 'Obesitas';
    }
}

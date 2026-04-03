<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Anak extends Model
{
    protected $table = 'anak';

    protected $fillable = [
        'petugas_id', 'posyandu_id',
        'nama', 'nik_anak', 'tanggal_lahir', 'tempat_lahir', 'jenis_kelamin',
        'no_kk', 'nama_ayah', 'nik_ayah', 'nama_ibu', 'nik_ibu',
        'no_telepon_ortu', 'email_ortu', 'alamat',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    // ── Relationships ─────────────────────────────────

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function posyandu(): BelongsTo
    {
        return $this->belongsTo(Posyandu::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    public function latestMeasurement(): HasOne
    {
        return $this->hasOne(Measurement::class)->latestOfMany('measured_at');
    }

    public function latestPhotoMeasurement(): HasOne
    {
        return $this->hasOne(Measurement::class)->ofMany(
            ['measured_at' => 'max'],
            fn ($query) => $query->whereNotNull('photo_path')
        );
    }

    public function orangTuaRelations(): HasMany
    {
        return $this->hasMany(OrangTuaAnak::class, 'anak_id');
    }

    public function orangTua(): BelongsToMany
    {
        return $this->belongsToMany(
            OrangTuaProfile::class,
            'orang_tua_anak',
            'anak_id',
            'orang_tua_id'
        )->withPivot('hubungan', 'is_primary', 'verified_at');
    }

    // ── Scopes ────────────────────────────────────────

    public function scopeForOrangTua(Builder $query, int $userId): Builder
    {
        return $query->whereHas('orangTuaRelations', function ($q) use ($userId) {
            $q->whereHas('orangTuaProfile', function ($q2) use ($userId) {
                $q2->where('user_id', $userId);
            });
        });
    }

    public function scopeForPosyandu(Builder $query, int $posyanduId): Builder
    {
        return $query->where('posyandu_id', $posyanduId);
    }

    // ── Accessors ─────────────────────────────────────

    public function getUmurAttribute(): array
    {
        $now = now();
        
        /** @var \Carbon\Carbon|null $lahirVal */
        $lahirVal = $this->tanggal_lahir;
        
        if (!$lahirVal) {
            return [
                'years'        => 0,
                'months'       => 0,
                'days'         => 0,
                'total_months' => 0,
                'formatted'    => '-',
            ];
        }

        $diff = $now->diff($lahirVal);

        $parts = [];
        if ($diff->y > 0) $parts[] = "{$diff->y} Tahun";
        if ($diff->m > 0) $parts[] = "{$diff->m} Bulan";
        if ($diff->d > 0) $parts[] = "{$diff->d} Hari";

        $formatted = count($parts) > 0 ? implode(' ', $parts) : "Usia 0 Hari";

        return [
            'years'        => $diff->y,
            'months'       => $diff->m,
            'days'         => $diff->d,
            'total_months' => ($diff->y * 12) + $diff->m,
            'formatted'    => $formatted,
        ];
    }

    public function getUmurBulanAttribute(): int
    {
        return (int) now()->diffInMonths($this->tanggal_lahir);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Posyandu extends Model
{
    protected $table = 'posyandu';

    protected $fillable = [
        'nama', 'kode_posyandu', 'alamat', 'kelurahan',
        'kecamatan', 'kota', 'provinsi', 'latitude',
        'longitude', 'no_telepon', 'jadwal_buka', 'status',
    ];

    protected $casts = [
        'jadwal_buka' => 'array',
        'latitude'    => 'decimal:8',
        'longitude'   => 'decimal:8',
    ];

    // ── Relationships ─────────────────────────────────

    public function petugas(): HasMany
    {
        return $this->hasMany(PetugasProfile::class);
    }

    public function anak(): HasMany
    {
        return $this->hasMany(Anak::class);
    }

    // ── Scopes ────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

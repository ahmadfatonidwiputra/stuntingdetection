<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetugasProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nik',
        'no_telepon',
        'posyandu_name',
        'posyandu_address',
        'kelurahan',
        'kecamatan',
        'kota',
        'provinsi',
        'document_path',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->posyandu_address,
            $this->kelurahan,
            $this->kecamatan,
            $this->kota,
            $this->provinsi,
        ])->filter()->implode(', ');
    }
}

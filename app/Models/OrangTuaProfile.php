<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrangTuaProfile extends Model
{
    protected $table = 'orang_tua_profile';

    protected $fillable = [
        'user_id', 'nama_lengkap', 'nik', 'no_kk',
        'hubungan', 'no_telepon', 'alamat',
    ];

    // ── Relationships ─────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anakRelations(): HasMany
    {
        return $this->hasMany(OrangTuaAnak::class, 'orang_tua_id');
    }

    public function anak(): BelongsToMany
    {
        return $this->belongsToMany(
            Anak::class,
            'orang_tua_anak',
            'orang_tua_id',
            'anak_id'
        )->withPivot('hubungan', 'is_primary', 'verified_at');
    }
}

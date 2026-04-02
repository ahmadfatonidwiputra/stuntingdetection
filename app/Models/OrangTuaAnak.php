<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrangTuaAnak extends Model
{
    protected $table = 'orang_tua_anak';

    public $timestamps = false;

    protected $fillable = [
        'orang_tua_id', 'anak_id', 'hubungan',
        'is_primary', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'is_primary'  => 'boolean',
        'verified_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────

    public function orangTuaProfile(): BelongsTo
    {
        return $this->belongsTo(OrangTuaProfile::class, 'orang_tua_id');
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

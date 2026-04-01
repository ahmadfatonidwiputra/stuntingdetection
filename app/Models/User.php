<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Role Helpers ──────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas_posyandu';
    }

    // ── Status Helpers ────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    // ── Scopes ────────────────────────────────────

    public function scopeSuperadmin(Builder $query): Builder
    {
        return $query->where('role', 'super_admin');
    }

    public function scopePetugas(Builder $query): Builder
    {
        return $query->where('role', 'petugas_posyandu');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // ── Relationships ─────────────────────────────

    public function petugasProfile(): HasOne
    {
        return $this->hasOne(PetugasProfile::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    // ── Static Helpers ────────────────────────────

    public static function hasSuperadmin(): bool
    {
        return static::query()->superadmin()->exists();
    }

    public static function needsInitialSuperadmin(): bool
    {
        return ! static::query()->exists();
    }
}

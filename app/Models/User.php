<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_superadmin',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_superadmin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected function isSuperadmin(): Attribute
    {
        return Attribute::make(
            set: static fn ($value) => $value ? true : null,
        );
    }

    public function scopeSuperadmin(Builder $query): Builder
    {
        return $query->where('is_superadmin', true);
    }

    public static function hasSuperadmin(): bool
    {
        return static::query()->superadmin()->exists();
    }

    public static function needsInitialSuperadmin(): bool
    {
        return ! static::query()->exists();
    }
}

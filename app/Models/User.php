<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'username',
        'email',
        'whatsapp',
        'profile_photo_path',
        'password',
        'role',
        'is_active',
        'can_create_lpj',
        'can_transfer_balance',
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
            'is_active' => 'boolean',
            'can_create_lpj' => 'boolean',
            'can_transfer_balance' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->isAdmin();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return asset('storage/'.$this->profile_photo_path);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function createdLpjs(): HasMany
    {
        return $this->hasMany(Lpj::class, 'created_by');
    }

    public function assignedLpjs(): HasMany
    {
        return $this->hasMany(LpjAssignedUser::class);
    }
}

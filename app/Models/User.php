<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory;
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';
    public const ROLE_DIREKTUR = 'direktur';

    protected $fillable = [
        'name',
        'username',
        'email',
        'whatsapp',
        'profile_photo_path',
        'profile_photo_disk',
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

        return app(\App\Services\AppFileStorageService::class)->url(
            $this->profile_photo_path,
            $this->profile_photo_disk ?: 'public'
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isDirektur(): bool
    {
        return $this->role === self::ROLE_DIREKTUR;
    }

    public function canAccessPwa(): bool
    {
        return $this->is_active && ($this->isUser() || $this->isDirektur());
    }

    public function canInputPwa(): bool
    {
        return $this->is_active && $this->isUser();
    }


    public function jabatanLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_DIREKTUR => 'Direktur',
            self::ROLE_USER => 'Petugas',
            default => 'Petugas',
        };
    }
    public function createdLpjs(): HasMany
    {
        return $this->hasMany(Lpj::class, 'created_by');
    }

    public function assignedLpjs(): HasMany
    {
        return $this->hasMany(LpjAssignedUser::class);
    }

    public function activityNotes(): HasMany
    {
        return $this->hasMany(ActivityNote::class);
    }

    public function lpjBalances(): HasMany
    {
        return $this->hasMany(LpjUserBalance::class);
    }

    public function lpjBalanceMutations(): HasMany
    {
        return $this->hasMany(LpjBalanceMutation::class);
    }

    public function lpjFinancialTransactions(): HasMany
    {
        return $this->hasMany(LpjFinancialTransaction::class);
    }

    public function lpjAdvanceClaims(): HasMany
    {
        return $this->hasMany(LpjAdvanceClaim::class);
    }
    // KICAP_ADMIN_AVATAR_PREVIEW_URL_V1
    public function getAdminAvatarPreviewUrlAttribute(): ?string
    {
        if (! empty($this->profile_photo_path)) {
            try {
                $url = app(\App\Services\AppFileStorageService::class)->url(
                    $this->profile_photo_path,
                    $this->profile_photo_disk ?: 'public'
                );

                if (is_string($url) && trim($url) !== '') {
                    return $url;
                }
            } catch (\Throwable $e) {
                // Fallback ke proxy internal jika resolver storage gagal.
            }
        }

        try {
            return route('admin.user-avatar.proxy', [
                'user' => $this->getKey(),
                'v' => optional($this->updated_at)->timestamp,
            ]);
        } catch (\Throwable $e) {
            return url('/admin/user-avatar/' . $this->getKey() . '/avatar');
        }
    }

}

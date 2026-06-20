<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageSetting extends Model
{
    public const PROVIDER_LOCAL = 'local';

    public const PROVIDER_R2 = 'r2';

    protected $fillable = [
        'provider',
        'r2_account_id',
        'r2_access_key_id',
        'r2_secret_access_key',
        'r2_bucket',
        'r2_endpoint',
        'r2_public_url',
        'root_prefix',
        'auto_webp_enabled',
        'webp_quality',
        'max_image_width',
    ];

    protected function casts(): array
    {
        return [
            'r2_secret_access_key' => 'encrypted',
            'auto_webp_enabled' => 'boolean',
            'webp_quality' => 'integer',
            'max_image_width' => 'integer',
        ];
    }

    public static function providerOptions(): array
    {
        return [
            self::PROVIDER_LOCAL => 'Local Storage',
            self::PROVIDER_R2 => 'Cloudflare R2',
        ];
    }

    public static function active(): self
    {
        return self::query()->firstOrCreate(['id' => 1], [
            'provider' => self::PROVIDER_LOCAL,
            'auto_webp_enabled' => true,
            'webp_quality' => 78,
            'max_image_width' => 1800,
        ]);
    }

    public function r2Endpoint(): ?string
    {
        if (filled($this->r2_endpoint)) {
            return rtrim((string) $this->r2_endpoint, '/');
        }

        if (blank($this->r2_account_id)) {
            return null;
        }

        return 'https://'.$this->r2_account_id.'.r2.cloudflarestorage.com';
    }

    public function isR2Configured(): bool
    {
        return $this->provider === self::PROVIDER_R2
            && filled($this->r2_access_key_id)
            && filled($this->r2_secret_access_key)
            && filled($this->r2_bucket)
            && filled($this->r2Endpoint());
    }
}

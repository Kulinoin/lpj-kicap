<?php

namespace Database\Seeders;

use App\Models\StorageSetting;
use Illuminate\Database\Seeder;

class StorageSettingSeeder extends Seeder
{
    public function run(): void
    {
        StorageSetting::query()->firstOrCreate(['id' => 1], [
            'provider' => StorageSetting::PROVIDER_LOCAL,
            'auto_webp_enabled' => true,
            'webp_quality' => 78,
            'max_image_width' => 1800,
        ]);
    }
}

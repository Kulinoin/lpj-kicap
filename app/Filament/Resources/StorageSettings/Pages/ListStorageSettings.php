<?php

namespace App\Filament\Resources\StorageSettings\Pages;

use App\Filament\Resources\StorageSettings\StorageSettingResource;
use App\Models\StorageSetting;
use Filament\Resources\Pages\ListRecords;

class ListStorageSettings extends ListRecords
{
    protected static string $resource = StorageSettingResource::class;

    public function mount(): void
    {
        StorageSetting::active();

        parent::mount();
    }
}

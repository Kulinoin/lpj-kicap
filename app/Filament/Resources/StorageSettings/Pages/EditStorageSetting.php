<?php

namespace App\Filament\Resources\StorageSettings\Pages;

use App\Filament\Resources\StorageSettings\StorageSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditStorageSetting extends EditRecord
{
    protected static string $resource = StorageSettingResource::class;

    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
}

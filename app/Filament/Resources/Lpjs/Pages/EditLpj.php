<?php

namespace App\Filament\Resources\Lpjs\Pages;

use App\Filament\Resources\Lpjs\LpjResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpj extends EditRecord
{
    protected static string $resource = LpjResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
}

<?php

namespace App\Filament\Resources\LpjTypes\Pages;

use App\Filament\Resources\LpjTypes\LpjTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpjType extends EditRecord
{
    protected static string $resource = LpjTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

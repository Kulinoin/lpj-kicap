<?php

namespace App\Filament\Resources\LpjTypes\Pages;

use App\Filament\Resources\LpjTypes\LpjTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpjTypes extends ListRecords
{
    protected static string $resource = LpjTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

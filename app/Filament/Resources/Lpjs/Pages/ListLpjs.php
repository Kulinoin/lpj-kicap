<?php

namespace App\Filament\Resources\Lpjs\Pages;

use App\Filament\Resources\Lpjs\LpjResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpjs extends ListRecords
{
    protected static string $resource = LpjResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

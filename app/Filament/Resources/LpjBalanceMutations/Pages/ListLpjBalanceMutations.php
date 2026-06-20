<?php

namespace App\Filament\Resources\LpjBalanceMutations\Pages;

use App\Filament\Resources\LpjBalanceMutations\LpjBalanceMutationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpjBalanceMutations extends ListRecords
{
    protected static string $resource = LpjBalanceMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Beri Dana Pegangan'),
        ];
    }
}

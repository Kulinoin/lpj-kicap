<?php

namespace App\Filament\Resources\ActivityCommittees\Pages;

use App\Filament\Resources\ActivityCommittees\ActivityCommitteeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListActivityCommittees extends ListRecords
{
    protected static string $resource = ActivityCommitteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Panitia'),
        ];
    }
}
<?php

namespace App\Filament\Resources\ActivityParticipants\Pages;

use App\Filament\Resources\ActivityParticipants\ActivityParticipantResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListActivityParticipants extends ListRecords
{
    protected static string $resource = ActivityParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Peserta'),
        ];
    }
}
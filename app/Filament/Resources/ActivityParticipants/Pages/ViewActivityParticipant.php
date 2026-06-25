<?php

namespace App\Filament\Resources\ActivityParticipants\Pages;

use App\Filament\Resources\ActivityParticipants\ActivityParticipantResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewActivityParticipant extends ViewRecord
{
    protected static string $resource = ActivityParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Peserta'),
        ];
    }
}
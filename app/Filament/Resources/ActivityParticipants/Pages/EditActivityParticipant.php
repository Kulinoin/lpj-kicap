<?php

namespace App\Filament\Resources\ActivityParticipants\Pages;

use App\Filament\Resources\ActivityParticipants\ActivityParticipantResource;
use Filament\Resources\Pages\EditRecord;

class EditActivityParticipant extends EditRecord
{
    protected static string $resource = ActivityParticipantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! blank($data['photo_path'] ?? null)) {
            $data['photo_disk'] = 'public';
        }

        return $data;
    }
}
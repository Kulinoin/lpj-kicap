<?php

namespace App\Filament\Resources\ActivityParticipants\Pages;

use App\Filament\Resources\ActivityParticipants\ActivityParticipantResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateActivityParticipant extends CreateRecord
{
    protected static string $resource = ActivityParticipantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        if (! blank($data['photo_path'] ?? null)) {
            $data['photo_disk'] = 'public';
        }

        return $data;
    }
}
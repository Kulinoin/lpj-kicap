<?php

namespace App\Filament\Resources\ActivitySchedules\Pages;

use App\Filament\Resources\ActivitySchedules\ActivityScheduleResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditActivitySchedule extends EditRecord
{
    protected static string $resource = ActivityScheduleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! blank($data['status'] ?? null)) {
            $data['status_updated_by'] = Auth::id();
            $data['status_updated_at'] = now();
        }

        return $data;
    }
}
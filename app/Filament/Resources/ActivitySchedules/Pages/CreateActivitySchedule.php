<?php

namespace App\Filament\Resources\ActivitySchedules\Pages;

use App\Filament\Resources\ActivitySchedules\ActivityScheduleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateActivitySchedule extends CreateRecord
{
    protected static string $resource = ActivityScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        if (! blank($data['status'] ?? null)) {
            $data['status_updated_by'] = Auth::id();
            $data['status_updated_at'] = now();
        }

        return $data;
    }
}
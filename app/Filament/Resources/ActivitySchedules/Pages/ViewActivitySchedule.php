<?php

namespace App\Filament\Resources\ActivitySchedules\Pages;

use App\Filament\Resources\ActivitySchedules\ActivityScheduleResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewActivitySchedule extends ViewRecord
{
    protected static string $resource = ActivityScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Rundown'),
        ];
    }
}
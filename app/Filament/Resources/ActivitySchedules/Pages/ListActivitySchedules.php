<?php

namespace App\Filament\Resources\ActivitySchedules\Pages;

use App\Filament\Resources\ActivitySchedules\ActivityScheduleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListActivitySchedules extends ListRecords
{
    protected static string $resource = ActivityScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Rundown'),
        ];
    }
}
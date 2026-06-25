<?php

namespace App\Filament\Resources\ActivityCommittees\Pages;

use App\Filament\Resources\ActivityCommittees\ActivityCommitteeResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewActivityCommittee extends ViewRecord
{
    protected static string $resource = ActivityCommitteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Panitia'),
        ];
    }
}
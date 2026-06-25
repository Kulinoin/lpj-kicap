<?php

namespace App\Filament\Resources\ActivityCommittees\Pages;

use App\Filament\Resources\ActivityCommittees\ActivityCommitteeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateActivityCommittee extends CreateRecord
{
    protected static string $resource = ActivityCommitteeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }
}
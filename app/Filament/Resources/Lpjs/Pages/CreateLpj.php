<?php

namespace App\Filament\Resources\Lpjs\Pages;

use App\Filament\Resources\Lpjs\LpjResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLpj extends CreateRecord
{
    protected static string $resource = LpjResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }
}

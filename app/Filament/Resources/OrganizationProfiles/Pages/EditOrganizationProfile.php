<?php

namespace App\Filament\Resources\OrganizationProfiles\Pages;

use App\Filament\Resources\OrganizationProfiles\OrganizationProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationProfile extends EditRecord
{
    protected static string $resource = OrganizationProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

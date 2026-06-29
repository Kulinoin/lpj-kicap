<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.view-user';

    public function getTitle(): string
    {
        return 'Preview User';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit User')
                ->icon('heroicon-m-pencil-square'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ActivityParticipants\Pages;

use App\Filament\Resources\ActivityParticipants\ActivityParticipantResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListActivityParticipants extends ListRecords
{
    protected static string $resource = ActivityParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importPeserta')
                ->label('Import Peserta')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->url(url('/admin/tools/import-peserta')),
            CreateAction::make()
                ->label('Tambah Peserta'),
        ];
    }
}
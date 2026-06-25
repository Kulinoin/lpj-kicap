<?php

namespace App\Filament\Resources\ActivityParticipants\Pages;

use App\Filament\Resources\ActivityParticipants\ActivityParticipantResource;
use Filament\Resources\Pages\ListRecords;

class ListActivityParticipants extends ListRecords
{
    protected static string $resource = ActivityParticipantResource::class;
}
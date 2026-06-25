<?php

namespace App\Filament\Resources\ActivityAttachments\Pages;

use App\Filament\Resources\ActivityAttachments\ActivityAttachmentResource;
use Filament\Resources\Pages\ListRecords;

class ListActivityAttachments extends ListRecords
{
    protected static string $resource = ActivityAttachmentResource::class;
}
<?php

namespace App\Filament\Resources\LpjFundReceipts\Pages;

use App\Filament\Resources\LpjFundReceipts\LpjFundReceiptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpjFundReceipts extends ListRecords
{
    protected static string $resource = LpjFundReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

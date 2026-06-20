<?php

namespace App\Filament\Resources\LpjFundReceipts\Pages;

use App\Filament\Resources\LpjFundReceipts\LpjFundReceiptResource;
use App\Models\Lpj;
use App\Models\LpjFundReceipt;
use App\Services\LpjFinanceService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateLpjFundReceipt extends CreateRecord
{
    protected static string $resource = LpjFundReceiptResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $lpj = Lpj::query()->findOrFail($data['lpj_id']);

        return app(LpjFinanceService::class)->recordFundReceipt($lpj, Auth::user(), $data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Dana masuk event tercatat';
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

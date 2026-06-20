<?php

namespace App\Filament\Resources\LpjBalanceMutations\Pages;

use App\Filament\Resources\LpjBalanceMutations\LpjBalanceMutationResource;
use App\Models\Lpj;
use App\Models\User;
use App\Services\LpjFinanceService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateLpjBalanceMutation extends CreateRecord
{
    protected static string $resource = LpjBalanceMutationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $lpj = Lpj::query()->findOrFail($data['lpj_id']);
        $user = User::query()->findOrFail($data['user_id']);

        return app(LpjFinanceService::class)->grantUserFund($lpj, $user, Auth::user(), $data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Dana pegangan user tercatat';
    }
}

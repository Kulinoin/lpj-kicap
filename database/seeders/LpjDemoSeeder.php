<?php

namespace Database\Seeders;

use App\Models\Lpj;
use App\Models\LpjAssignedUser;
use App\Models\LpjUserBalance;
use App\Models\LpjType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LpjDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@kicap.id')->first();
        $user = User::query()->where('email', 'user@kicap.id')->first();
        $pendamping = User::query()->where('email', 'pendamping@kicap.id')->first();
        $type = LpjType::query()->where('slug', 'kegiatan-internal')->first();

        if (! $admin || ! $user || ! $pendamping || ! $type) {
            return;
        }

        $lpj = Lpj::query()->updateOrCreate(
            ['code' => 'LPJ-DEMO-001'],
            [
                'title' => 'Contoh LPJ Kegiatan Internal',
                'lpj_type_id' => $type->id,
                'created_by' => $admin->id,
                'person_in_charge_id' => $user->id,
                'status' => Lpj::STATUS_AKTIF,
                'completeness_status' => Lpj::COMPLETENESS_BELUM_LENGKAP,
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'location' => '[Lokasi kegiatan]',
                'funding_source' => '[Sumber dana]',
                'period_label' => now()->translatedFormat('F Y'),
            ]
        );

        LpjAssignedUser::query()->updateOrCreate(
            [
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
            ],
            [
                'role_label' => 'Petugas Lapangan',
                'can_input_transaction' => true,
                'can_upload_documentation' => true,
                'can_edit_activity_data' => true,
            ]
        );

        LpjAssignedUser::query()->updateOrCreate(
            [
                'lpj_id' => $lpj->id,
                'user_id' => $pendamping->id,
            ],
            [
                'role_label' => 'Pendamping Lapangan',
                'can_input_transaction' => true,
                'can_upload_documentation' => true,
                'can_edit_activity_data' => true,
            ]
        );

        LpjUserBalance::query()->updateOrCreate(
            [
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
            ],
            [
                'balance' => 750000,
            ]
        );

        LpjUserBalance::query()->updateOrCreate(
            [
                'lpj_id' => $lpj->id,
                'user_id' => $pendamping->id,
            ],
            [
                'balance' => 0,
            ]
        );
    }
}

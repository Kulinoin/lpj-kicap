<?php

namespace Tests\Feature;

use App\Models\ActivityNote;
use App\Models\Lpj;
use App\Models\LpjAssignedUser;
use App\Models\LpjType;
use App\Models\User;
use Database\Seeders\KicapUserSeeder;
use Database\Seeders\LpjTypeSeeder;
use Database\Seeders\NarrativeTemplateSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Slice02LpjDetailMobileInputTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_open_assigned_active_lpj_detail_with_operational_notes(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $type = LpjType::query()->where('slug', 'penyelenggaraan-event')->firstOrFail();

        $lpj = $this->makeAssignedLpj('LPJ-S02-EVENT', Lpj::STATUS_AKTIF, $admin, $user, $type);

        $response = $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}");

        $response->assertOk()
            ->assertJsonPath('data.code', 'LPJ-S02-EVENT')
            ->assertJsonPath('data.status_label', 'Aktif')
            ->assertJsonPath('data.can_input_operational_data', true)
            ->assertJsonMissingPath('data.narratives')
            ->assertJsonFragment(['label' => 'Hasil di Lapangan'])
            ->assertJsonFragment(['label' => 'Kendala'])
            ->assertJsonFragment(['label' => 'Saran Tindak Lanjut']);

        foreach (ActivityNote::types() as $type) {
            $this->assertDatabaseHas('activity_notes', [
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
                'type' => $type,
                'include_in_report' => false,
            ]);
        }
    }

    public function test_user_can_save_operational_notes_for_active_lpj(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $type = LpjType::query()->where('slug', 'kegiatan-internal')->firstOrFail();

        $lpj = $this->makeAssignedLpj('LPJ-S02-SAVE', Lpj::STATUS_AKTIF, $admin, $user, $type);

        $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}")->assertOk();

        $response = $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/activity-notes", [
            'notes' => [
                [
                    'type' => ActivityNote::TYPE_RESULT,
                    'content' => 'Kegiatan berjalan lancar dan peserta hadir sesuai daftar.',
                ],
                [
                    'type' => ActivityNote::TYPE_OBSTACLE,
                    'content' => 'Ada keterlambatan transportasi saat berangkat.',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'type' => ActivityNote::TYPE_RESULT,
                'content' => 'Kegiatan berjalan lancar dan peserta hadir sesuai daftar.',
                'include_in_report' => false,
            ])
            ->assertJsonFragment([
                'type' => ActivityNote::TYPE_OBSTACLE,
                'content' => 'Ada keterlambatan transportasi saat berangkat.',
                'include_in_report' => false,
            ]);

        $this->assertDatabaseHas('activity_notes', [
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'type' => ActivityNote::TYPE_RESULT,
            'content' => 'Kegiatan berjalan lancar dan peserta hadir sesuai daftar.',
        ]);
    }

    public function test_user_cannot_edit_lpj_narratives_from_mobile_api(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $type = LpjType::query()->where('slug', 'kegiatan-internal')->firstOrFail();

        $lpj = $this->makeAssignedLpj('LPJ-S02-NARASI-LOCK', Lpj::STATUS_AKTIF, $admin, $user, $type);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/narratives", [
            'narratives' => [
                [
                    'section' => 'background',
                    'content' => 'User tidak boleh mengurus narasi.',
                ],
            ],
        ])->assertNotFound();
    }

    public function test_finish_lpj_is_read_only_for_assigned_user(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $type = LpjType::query()->where('slug', 'kegiatan-internal')->firstOrFail();

        $lpj = $this->makeAssignedLpj('LPJ-S02-FINISH', Lpj::STATUS_FINISH, $admin, $user, $type);

        $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}")
            ->assertOk()
            ->assertJsonPath('data.status_label', 'Selesai')
            ->assertJsonPath('data.can_input_operational_data', false);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/activity-notes", [
            'notes' => [
                [
                    'type' => ActivityNote::TYPE_RESULT,
                    'content' => 'Tidak boleh tersimpan.',
                ],
            ],
        ])->assertForbidden();
    }

    public function test_unassigned_or_admin_user_cannot_access_mobile_lpj_detail(): void
    {
        [$admin, $user, $otherUser] = $this->seedSliceData();
        $type = LpjType::query()->where('slug', 'kegiatan-internal')->firstOrFail();

        $lpj = $this->makeAssignedLpj('LPJ-S02-HIDDEN', Lpj::STATUS_AKTIF, $admin, $user, $type);

        $this->actingAs($otherUser)->getJson("/api/app/lpjs/{$lpj->id}")->assertNotFound();
        $this->actingAs($admin)->getJson("/api/app/lpjs/{$lpj->id}")->assertForbidden();
    }

    private function seedSliceData(): array
    {
        $this->seed([
            KicapUserSeeder::class,
            LpjTypeSeeder::class,
            NarrativeTemplateSeeder::class,
        ]);

        return [
            User::query()->where('email', 'admin@kicap.id')->firstOrFail(),
            User::query()->where('email', 'user@kicap.id')->firstOrFail(),
            User::query()->where('email', 'pendamping@kicap.id')->firstOrFail(),
        ];
    }

    private function makeAssignedLpj(string $code, string $status, User $admin, User $user, LpjType $type): Lpj
    {
        $lpj = Lpj::query()->create([
            'code' => $code,
            'title' => $code,
            'lpj_type_id' => $type->id,
            'created_by' => $admin->id,
            'person_in_charge_id' => $user->id,
            'status' => $status,
            'completeness_status' => Lpj::COMPLETENESS_BELUM_LENGKAP,
            'start_date' => '2026-06-20',
            'end_date' => '2026-06-21',
            'location' => 'Jakarta',
            'funding_source' => 'Dana Operasional',
            'external_organizer' => 'Mitra Seleksi',
            'organization_role' => 'Pendamping peserta',
        ]);

        LpjAssignedUser::query()->create([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'role_label' => 'Petugas Lapangan',
            'can_input_transaction' => true,
            'can_upload_documentation' => true,
            'can_edit_activity_data' => true,
        ]);

        return $lpj;
    }
}

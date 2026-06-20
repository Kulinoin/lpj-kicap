<?php

namespace Tests\Feature;

use App\Models\ActivityAttachment;
use App\Models\ActivityDocumentation;
use App\Models\ActivityNote;
use App\Models\Lpj;
use App\Models\LpjAssignedUser;
use App\Models\LpjFinancialTransaction;
use App\Models\LpjType;
use App\Models\User;
use Database\Seeders\KicapUserSeeder;
use Database\Seeders\LpjTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Slice04ExecutionDocumentationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_save_execution_participants_committee_and_rundown(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S04-EXECUTION', Lpj::STATUS_AKTIF, $admin, $user);

        $response = $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/execution-data", [
            'participants' => [
                [
                    'name' => 'Sakura Tanaka',
                    'origin' => 'Kelas Bahasa Jepang A',
                    'participant_number' => 'JP-01',
                    'attendance_status' => 'hadir',
                    'result_status' => 'Lulus seleksi administrasi',
                    'note' => 'Datang tepat waktu.',
                ],
            ],
            'committees' => [
                [
                    'name' => 'User Lapangan',
                    'role' => 'Pendamping',
                    'task' => 'Mendampingi peserta selama registrasi.',
                    'contact' => '08123456789',
                ],
            ],
            'schedules' => [
                [
                    'start_time' => '2026-06-20T08:00',
                    'end_time' => '2026-06-20T09:00',
                    'activity_name' => 'Registrasi peserta',
                    'responsible_person' => 'User Lapangan',
                    'note' => 'Registrasi di meja depan.',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.execution.can_edit_activity_data', true)
            ->assertJsonFragment(['name' => 'Sakura Tanaka'])
            ->assertJsonFragment(['role' => 'Pendamping'])
            ->assertJsonFragment(['activity_name' => 'Registrasi peserta']);

        $this->assertDatabaseHas('activity_participants', [
            'lpj_id' => $lpj->id,
            'name' => 'Sakura Tanaka',
            'attendance_status' => 'hadir',
        ]);

        $this->assertDatabaseHas('activity_committees', [
            'lpj_id' => $lpj->id,
            'name' => 'User Lapangan',
            'role' => 'Pendamping',
        ]);

        $this->assertDatabaseHas('activity_schedules', [
            'lpj_id' => $lpj->id,
            'activity_name' => 'Registrasi peserta',
        ]);
    }

    public function test_user_can_upload_documentation_and_supporting_attachment(): void
    {
        Storage::fake('public');

        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S04-UPLOAD', Lpj::STATUS_AKTIF, $admin, $user);

        $documentationResponse = $this->actingAs($user)->post("/api/app/lpjs/{$lpj->id}/documentations", [
            'category' => ActivityDocumentation::CATEGORY_EXECUTION,
            'caption' => 'Dokumentasi sesi pembukaan.',
            'include_in_report' => true,
            'file' => UploadedFile::fake()->create('pembukaan.jpg', 10, 'image/jpeg'),
        ], [
            'Accept' => 'application/json',
        ]);

        $documentationResponse->assertCreated()
            ->assertJsonFragment([
                'caption' => 'Dokumentasi sesi pembukaan.',
                'include_in_report' => true,
            ]);

        $documentation = ActivityDocumentation::query()->where('lpj_id', $lpj->id)->firstOrFail();
        $this->assertSame('public', $documentation->file_disk);
        Storage::disk('public')->assertExists($documentation->file_path);

        $attachmentResponse = $this->actingAs($user)->post("/api/app/lpjs/{$lpj->id}/attachments", [
            'title' => 'Daftar Hadir Manual',
            'description' => 'Scan daftar hadir dari lokasi kegiatan.',
            'include_in_report' => true,
            'file' => UploadedFile::fake()->create('daftar-hadir.pdf', 15, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
        ]);

        $attachmentResponse->assertCreated()
            ->assertJsonFragment([
                'title' => 'Daftar Hadir Manual',
                'include_in_report' => true,
            ]);

        $attachment = ActivityAttachment::query()->where('lpj_id', $lpj->id)->firstOrFail();
        $this->assertSame('public', $attachment->file_disk);
        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_user_can_choose_activity_notes_to_include_in_report(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S04-NOTES', Lpj::STATUS_AKTIF, $admin, $user);

        $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}")->assertOk();

        $response = $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/activity-notes", [
            'notes' => [
                [
                    'type' => ActivityNote::TYPE_EVALUATION,
                    'content' => 'Peserta antusias dan alur registrasi perlu dibuat lebih cepat.',
                    'include_in_report' => true,
                ],
                [
                    'type' => ActivityNote::TYPE_OBSTACLE,
                    'content' => 'Cuaca hujan saat kepulangan.',
                    'include_in_report' => false,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'type' => ActivityNote::TYPE_EVALUATION,
                'include_in_report' => true,
            ]);

        $this->assertDatabaseHas('activity_notes', [
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'type' => ActivityNote::TYPE_EVALUATION,
            'include_in_report' => true,
        ]);
    }

    public function test_finished_lpj_is_read_only_for_execution_and_uploads(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S04-FINISH', Lpj::STATUS_FINISH, $admin, $user);

        $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}")
            ->assertOk()
            ->assertJsonPath('data.execution.can_edit_activity_data', false)
            ->assertJsonPath('data.execution.can_upload_documentation', false);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/execution-data", [
            'participants' => [
                ['name' => 'Tidak boleh tersimpan'],
            ],
        ])->assertForbidden();

        $this->actingAs($user)->post("/api/app/lpjs/{$lpj->id}/documentations", [
            'category' => ActivityDocumentation::CATEGORY_EXECUTION,
            'caption' => 'Tidak boleh upload',
            'file' => UploadedFile::fake()->create('locked.jpg', 10, 'image/jpeg'),
        ], [
            'Accept' => 'application/json',
        ])->assertForbidden();
    }

    public function test_slice04_does_not_change_transfer_or_final_output_guardrails(): void
    {
        [$admin, $user, $recipient] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S04-GUARD', Lpj::STATUS_AKTIF, $admin, $user, $recipient);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/execution-data", [
            'participants' => [
                ['name' => 'Peserta Guardrail'],
            ],
        ])->assertOk();

        $this->assertDatabaseMissing('lpj_financial_transactions', [
            'lpj_id' => $lpj->id,
            'type' => LpjFinancialTransaction::TYPE_EXPENSE,
        ]);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/final-pdf")->assertNotFound();
    }

    private function seedSliceData(): array
    {
        $this->seed([
            KicapUserSeeder::class,
            LpjTypeSeeder::class,
        ]);

        return [
            User::query()->where('email', 'admin@kicap.id')->firstOrFail(),
            User::query()->where('email', 'user@kicap.id')->firstOrFail(),
            User::query()->where('email', 'pendamping@kicap.id')->firstOrFail(),
        ];
    }

    private function makeAssignedLpj(string $code, string $status, User $admin, User $user, ?User $recipient = null): Lpj
    {
        $type = LpjType::query()->where('slug', 'kegiatan-internal')->firstOrFail();

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
        ]);

        foreach (array_filter([$user, $recipient]) as $assignedUser) {
            LpjAssignedUser::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $assignedUser->id,
                'role_label' => 'Petugas Lapangan',
                'can_input_transaction' => true,
                'can_upload_documentation' => true,
                'can_edit_activity_data' => true,
            ]);
        }

        return $lpj;
    }
}

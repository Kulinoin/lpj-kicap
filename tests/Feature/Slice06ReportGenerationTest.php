<?php

namespace Tests\Feature;

use App\Models\ActivityAttachment;
use App\Models\ActivityDocumentation;
use App\Models\ActivityNote;
use App\Models\Lpj;
use App\Models\LpjAdvanceClaim;
use App\Models\LpjAssignedUser;
use App\Models\LpjBalanceMutation;
use App\Models\LpjFinancialTransaction;
use App\Models\LpjReportSnapshot;
use App\Models\LpjType;
use App\Models\User;
use App\Services\LpjFinanceService;
use Database\Seeders\KicapUserSeeder;
use Database\Seeders\LpjTypeSeeder;
use Database\Seeders\OrganizationProfileSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Slice06ReportGenerationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_open_print_ready_lpj_for_finished_event_only(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $finished = $this->makeReportableLpj('LPJ-S06-FINAL', Lpj::STATUS_FINISH, $admin, $user);
        $active = $this->makeReportableLpj('LPJ-S06-ACTIVE', Lpj::STATUS_AKTIF, $admin, $user);

        $response = $this->actingAs($admin)->get(route('admin.lpjs.report.print', $finished));

        $response->assertOk()
            ->assertSee('Laporan Pertanggungjawaban')
            ->assertSee('Halaman Pengesahan')
            ->assertSee('Kegiatan Slice 06')
            ->assertSee('Dana talangan konsumsi peserta')
            ->assertSee('Rp 125.000')
            ->assertSee('Dokumentasi final masuk LPJ')
            ->assertDontSee('Transaksi ditolak internal')
            ->assertDontSee('Transfer saldo internal')
            ->assertDontSee('Klaim reimbursement internal')
            ->assertDontSee('Dokumentasi internal tidak masuk');

        $adminSnapshot = LpjReportSnapshot::query()
            ->where('lpj_id', $finished->id)
            ->where('source', LpjReportSnapshot::SOURCE_ADMIN_PRINT)
            ->firstOrFail();

        $this->assertSame('LPJ-S06-FINAL-V001', $adminSnapshot->snapshot_number);

        $this->actingAs($admin)
            ->get(route('admin.lpjs.report.print', $active))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.lpjs.report.print', $finished))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('app.lpjs.report.print', $finished))
            ->assertOk()
            ->assertSee('Kegiatan Slice 06')
            ->assertSee('Cetak');

        $userSnapshot = LpjReportSnapshot::query()
            ->where('lpj_id', $finished->id)
            ->where('source', LpjReportSnapshot::SOURCE_USER_PRINT)
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.lpj-report-snapshots.print', $userSnapshot))
            ->assertOk()
            ->assertSee('Kegiatan Slice 06')
            ->assertSee('Laporan Pertanggungjawaban');

        $this->actingAs($user)
            ->get(route('app.lpjs.report.print', $active))
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson("/api/app/lpjs/{$finished->id}")
            ->assertOk()
            ->assertJsonPath('data.can_print_report', true)
            ->assertJsonPath('data.report_print_url', route('app.lpjs.report.print', $finished));
    }

    public function test_admin_can_download_pdf_for_finished_lpj(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeReportableLpj('LPJ-S06-PDF', Lpj::STATUS_FINISH, $admin, $user);

        $response = $this->actingAs($admin)->get(route('admin.lpjs.report.pdf', $lpj));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="LPJ-LPJ-S06-PDF.pdf"');

        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertDatabaseHas('lpj_report_snapshots', [
            'lpj_id' => $lpj->id,
            'source' => LpjReportSnapshot::SOURCE_ADMIN_PDF,
            'snapshot_number' => 'LPJ-S06-PDF-V001',
        ]);
    }

    public function test_admin_report_snapshot_resource_route_is_registered(): void
    {
        [$admin] = $this->seedSliceData();

        $this->actingAs($admin)
            ->get('/admin/lpj-report-snapshots')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/storage-settings')
            ->assertOk();
    }

    private function seedSliceData(): array
    {
        $this->seed([
            KicapUserSeeder::class,
            LpjTypeSeeder::class,
            OrganizationProfileSeeder::class,
        ]);

        return [
            User::query()->where('email', 'admin@kicap.id')->firstOrFail(),
            User::query()->where('email', 'user@kicap.id')->firstOrFail(),
        ];
    }

    private function makeReportableLpj(string $code, string $status, User $admin, User $user): Lpj
    {
        $type = LpjType::query()->where('slug', 'kegiatan-internal')->firstOrFail();

        $lpj = Lpj::query()->create([
            'code' => $code,
            'title' => 'Kegiatan Slice 06',
            'lpj_type_id' => $type->id,
            'created_by' => $admin->id,
            'person_in_charge_id' => $user->id,
            'status' => $status,
            'completeness_status' => Lpj::COMPLETENESS_SIAP_FINALISASI,
            'start_date' => '2026-06-20',
            'end_date' => '2026-06-21',
            'location' => 'Jakarta',
            'funding_source' => 'Lembaga',
            'assignment_letter_number' => 'ST-06/KICAP/2026',
            'period_label' => '20-21 Juni 2026',
            'organization_role' => 'Lembaga berperan sebagai pelaksana kegiatan dan pengelola pertanggungjawaban.',
            'finalized_at' => $status === Lpj::STATUS_FINISH ? now() : null,
            'finalized_by' => $status === Lpj::STATUS_FINISH ? $admin->id : null,
        ]);

        LpjAssignedUser::query()->create([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'role_label' => 'Petugas Lapangan',
            'can_input_transaction' => true,
            'can_upload_documentation' => true,
            'can_edit_activity_data' => true,
        ]);

        $lpj->participants()->create([
            'created_by' => $user->id,
            'name' => 'Peserta LPJ',
            'origin' => 'Kelas Jepang',
            'participant_number' => 'P-001',
            'attendance_status' => 'hadir',
            'result_status' => 'Lulus administrasi',
        ]);

        $lpj->committees()->create([
            'created_by' => $user->id,
            'name' => 'Petugas Kicap',
            'role' => 'Pendamping',
            'task' => 'Mendampingi peserta dan dokumentasi.',
        ]);

        $lpj->schedules()->create([
            'created_by' => $user->id,
            'start_time' => '2026-06-20 09:00:00',
            'end_time' => '2026-06-20 11:00:00',
            'activity_name' => 'Registrasi dan pengarahan',
            'responsible_person' => 'Petugas Kicap',
            'sort_order' => 1,
        ]);

        $lpj->activityNotes()->create([
            'user_id' => $user->id,
            'type' => ActivityNote::TYPE_RESULT,
            'content' => 'Peserta hadir dan mengikuti arahan kegiatan.',
            'include_in_report' => true,
        ]);

        $lpj->activityNotes()->create([
            'user_id' => $user->id,
            'type' => ActivityNote::TYPE_OBSTACLE,
            'content' => 'Catatan internal tidak masuk LPJ',
            'include_in_report' => false,
        ]);

        ActivityDocumentation::query()->create([
            'lpj_id' => $lpj->id,
            'uploaded_by' => $user->id,
            'category' => ActivityDocumentation::CATEGORY_EXECUTION,
            'file_path' => 'activity-documentations/final.jpg',
            'original_name' => 'final.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'caption' => 'Dokumentasi final masuk LPJ',
            'include_in_report' => true,
            'sort_order' => 1,
        ]);

        ActivityDocumentation::query()->create([
            'lpj_id' => $lpj->id,
            'uploaded_by' => $user->id,
            'category' => ActivityDocumentation::CATEGORY_OTHER,
            'file_path' => 'activity-documentations/internal.jpg',
            'original_name' => 'internal.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'caption' => 'Dokumentasi internal tidak masuk',
            'include_in_report' => false,
            'sort_order' => 2,
        ]);

        ActivityAttachment::query()->create([
            'lpj_id' => $lpj->id,
            'uploaded_by' => $user->id,
            'title' => 'Daftar Hadir',
            'file_path' => 'activity-attachments/daftar-hadir.pdf',
            'original_name' => 'daftar-hadir.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'description' => 'Lampiran resmi kegiatan',
            'include_in_report' => true,
        ]);

        app(LpjFinanceService::class)->recordFundReceipt($lpj, $admin, [
            'source_name' => 'Lembaga',
            'description' => 'Dana kegiatan',
            'amount' => 500000,
            'received_at' => '2026-06-20',
        ]);

        $validAdvance = LpjFinancialTransaction::query()->create([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'type' => LpjFinancialTransaction::TYPE_EXPENSE,
            'source_type' => LpjFinancialTransaction::SOURCE_ADVANCE,
            'status' => LpjFinancialTransaction::STATUS_VALID,
            'category' => 'Konsumsi',
            'description' => 'Dana talangan konsumsi peserta',
            'amount' => 125000,
            'spent_at' => '2026-06-20',
            'proof_path' => 'transaction-proofs/konsumsi.pdf',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        LpjAdvanceClaim::query()->create([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'financial_transaction_id' => $validAdvance->id,
            'amount' => 125000,
            'status' => LpjAdvanceClaim::STATUS_PAID,
            'admin_note' => 'Klaim reimbursement internal',
        ]);

        LpjFinancialTransaction::query()->create([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'type' => LpjFinancialTransaction::TYPE_EXPENSE,
            'source_type' => LpjFinancialTransaction::SOURCE_BALANCE,
            'status' => LpjFinancialTransaction::STATUS_REJECTED,
            'category' => 'Transportasi',
            'description' => 'Transaksi ditolak internal',
            'amount' => 99000,
            'spent_at' => '2026-06-20',
            'no_proof_reason' => 'Ditolak oleh Admin',
        ]);

        LpjBalanceMutation::query()->create([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'created_by' => $user->id,
            'type' => LpjBalanceMutation::TYPE_TRANSFER_OUT,
            'direction' => LpjBalanceMutation::DIRECTION_OUT,
            'amount' => 50000,
            'balance_after' => 100000,
            'note' => 'Transfer saldo internal',
            'occurred_at' => now(),
        ]);

        app(\App\Services\LpjReviewService::class)->recalculateFinancialSummary($lpj);

        return $lpj->fresh();
    }
}

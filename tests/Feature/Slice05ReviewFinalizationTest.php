<?php

namespace Tests\Feature;

use App\Models\ActivityDocumentation;
use App\Models\ActivityNote;
use App\Models\Lpj;
use App\Models\LpjAssignedUser;
use App\Models\LpjFinancialTransaction;
use App\Models\LpjType;
use App\Models\LpjUserBalance;
use App\Models\User;
use App\Services\ActivityNoteService;
use App\Services\LpjFinanceService;
use App\Services\LpjReviewService;
use Database\Seeders\KicapUserSeeder;
use Database\Seeders\LpjTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Slice05ReviewFinalizationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_review_transactions_and_recalculate_valid_expense(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S05-REVIEW', Lpj::STATUS_AKTIF, $admin, $user);

        app(LpjFinanceService::class)->recordFundReceipt($lpj, $admin, [
            'source_name' => 'Lembaga',
            'amount' => 500000,
            'received_at' => '2026-06-20',
        ]);

        LpjUserBalance::query()->create(['lpj_id' => $lpj->id, 'user_id' => $user->id, 'balance' => 300000]);

        $transaction = app(LpjFinanceService::class)->createExpense($lpj->fresh(), $user, [
            'category' => 'Transportasi',
            'description' => 'Transportasi lokal',
            'amount' => 125000,
            'spent_at' => '2026-06-20',
            'no_proof_reason' => 'Tiket tidak diberikan vendor.',
        ], null);

        app(LpjReviewService::class)->reviewTransaction(
            $transaction,
            $admin,
            LpjFinancialTransaction::STATUS_VALID,
            'Alasan tanpa bukti diterima.'
        );

        $this->assertDatabaseHas('lpj_financial_transactions', [
            'id' => $transaction->id,
            'status' => LpjFinancialTransaction::STATUS_VALID,
            'reviewed_by' => $admin->id,
        ]);

        $lpj->refresh();
        $this->assertSame('125000.00', $lpj->total_valid_expense);
        $this->assertSame('375000.00', $lpj->total_remaining_fund);
    }

    public function test_user_can_submit_requested_transaction_revision(): void
    {
        Storage::fake('public');

        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S05-REVISION', Lpj::STATUS_AKTIF, $admin, $user);

        $transaction = LpjFinancialTransaction::query()->create([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'type' => LpjFinancialTransaction::TYPE_EXPENSE,
            'source_type' => LpjFinancialTransaction::SOURCE_ADVANCE,
            'status' => LpjFinancialTransaction::STATUS_NEEDS_REVISION,
            'category' => 'Konsumsi',
            'description' => 'Snack peserta',
            'amount' => 75000,
            'spent_at' => '2026-06-20',
            'admin_note' => 'Lengkapi bukti atau alasan.',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post("/api/app/lpjs/{$lpj->id}/financial-transactions/{$transaction->id}/revision", [
            'category' => 'Konsumsi',
            'description' => 'Snack peserta seleksi sesi pagi',
            'spent_at' => '2026-06-20',
            'proof' => UploadedFile::fake()->create('nota-snack.pdf', 12, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['status_label' => 'Perlu Review']);

        $transaction->refresh();
        $this->assertSame(LpjFinancialTransaction::STATUS_NEEDS_REVIEW, $transaction->status);
        $this->assertNull($transaction->reviewed_by);
        Storage::disk('public')->assertExists($transaction->proof_path);
    }

    public function test_event_can_be_submitted_reviewed_finalized_and_locked(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeCompleteLpj('LPJ-S05-FINAL', $admin, $user);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/submit-review")
            ->assertOk()
            ->assertJsonPath('data.completeness_status', Lpj::COMPLETENESS_SIAP_REVIEW);

        $review = app(LpjReviewService::class)->reviewPayload($lpj->fresh());
        $this->assertTrue($review['can_finalize']);

        $finalized = app(LpjReviewService::class)->finalize($lpj->fresh(), $admin);

        $this->assertSame(Lpj::STATUS_FINISH, $finalized->status);
        $this->assertSame(Lpj::COMPLETENESS_SIAP_FINALISASI, $finalized->completeness_status);
        $this->assertNotNull($finalized->finalized_at);

        $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}")
            ->assertOk()
            ->assertJsonPath('data.finance.can_input_finance', false)
            ->assertJsonPath('data.execution.can_edit_activity_data', false);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/expenses", [
            'category' => 'Transportasi',
            'description' => 'Tidak boleh masuk setelah finish',
            'amount' => 10000,
            'spent_at' => '2026-06-20',
            'no_proof_reason' => 'Event sudah selesai.',
        ])->assertForbidden();
    }

    public function test_event_cannot_be_finalized_with_pending_transaction_review(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeCompleteLpj('LPJ-S05-PENDING', $admin, $user, reviewTransaction: false);

        $review = app(LpjReviewService::class)->reviewPayload($lpj->fresh());

        $this->assertFalse($review['can_finalize']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(LpjReviewService::class)->finalize($lpj->fresh(), $admin);
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
        ];
    }

    private function makeAssignedLpj(string $code, string $status, User $admin, User $user): Lpj
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

    private function makeCompleteLpj(string $code, User $admin, User $user, bool $reviewTransaction = true): Lpj
    {
        $lpj = $this->makeAssignedLpj($code, Lpj::STATUS_AKTIF, $admin, $user);

        app(ActivityNoteService::class)->ensureForUser($lpj, $user);
        $lpj->activityNotes()->where('user_id', $user->id)->where('type', ActivityNote::TYPE_EVALUATION)->update([
            'content' => 'Pelaksanaan berjalan baik dan peserta hadir sesuai target.',
            'include_in_report' => true,
        ]);

        $lpj->participants()->create([
            'created_by' => $user->id,
            'name' => 'Peserta Finalisasi',
            'attendance_status' => 'hadir',
        ]);

        $lpj->committees()->create([
            'created_by' => $user->id,
            'name' => 'User Lapangan',
            'role' => 'Pendamping',
        ]);

        $lpj->schedules()->create([
            'created_by' => $user->id,
            'activity_name' => 'Registrasi dan briefing',
            'sort_order' => 1,
        ]);

        ActivityDocumentation::query()->create([
            'lpj_id' => $lpj->id,
            'uploaded_by' => $user->id,
            'category' => ActivityDocumentation::CATEGORY_EXECUTION,
            'file_path' => 'activity-documentations/final.jpg',
            'original_name' => 'final.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'caption' => 'Dokumentasi kegiatan',
            'include_in_report' => true,
        ]);

        app(LpjFinanceService::class)->recordFundReceipt($lpj, $admin, [
            'source_name' => 'Lembaga',
            'amount' => 500000,
            'received_at' => '2026-06-20',
        ]);

        $transaction = LpjFinancialTransaction::query()->create([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'type' => LpjFinancialTransaction::TYPE_EXPENSE,
            'source_type' => LpjFinancialTransaction::SOURCE_ADVANCE,
            'status' => LpjFinancialTransaction::STATUS_NEEDS_REVIEW,
            'category' => 'Dokumentasi',
            'description' => 'Cetak dokumentasi kegiatan',
            'amount' => 100000,
            'spent_at' => '2026-06-20',
            'no_proof_reason' => 'Bukti digabung dalam lampiran dokumentasi.',
        ]);

        if ($reviewTransaction) {
            app(LpjReviewService::class)->reviewTransaction($transaction, $admin, LpjFinancialTransaction::STATUS_VALID);
        }

        return $lpj->fresh();
    }
}

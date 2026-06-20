<?php

namespace Tests\Feature;

use App\Models\Lpj;
use App\Models\LpjAdvanceClaim;
use App\Models\LpjAssignedUser;
use App\Models\LpjBalanceMutation;
use App\Models\LpjFinancialTransaction;
use App\Models\LpjType;
use App\Models\LpjUserBalance;
use App\Models\User;
use App\Services\LpjFinanceService;
use Database\Seeders\KicapUserSeeder;
use Database\Seeders\LpjTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Slice03OperationalFinanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_record_lpj_fund_receipt_and_grant_user_balance(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S03-FUND', Lpj::STATUS_AKTIF, $admin, $user);

        $service = app(LpjFinanceService::class);

        $service->recordFundReceipt($lpj, $admin, [
            'source_name' => 'Lembaga',
            'amount' => 1000000,
            'received_at' => '2026-06-20',
        ]);

        $service->grantUserFund($lpj->fresh(), $user, $admin, [
            'amount' => 600000,
            'note' => 'Dana pegangan awal',
        ]);

        $this->assertDatabaseHas('lpj_fund_receipts', [
            'lpj_id' => $lpj->id,
            'source_name' => 'Lembaga',
            'amount' => 1000000,
        ]);

        $this->assertDatabaseHas('lpj_user_balances', [
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'balance' => 600000,
        ]);

        $this->assertDatabaseHas('lpj_balance_mutations', [
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'type' => LpjBalanceMutation::TYPE_FUND_IN,
            'amount' => 600000,
        ]);

        $lpj->refresh();
        $this->assertSame('1000000.00', $lpj->total_funds_received);
        $this->assertSame('1000000.00', $lpj->total_remaining_fund);
        $this->assertSame(600000.0, $lpj->allocatedUserFundTotal());
        $this->assertSame(400000.0, $lpj->remainingAllocationFund());

        $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}")
            ->assertOk()
            ->assertJsonPath('data.finance.allocated_fund', 600000)
            ->assertJsonPath('data.finance.remaining_allocation', 400000);
    }

    public function test_admin_cannot_allocate_user_funds_above_event_fund_receipts(): void
    {
        [$admin, $user, $recipient] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S03-ALLOC-LIMIT', Lpj::STATUS_AKTIF, $admin, $user, $recipient);

        $service = app(LpjFinanceService::class);

        $service->recordFundReceipt($lpj, $admin, [
            'source_name' => 'Lembaga',
            'amount' => 500000,
            'received_at' => '2026-06-20',
        ]);

        $service->grantUserFund($lpj->fresh(), $user, $admin, [
            'amount' => 350000,
            'note' => 'Dana pegangan awal',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $service->grantUserFund($lpj->fresh(), $recipient, $admin, [
            'amount' => 200000,
            'note' => 'Melewati plafon dana masuk',
        ]);
    }

    public function test_user_can_see_balance_and_record_expense_with_proof(): void
    {
        Storage::fake('public');

        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S03-EXPENSE', Lpj::STATUS_AKTIF, $admin, $user);
        LpjUserBalance::query()->create(['lpj_id' => $lpj->id, 'user_id' => $user->id, 'balance' => 500000]);

        $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}")
            ->assertOk()
            ->assertJsonPath('data.finance.balance', 500000)
            ->assertJsonPath('data.finance.can_input_finance', true)
            ->assertJsonFragment(['finance_category_options' => [
                'Konsumsi',
                'Akomodasi',
                'Operasional',
                'Transportasi',
                'Dokumentasi',
                'Lainnya',
            ]]);

        $response = $this->actingAs($user)->post("/api/app/lpjs/{$lpj->id}/expenses", [
            'category' => 'Transportasi',
            'description' => 'Taksi menuju lokasi kegiatan',
            'amount' => 125000,
            'spent_at' => '2026-06-20',
            'proof' => UploadedFile::fake()->create('nota.pdf', 10, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.finance.balance', 375000)
            ->assertJsonFragment(['status_label' => 'Perlu Review']);

        $transaction = LpjFinancialTransaction::query()->where('lpj_id', $lpj->id)->firstOrFail();

        Storage::disk('public')->assertExists($transaction->proof_path);

        $this->assertDatabaseHas('lpj_balance_mutations', [
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'financial_transaction_id' => $transaction->id,
            'type' => LpjBalanceMutation::TYPE_EXPENSE,
            'amount' => 125000,
            'balance_after' => 375000,
        ]);
    }

    public function test_user_transfer_balance_moves_money_immediately_without_lpj_expense(): void
    {
        [$admin, $user, $recipient] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S03-TRANSFER', Lpj::STATUS_AKTIF, $admin, $user, $recipient);

        LpjUserBalance::query()->create(['lpj_id' => $lpj->id, 'user_id' => $user->id, 'balance' => 400000]);
        LpjUserBalance::query()->create(['lpj_id' => $lpj->id, 'user_id' => $recipient->id, 'balance' => 100000]);

        $response = $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/balance-transfers", [
            'recipient_user_id' => $recipient->id,
            'amount' => 150000,
            'note' => 'Uang makan tim pendamping',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.finance.balance', 250000);

        $this->assertDatabaseHas('lpj_user_balances', [
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'balance' => 250000,
        ]);

        $this->assertDatabaseHas('lpj_user_balances', [
            'lpj_id' => $lpj->id,
            'user_id' => $recipient->id,
            'balance' => 250000,
        ]);

        $this->assertDatabaseHas('lpj_balance_mutations', [
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'related_user_id' => $recipient->id,
            'type' => LpjBalanceMutation::TYPE_TRANSFER_OUT,
            'amount' => 150000,
        ]);

        $this->assertDatabaseHas('lpj_balance_mutations', [
            'lpj_id' => $lpj->id,
            'user_id' => $recipient->id,
            'related_user_id' => $user->id,
            'type' => LpjBalanceMutation::TYPE_TRANSFER_IN,
            'amount' => 150000,
        ]);

        $this->assertDatabaseMissing('lpj_financial_transactions', [
            'lpj_id' => $lpj->id,
        ]);
        $this->assertSame('0.00', $lpj->fresh()->total_valid_expense);
    }

    public function test_user_cannot_transfer_more_than_available_balance(): void
    {
        [$admin, $user, $recipient] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S03-LIMIT', Lpj::STATUS_AKTIF, $admin, $user, $recipient);

        LpjUserBalance::query()->create(['lpj_id' => $lpj->id, 'user_id' => $user->id, 'balance' => 50000]);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/balance-transfers", [
            'recipient_user_id' => $recipient->id,
            'amount' => 75000,
            'note' => 'Harus ditolak',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('amount');
    }

    public function test_user_can_record_advance_expense_and_internal_claim_without_reducing_balance(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S03-ADVANCE', Lpj::STATUS_AKTIF, $admin, $user);
        LpjUserBalance::query()->create(['lpj_id' => $lpj->id, 'user_id' => $user->id, 'balance' => 200000]);

        $response = $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/advance-expenses", [
            'category' => 'Konsumsi',
            'description' => 'Snack peserta dibayar dulu oleh petugas',
            'amount' => 85000,
            'spent_at' => '2026-06-20',
            'no_proof_reason' => 'Nota menyusul dari vendor.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.finance.balance', 200000);

        $transaction = LpjFinancialTransaction::query()->where('lpj_id', $lpj->id)->firstOrFail();

        $this->assertSame(LpjFinancialTransaction::SOURCE_ADVANCE, $transaction->source_type);
        $this->assertDatabaseHas('lpj_advance_claims', [
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
            'financial_transaction_id' => $transaction->id,
            'amount' => 85000,
            'status' => LpjAdvanceClaim::STATUS_SUBMITTED,
        ]);
    }

    public function test_finished_lpj_is_read_only_for_finance_input(): void
    {
        [$admin, $user] = $this->seedSliceData();
        $lpj = $this->makeAssignedLpj('LPJ-S03-FINISH', Lpj::STATUS_FINISH, $admin, $user);

        $this->actingAs($user)->getJson("/api/app/lpjs/{$lpj->id}")
            ->assertOk()
            ->assertJsonPath('data.finance.can_input_finance', false);

        $this->actingAs($user)->postJson("/api/app/lpjs/{$lpj->id}/expenses", [
            'category' => 'Transportasi',
            'description' => 'Tidak boleh tersimpan',
            'amount' => 50000,
            'spent_at' => '2026-06-20',
            'no_proof_reason' => 'LPJ sudah selesai.',
        ])->assertForbidden();
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

<?php

namespace App\Services;

use App\Models\Lpj;
use App\Models\LpjAdvanceClaim;
use App\Models\LpjBalanceMutation;
use App\Models\LpjFinancialTransaction;
use App\Models\LpjFundReceipt;
use App\Models\LpjUserBalance;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LpjFinanceService
{
    public function recordFundReceipt(Lpj $lpj, User $admin, array $data): LpjFundReceipt
    {
        return DB::transaction(function () use ($lpj, $admin, $data): LpjFundReceipt {
            $receipt = LpjFundReceipt::query()->create([
                'lpj_id' => $lpj->id,
                'recorded_by' => $admin->id,
                'source_name' => $data['source_name'],
                'description' => $data['description'] ?? null,
                'amount' => $this->normalizeAmount($data['amount']),
                'received_at' => $data['received_at'],
            ]);

            $lpj->forceFill([
                'total_funds_received' => $lpj->total_funds_received + $receipt->amount,
                'total_remaining_fund' => $lpj->total_remaining_fund + $receipt->amount,
            ])->save();

            return $receipt;
        });
    }

    public function grantUserFund(Lpj $lpj, User $user, User $admin, array $data): LpjBalanceMutation
    {
        $this->ensureAssignedUser($lpj, $user);

        return DB::transaction(function () use ($lpj, $user, $admin, $data): LpjBalanceMutation {
            $balance = $this->lockedBalance($lpj, $user);
            $amount = $this->normalizeAmount($data['amount']);
            $balance->balance = $this->add($balance->balance, $amount);
            $balance->save();

            return LpjBalanceMutation::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
                'created_by' => $admin->id,
                'type' => LpjBalanceMutation::TYPE_FUND_IN,
                'direction' => LpjBalanceMutation::DIRECTION_IN,
                'amount' => $amount,
                'balance_after' => $balance->balance,
                'note' => $data['note'] ?? null,
                'occurred_at' => now(),
            ]);
        });
    }

    public function createExpense(Lpj $lpj, User $user, array $data, ?UploadedFile $proof): LpjFinancialTransaction
    {
        $this->ensureUserCanInputFinance($lpj, $user);

        return DB::transaction(function () use ($lpj, $user, $data, $proof): LpjFinancialTransaction {
            $amount = $this->normalizeAmount($data['amount']);
            $balance = $this->lockedBalance($lpj, $user);

            if ($this->compare($balance->balance, $amount) < 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo pegangan tidak cukup.',
                ]);
            }

            $transaction = LpjFinancialTransaction::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
                'type' => LpjFinancialTransaction::TYPE_EXPENSE,
                'source_type' => LpjFinancialTransaction::SOURCE_BALANCE,
                'status' => LpjFinancialTransaction::STATUS_NEEDS_REVIEW,
                'category' => $data['category'],
                'description' => $data['description'],
                'amount' => $amount,
                'spent_at' => $data['spent_at'],
                'proof_path' => $this->storeProof($proof),
                'no_proof_reason' => $data['no_proof_reason'] ?? null,
            ]);

            $balance->balance = $this->subtract($balance->balance, $amount);
            $balance->save();

            LpjBalanceMutation::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
                'financial_transaction_id' => $transaction->id,
                'created_by' => $user->id,
                'type' => LpjBalanceMutation::TYPE_EXPENSE,
                'direction' => LpjBalanceMutation::DIRECTION_OUT,
                'amount' => $amount,
                'balance_after' => $balance->balance,
                'note' => $data['description'],
                'occurred_at' => now(),
            ]);

            return $transaction;
        });
    }

    public function createAdvanceExpense(Lpj $lpj, User $user, array $data, ?UploadedFile $proof): LpjFinancialTransaction
    {
        $this->ensureUserCanInputFinance($lpj, $user);

        return DB::transaction(function () use ($lpj, $user, $data, $proof): LpjFinancialTransaction {
            $amount = $this->normalizeAmount($data['amount']);

            $transaction = LpjFinancialTransaction::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
                'type' => LpjFinancialTransaction::TYPE_EXPENSE,
                'source_type' => LpjFinancialTransaction::SOURCE_ADVANCE,
                'status' => LpjFinancialTransaction::STATUS_NEEDS_REVIEW,
                'category' => $data['category'],
                'description' => $data['description'],
                'amount' => $amount,
                'spent_at' => $data['spent_at'],
                'proof_path' => $this->storeProof($proof),
                'no_proof_reason' => $data['no_proof_reason'] ?? null,
            ]);

            LpjAdvanceClaim::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
                'financial_transaction_id' => $transaction->id,
                'amount' => $amount,
                'status' => LpjAdvanceClaim::STATUS_SUBMITTED,
            ]);

            return $transaction;
        });
    }

    public function transferBalance(Lpj $lpj, User $sender, User $recipient, array $data): array
    {
        $this->ensureUserCanInputFinance($lpj, $sender);

        if (! $sender->can_transfer_balance) {
            abort(403, 'User tidak memiliki izin transfer saldo.');
        }

        $this->ensureAssignedUser($lpj, $recipient);

        if ($sender->is($recipient)) {
            throw ValidationException::withMessages([
                'recipient_user_id' => 'Penerima transfer harus berbeda.',
            ]);
        }

        return DB::transaction(function () use ($lpj, $sender, $recipient, $data): array {
            $amount = $this->normalizeAmount($data['amount']);
            $senderBalance = $this->lockedBalance($lpj, $sender);
            $recipientBalance = $this->lockedBalance($lpj, $recipient);

            if ($this->compare($senderBalance->balance, $amount) < 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo pegangan tidak cukup.',
                ]);
            }

            $reference = (string) Str::uuid();

            $senderBalance->balance = $this->subtract($senderBalance->balance, $amount);
            $senderBalance->save();

            $out = LpjBalanceMutation::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $sender->id,
                'related_user_id' => $recipient->id,
                'created_by' => $sender->id,
                'type' => LpjBalanceMutation::TYPE_TRANSFER_OUT,
                'direction' => LpjBalanceMutation::DIRECTION_OUT,
                'amount' => $amount,
                'balance_after' => $senderBalance->balance,
                'transfer_reference' => $reference,
                'note' => $data['note'] ?? null,
                'occurred_at' => now(),
            ]);

            $recipientBalance->balance = $this->add($recipientBalance->balance, $amount);
            $recipientBalance->save();

            $in = LpjBalanceMutation::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $recipient->id,
                'related_user_id' => $sender->id,
                'created_by' => $sender->id,
                'type' => LpjBalanceMutation::TYPE_TRANSFER_IN,
                'direction' => LpjBalanceMutation::DIRECTION_IN,
                'amount' => $amount,
                'balance_after' => $recipientBalance->balance,
                'transfer_reference' => $reference,
                'note' => $data['note'] ?? null,
                'occurred_at' => now(),
            ]);

            return [$out, $in];
        });
    }

    public function financePayload(Lpj $lpj, User $user): array
    {
        $assignment = $lpj->assignedUsers()
            ->where('user_id', $user->id)
            ->first();

        $balance = LpjUserBalance::query()
            ->firstOrCreate(['lpj_id' => $lpj->id, 'user_id' => $user->id], ['balance' => 0]);

        $transactions = LpjFinancialTransaction::query()
            ->where('lpj_id', $lpj->id)
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (LpjFinancialTransaction $transaction): array => [
                'id' => $transaction->id,
                'category' => $transaction->category,
                'description' => $transaction->description,
                'amount' => (float) $transaction->amount,
                'source_type' => $transaction->source_type,
                'source_label' => LpjFinancialTransaction::sourceLabels()[$transaction->source_type] ?? $transaction->source_type,
                'status' => $transaction->status,
                'status_label' => LpjFinancialTransaction::statusLabels()[$transaction->status] ?? $transaction->status,
                'spent_at' => $transaction->spent_at?->toDateString(),
                'has_proof' => filled($transaction->proof_path),
                'no_proof_reason' => $transaction->no_proof_reason,
                'admin_note' => $transaction->admin_note,
                'can_submit_revision' => $lpj->status === Lpj::STATUS_AKTIF && in_array($transaction->status, [
                    LpjFinancialTransaction::STATUS_NEEDS_REVISION,
                    LpjFinancialTransaction::STATUS_WAITING_PROOF,
                ], true),
            ]);

        $claims = LpjAdvanceClaim::query()
            ->where('lpj_id', $lpj->id)
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (LpjAdvanceClaim $claim): array => [
                'id' => $claim->id,
                'amount' => (float) $claim->amount,
                'status' => $claim->status,
                'status_label' => LpjAdvanceClaim::statusLabels()[$claim->status] ?? $claim->status,
            ]);

        $transferTargets = $lpj->assignedUsers()
            ->with('user:id,name')
            ->where('user_id', '!=', $user->id)
            ->get()
            ->map(fn ($assignment): array => [
                'id' => $assignment->user->id,
                'name' => $assignment->user->name,
            ])
            ->values();

        return [
            'balance' => (float) $balance->balance,
            'can_input_finance' => $lpj->status === Lpj::STATUS_AKTIF && (bool) $assignment?->can_input_transaction,
            'can_transfer_balance' => (bool) $user->can_transfer_balance,
            'transfer_targets' => $transferTargets,
            'transactions' => $transactions,
            'advance_claims' => $claims,
        ];
    }

    private function ensureUserCanInputFinance(Lpj $lpj, User $user): void
    {
        abort_unless($lpj->status === Lpj::STATUS_AKTIF, 403);

        $assignment = $lpj->assignedUsers()
            ->where('user_id', $user->id)
            ->first();

        abort_unless($assignment && $assignment->can_input_transaction, 403);
    }

    private function ensureAssignedUser(Lpj $lpj, User $user): void
    {
        abort_unless($user->isUser() && $user->is_active, 403);
        abort_unless($lpj->assignedUsers()->where('user_id', $user->id)->exists(), 422);
    }

    private function lockedBalance(Lpj $lpj, User $user): LpjUserBalance
    {
        LpjUserBalance::query()->firstOrCreate([
            'lpj_id' => $lpj->id,
            'user_id' => $user->id,
        ], [
            'balance' => 0,
        ]);

        return LpjUserBalance::query()
            ->where('lpj_id', $lpj->id)
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function normalizeAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function add(mixed $left, mixed $right): string
    {
        return number_format(((float) $left) + ((float) $right), 2, '.', '');
    }

    private function subtract(mixed $left, mixed $right): string
    {
        return number_format(((float) $left) - ((float) $right), 2, '.', '');
    }

    private function compare(mixed $left, mixed $right): int
    {
        return ((float) $left) <=> ((float) $right);
    }

    private function storeProof(?UploadedFile $proof): ?string
    {
        return $proof?->store('transaction-proofs', 'public');
    }
}

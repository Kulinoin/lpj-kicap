<?php

namespace App\Services;

use App\Models\Lpj;
use App\Models\LpjFinancialTransaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LpjReviewService
{
    public function submitForReview(Lpj $lpj, User $user): Lpj
    {
        abort_unless($lpj->status === Lpj::STATUS_AKTIF, 403);
        abort_unless($lpj->assignedUsers()->where('user_id', $user->id)->exists(), 403);

        $lpj->forceFill([
            'completeness_status' => Lpj::COMPLETENESS_SIAP_REVIEW,
            'submitted_at' => $lpj->submitted_at ?? now(),
        ])->save();

        return $lpj->fresh();
    }

    public function reviewTransaction(LpjFinancialTransaction $transaction, User $admin, string $status, ?string $adminNote = null): LpjFinancialTransaction
    {
        $allowedStatuses = [
            LpjFinancialTransaction::STATUS_VALID,
            LpjFinancialTransaction::STATUS_REJECTED,
            LpjFinancialTransaction::STATUS_NEEDS_REVISION,
            LpjFinancialTransaction::STATUS_WAITING_PROOF,
        ];

        if (! in_array($status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Status review transaksi tidak valid.',
            ]);
        }

        if (
            $status === LpjFinancialTransaction::STATUS_VALID
            && blank($transaction->proof_path)
            && blank($transaction->no_proof_reason)
        ) {
            throw ValidationException::withMessages([
                'status' => 'Transaksi tanpa bukti wajib memiliki alasan sebelum divalidasi.',
            ]);
        }

        if (
            in_array($status, [LpjFinancialTransaction::STATUS_NEEDS_REVISION, LpjFinancialTransaction::STATUS_WAITING_PROOF], true)
            && blank($adminNote)
        ) {
            throw ValidationException::withMessages([
                'admin_note' => 'Catatan Admin wajib diisi untuk permintaan revisi.',
            ]);
        }

        return DB::transaction(function () use ($transaction, $admin, $status, $adminNote): LpjFinancialTransaction {
            $transaction->forceFill([
                'status' => $status,
                'admin_note' => $adminNote,
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
            ])->save();

            $this->recalculateFinancialSummary($transaction->lpj()->lockForUpdate()->firstOrFail());

            return $transaction->fresh();
        });
    }

    public function submitTransactionRevision(
        Lpj $lpj,
        LpjFinancialTransaction $transaction,
        User $user,
        array $data,
        ?UploadedFile $proof
    ): LpjFinancialTransaction {
        abort_unless($lpj->status === Lpj::STATUS_AKTIF, 403);
        abort_unless($transaction->lpj_id === $lpj->id && $transaction->user_id === $user->id, 404);
        abort_unless(in_array($transaction->status, [
            LpjFinancialTransaction::STATUS_NEEDS_REVISION,
            LpjFinancialTransaction::STATUS_WAITING_PROOF,
        ], true), 403);

        return DB::transaction(function () use ($transaction, $data, $proof): LpjFinancialTransaction {
            $payload = [
                'category' => $data['category'],
                'description' => $data['description'],
                'spent_at' => $data['spent_at'],
                'no_proof_reason' => $data['no_proof_reason'] ?? null,
                'status' => LpjFinancialTransaction::STATUS_NEEDS_REVIEW,
                'reviewed_at' => null,
                'reviewed_by' => null,
            ];

            if ($proof) {
                if ($transaction->proof_path) {
                    Storage::disk('public')->delete($transaction->proof_path);
                }

                $payload['proof_path'] = $proof->store('transaction-proofs', 'public');
            }

            $transaction->forceFill($payload)->save();

            return $transaction->fresh();
        });
    }

    public function finalize(Lpj $lpj, User $admin): Lpj
    {
        if ($lpj->status === Lpj::STATUS_FINISH) {
            return $lpj->fresh();
        }

        $review = $this->reviewPayload($lpj);

        if (! $review['can_finalize']) {
            throw ValidationException::withMessages([
                'lpj' => 'Event belum memenuhi checklist finalisasi.',
            ]);
        }

        return DB::transaction(function () use ($lpj, $admin): Lpj {
            $locked = Lpj::query()->lockForUpdate()->findOrFail($lpj->id);
            $this->recalculateFinancialSummary($locked);

            $locked->forceFill([
                'status' => Lpj::STATUS_FINISH,
                'completeness_status' => Lpj::COMPLETENESS_SIAP_FINALISASI,
                'submitted_at' => $locked->submitted_at ?? now(),
                'approved_at' => $locked->approved_at ?? now(),
                'approved_by' => $locked->approved_by ?? $admin->id,
                'finalized_at' => now(),
                'finalized_by' => $admin->id,
            ])->save();

            return $locked->fresh();
        });
    }

    public function reviewPayload(Lpj $lpj): array
    {
        $lpj->loadMissing(['type', 'assignedUsers', 'activityNotes', 'participants', 'committees', 'schedules', 'documentations', 'attachments']);

        $lpj = $this->recalculateFinancialSummary($lpj);

        $pendingTransactionCount = $lpj->financialTransactions()
            ->whereIn('status', [
                LpjFinancialTransaction::STATUS_NEEDS_REVIEW,
                LpjFinancialTransaction::STATUS_WAITING_PROOF,
                LpjFinancialTransaction::STATUS_NEEDS_REVISION,
            ])
            ->count();

        $transactionsWithoutProofReason = $lpj->financialTransactions()
            ->whereNull('proof_path')
            ->where(function ($query): void {
                $query->whereNull('no_proof_reason')
                    ->orWhere('no_proof_reason', '');
            })
            ->count();

        $validExpense = (float) $lpj->total_valid_expense;
        $fundsReceived = (float) $lpj->total_funds_received;
        $remainingFund = (float) $lpj->total_remaining_fund;
        $totalUserBalance = (float) $lpj->userBalances()->sum('balance');

        $items = [
            [
                'key' => 'basic_event_data',
                'label' => 'Data dasar event/kegiatan',
                'passed' => filled($lpj->title)
                    && filled($lpj->type)
                    && filled($lpj->start_date)
                    && filled($lpj->end_date)
                    && filled($lpj->location)
                    && $lpj->assignedUsers->isNotEmpty(),
                'note' => 'Judul, tipe, tanggal, lokasi, dan user ditugaskan wajib tersedia.',
            ],
            [
                'key' => 'execution_data',
                'label' => 'Data pelaksanaan',
                'passed' => $lpj->participants->isNotEmpty()
                    && $lpj->committees->isNotEmpty()
                    && $lpj->schedules->isNotEmpty(),
                'note' => 'Peserta, panitia/pendamping, dan rundown harus terisi.',
            ],
            [
                'key' => 'report_notes',
                'label' => 'Catatan masuk LPJ',
                'passed' => $lpj->activityNotes
                    ->where('include_in_report', true)
                    ->filter(fn ($note): bool => filled($note->content))
                    ->isNotEmpty(),
                'note' => 'Minimal satu catatan operasional/evaluasi ditandai Masuk LPJ.',
            ],
            [
                'key' => 'report_files',
                'label' => 'Dokumentasi atau lampiran LPJ',
                'passed' => $lpj->documentations->where('include_in_report', true)->isNotEmpty()
                    || $lpj->attachments->where('include_in_report', true)->isNotEmpty(),
                'note' => 'Minimal satu dokumentasi atau lampiran ditandai Masuk LPJ.',
            ],
            [
                'key' => 'transaction_review',
                'label' => 'Review transaksi selesai',
                'passed' => $pendingTransactionCount === 0,
                'note' => "{$pendingTransactionCount} transaksi masih perlu review/revisi.",
            ],
            [
                'key' => 'no_proof_reason',
                'label' => 'Transaksi tanpa bukti punya alasan',
                'passed' => $transactionsWithoutProofReason === 0,
                'note' => "{$transactionsWithoutProofReason} transaksi tanpa bukti belum punya alasan.",
            ],
            [
                'key' => 'simple_reconciliation',
                'label' => 'Rekonsiliasi saldo sederhana',
                'passed' => $fundsReceived >= $validExpense && $remainingFund >= 0,
                'note' => 'Dana diterima harus cukup untuk total pengeluaran valid.',
            ],
        ];

        return [
            'lpj_id' => $lpj->id,
            'status' => $lpj->status,
            'completeness_status' => $lpj->completeness_status,
            'items' => $items,
            'can_finalize' => collect($items)->every(fn (array $item): bool => $item['passed']),
            'summary' => [
                'funds_received' => $fundsReceived,
                'valid_expense' => $validExpense,
                'remaining_fund' => $remainingFund,
                'user_balance_total' => $totalUserBalance,
                'pending_transaction_count' => $pendingTransactionCount,
                'transactions_without_proof_reason' => $transactionsWithoutProofReason,
            ],
        ];
    }

    public function recalculateFinancialSummary(Lpj $lpj): Lpj
    {
        $validExpense = (float) $lpj->financialTransactions()
            ->where('status', LpjFinancialTransaction::STATUS_VALID)
            ->sum('amount');

        $fundsReceived = (float) $lpj->fundReceipts()->sum('amount');

        if ($fundsReceived <= 0) {
            $fundsReceived = (float) $lpj->total_funds_received;
        }

        $lpj->forceFill([
            'total_funds_received' => number_format($fundsReceived, 2, '.', ''),
            'total_valid_expense' => number_format($validExpense, 2, '.', ''),
            'total_remaining_fund' => number_format($fundsReceived - $validExpense, 2, '.', ''),
        ])->save();

        return $lpj->fresh();
    }
}

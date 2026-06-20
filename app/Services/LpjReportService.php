<?php

namespace App\Services;

use App\Models\ActivityDocumentation;
use App\Models\ActivityNote;
use App\Models\Lpj;
use App\Models\LpjFinancialTransaction;
use App\Models\LpjReportSnapshot;
use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LpjReportService
{
    public function reportData(Lpj $lpj): array
    {
        $this->ensureFinal($lpj);

        $lpj = app(LpjReviewService::class)->recalculateFinancialSummary($lpj);

        $lpj->loadMissing([
            'type',
            'creator',
            'personInCharge',
            'participants',
            'committees',
            'schedules',
            'activityNotes.user',
            'documentations',
            'attachments',
            'fundReceipts.recorder',
            'financialTransactions.user',
            'financialTransactions.reviewer',
        ]);

        $transactions = $lpj->financialTransactions
            ->where('status', LpjFinancialTransaction::STATUS_VALID)
            ->sortBy(fn (LpjFinancialTransaction $transaction): string => $transaction->spent_at?->toDateString().'-'.str_pad((string) $transaction->id, 8, '0', STR_PAD_LEFT))
            ->values();

        $documentations = $lpj->documentations
            ->where('include_in_report', true)
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $attachments = $lpj->attachments
            ->where('include_in_report', true)
            ->sortBy('id')
            ->values();

        $notes = $lpj->activityNotes
            ->where('include_in_report', true)
            ->filter(fn (ActivityNote $note): bool => filled($note->content))
            ->groupBy('type');

        $organization = OrganizationProfile::query()->first();

        return [
            'lpj' => $lpj,
            'organization' => $organization,
            'approval' => $this->approvalData($lpj, $organization),
            'participants' => $lpj->participants->values(),
            'committees' => $lpj->committees->values(),
            'schedules' => $lpj->schedules->sortBy([
                ['sort_order', 'asc'],
                ['start_time', 'asc'],
                ['id', 'asc'],
            ])->values(),
            'notes' => $notes,
            'fund_receipts' => $lpj->fundReceipts->sortBy('received_at')->values(),
            'transactions' => $transactions,
            'category_totals' => $this->categoryTotals($transactions),
            'documentations' => $documentations,
            'attachments' => $attachments,
            'proof_attachments' => $transactions->filter(fn (LpjFinancialTransaction $transaction): bool => filled($transaction->proof_path))->values(),
            'generated_at' => now(),
        ];
    }

    public function ensureFinal(Lpj $lpj): void
    {
        if ($lpj->status !== Lpj::STATUS_FINISH) {
            throw ValidationException::withMessages([
                'lpj' => 'Dokumen LPJ final hanya dapat dibuat untuk event/kegiatan yang sudah selesai.',
            ]);
        }
    }

    public function createSnapshot(Lpj $lpj, ?User $generator, string $source, string $html): LpjReportSnapshot
    {
        $this->ensureFinal($lpj);

        return DB::transaction(function () use ($lpj, $generator, $source, $html): LpjReportSnapshot {
            $locked = Lpj::query()->lockForUpdate()->findOrFail($lpj->id);
            $version = ((int) $locked->reportSnapshots()->max('version')) + 1;

            return LpjReportSnapshot::query()->create([
                'lpj_id' => $locked->id,
                'generated_by' => $generator?->id,
                'version' => $version,
                'snapshot_number' => sprintf('%s-V%03d', $locked->code, $version),
                'source' => $source,
                'generated_by_role' => $generator?->role,
                'total_funds_received' => $locked->total_funds_received,
                'total_valid_expense' => $locked->total_valid_expense,
                'total_remaining_fund' => $locked->total_remaining_fund,
                'snapshot_html' => $html,
                'generated_at' => now(),
            ]);
        });
    }

    public function publicFileUrl(?string $path, ?string $disk = null): ?string
    {
        return app(AppFileStorageService::class)->url($path, $disk);
    }

    public function publicFilePath(?string $path, ?string $disk = null): ?string
    {
        return app(AppFileStorageService::class)->path($path, $disk);
    }

    private function approvalData(Lpj $lpj, ?OrganizationProfile $organization): array
    {
        return [
            'made_by' => $lpj->personInCharge?->name ?? $lpj->creator?->name ?? '-',
            'checked_by' => $lpj->creator?->name ?? '-',
            'approved_by' => $organization?->leader_name ?: '[Diisi kemudian]',
            'approved_position' => $organization?->leader_position ?: '[Diisi kemudian]',
            'city' => $organization?->default_city ?: '[Diisi kemudian]',
            'date' => $lpj->finalized_at ?? now(),
        ];
    }

    private function categoryTotals(Collection $transactions): Collection
    {
        return $transactions
            ->groupBy('category')
            ->map(fn (Collection $items, string $category): array => [
                'category' => $category,
                'amount' => $items->sum(fn (LpjFinancialTransaction $transaction): float => (float) $transaction->amount),
            ])
            ->values();
    }
}

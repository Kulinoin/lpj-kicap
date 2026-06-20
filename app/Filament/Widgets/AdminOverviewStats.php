<?php

namespace App\Filament\Widgets;

use App\Models\Lpj;
use App\Models\LpjReportSnapshot;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Operasional';

    protected ?string $description = 'Pantau event, dana, petugas, dan dokumen LPJ final dari satu layar.';

    protected function getStats(): array
    {
        $activeEvents = Lpj::query()->where('status', Lpj::STATUS_AKTIF)->count();
        $finishedEvents = Lpj::query()->where('status', Lpj::STATUS_FINISH)->count();
        $reviewEvents = Lpj::query()
            ->whereIn('completeness_status', [
                Lpj::COMPLETENESS_SIAP_REVIEW,
                Lpj::COMPLETENESS_SIAP_FINALISASI,
            ])
            ->count();
        $totalFunds = (float) Lpj::query()->sum('total_funds_received');
        $validExpense = (float) Lpj::query()->sum('total_valid_expense');
        $remainingFunds = (float) Lpj::query()->sum('total_remaining_fund');

        return [
            Stat::make('Event Aktif', number_format($activeEvents, 0, ',', '.'))
                ->description('Sedang berjalan dan bisa diinput User')
                ->descriptionIcon('heroicon-o-bolt')
                ->color('info')
                ->icon('heroicon-o-calendar-days'),
            Stat::make('Siap Review', number_format($reviewEvents, 0, ',', '.'))
                ->description('Menunggu cek/finalisasi Admin')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->icon('heroicon-o-eye'),
            Stat::make('Event Selesai', number_format($finishedEvents, 0, ',', '.'))
                ->description('Terkunci dan siap dokumen LPJ')
                ->descriptionIcon('heroicon-o-lock-closed')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Dana Masuk', $this->money($totalFunds))
                ->description('Total dana event tercatat')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->icon('heroicon-o-wallet'),
            Stat::make('Pengeluaran Valid', $this->money($validExpense))
                ->description('Masuk perhitungan LPJ final')
                ->descriptionIcon('heroicon-o-receipt-percent')
                ->color('danger')
                ->icon('heroicon-o-receipt-refund'),
            Stat::make('Sisa Dana', $this->money($remainingFunds))
                ->description('Sisa global seluruh event')
                ->descriptionIcon('heroicon-o-scale')
                ->color('info')
                ->icon('heroicon-o-chart-bar'),
            Stat::make('User Aktif', number_format(User::query()->where('is_active', true)->count(), 0, ',', '.'))
                ->description('Admin dan petugas aktif')
                ->descriptionIcon('heroicon-o-users')
                ->color('gray')
                ->icon('heroicon-o-user-group'),
            Stat::make('Snapshot LPJ', number_format(LpjReportSnapshot::query()->count(), 0, ',', '.'))
                ->description('Dokumen LPJ pernah dicetak/export')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->icon('heroicon-o-folder-open'),
        ];
    }

    private function money(float $value): string
    {
        return 'Rp' . number_format($value, 0, ',', '.');
    }
}

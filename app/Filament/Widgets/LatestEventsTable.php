<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Lpjs\LpjResource;
use App\Models\Lpj;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestEventsTable extends TableWidget
{
    protected static ?string $heading = 'Event Terbaru';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Event')
                    ->description(fn (Lpj $record): string => $record->location ?: 'Lokasi belum diisi')
                    ->searchable()
                    ->limit(42),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => Lpj::statusLabels()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Lpj::STATUS_AKTIF => 'success',
                        Lpj::STATUS_FINISH => 'info',
                        Lpj::STATUS_DRAFT => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('completeness_status')
                    ->label('Kelengkapan')
                    ->formatStateUsing(fn (string $state): string => Lpj::completenessLabels()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Lpj::COMPLETENESS_SIAP_FINALISASI => 'success',
                        Lpj::COMPLETENESS_SIAP_REVIEW => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('personInCharge.name')
                    ->label('PJ')
                    ->placeholder('-'),
                TextColumn::make('total_funds_received')
                    ->label('Dana Masuk')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('updated_at')
                    ->label('Update')
                    ->since()
                    ->sortable(),
            ])
            ->recordUrl(fn (Lpj $record): string => LpjResource::getUrl('edit', ['record' => $record]))
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10]);
    }

    protected function getTableQuery(): Builder
    {
        return Lpj::query()
            ->with(['personInCharge:id,name'])
            ->latest('updated_at');
    }
}

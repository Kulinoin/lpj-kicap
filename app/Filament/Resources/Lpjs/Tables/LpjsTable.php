<?php

namespace App\Filament\Resources\Lpjs\Tables;

use App\Models\Lpj;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpjsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('type.name')
                    ->label('Tipe')
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                TextColumn::make('personInCharge.name')
                    ->label('PJ')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Lpj::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Lpj::STATUS_AKTIF => 'success',
                        Lpj::STATUS_FINISH => 'info',
                        Lpj::STATUS_ARSIPKAN => 'gray',
                        default => 'warning',
                    })
                    ->searchable(),
                TextColumn::make('completeness_status')
                    ->label('Kelengkapan')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),
                TextColumn::make('funding_source')
                    ->label('Sumber Dana')
                    ->searchable(),
                TextColumn::make('assignment_letter_number')
                    ->label('Surat Tugas')
                    ->searchable(),
                TextColumn::make('period_label')
                    ->label('Periode')
                    ->searchable(),
                TextColumn::make('external_organizer')
                    ->label('Penyelenggara')
                    ->searchable(),
                TextColumn::make('total_funds_received')
                    ->label('Dana')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_valid_expense')
                    ->label('Pengeluaran')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_remaining_fund')
                    ->label('Sisa')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Diajukan')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->label('Disetujui')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('approved_by')
                    ->label('Approver')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('finalized_at')
                    ->label('Finish')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('finalized_by')
                    ->label('Finalizer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('archived_at')
                    ->label('Arsip')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

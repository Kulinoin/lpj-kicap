<?php

namespace App\Filament\Resources\Lpjs\Tables;

use App\Models\Lpj;
use App\Services\LpjReviewService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
                    ->formatStateUsing(fn (string $state): string => Lpj::completenessLabels()[$state] ?? $state)
                    ->badge()
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
                TextColumn::make('total_valid_expense')
                    ->label('Pengeluaran Valid')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Aktif')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (Lpj $record): bool => $record->status !== Lpj::STATUS_DRAFT)
                    ->action(function (Lpj $record): void {
                        $record->forceFill([
                            'status' => Lpj::STATUS_AKTIF,
                        ])->save();

                        Notification::make()
                            ->title('Event diaktifkan')
                            ->success()
                            ->send();
                    }),
                Action::make('finish')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (Lpj $record): bool => $record->status !== Lpj::STATUS_AKTIF)
                    ->modalHeading(fn (Lpj $record): string => 'Selesaikan '.$record->code)
                    ->modalDescription('Event akan difinalisasi jika checklist sudah PASS. Setelah selesai, User tidak dapat input lagi.')
                    ->action(function (Lpj $record): void {
                        try {
                            app(LpjReviewService::class)->finalize($record, Auth::user());

                            Notification::make()
                                ->title('Event selesai dan terkunci')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title($exception->validator->errors()->first() ?: 'Checklist finalisasi belum lengkap')
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('checklist')
                    ->label('Checklist')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->modalHeading(fn (Lpj $record): string => 'Checklist Finalisasi '.$record->code)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Lpj $record) => view('filament.lpj-review-checklist', [
                        'record' => $record->fresh(),
                        'review' => app(LpjReviewService::class)->reviewPayload($record->fresh()),
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

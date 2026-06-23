<?php

namespace App\Filament\Resources\LpjFinancialTransactions;

use App\Filament\Resources\LpjFinancialTransactions\Pages\ListLpjFinancialTransactions;
use App\Models\LpjFinancialTransaction;
use App\Services\LpjReviewService;
use App\Services\AppFileStorageService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LpjFinancialTransactionResource extends Resource
{
    protected static ?string $model = LpjFinancialTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static ?string $navigationLabel = 'Transaksi Event';

    protected static ?string $modelLabel = 'Transaksi Event';

    protected static ?string $pluralModelLabel = 'Transaksi Event';

    protected static \UnitEnum|string|null $navigationGroup = 'Dana Kegiatan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lpj.code')
                    ->label('Kode Event')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('source_type')
                    ->label('Sumber')
                    ->formatStateUsing(fn (string $state): string => LpjFinancialTransaction::sourceLabels()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => LpjFinancialTransaction::statusLabels()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                IconColumn::make('proof_path')
                    ->label('Bukti')
                    ->boolean()
                    ->getStateUsing(fn (LpjFinancialTransaction $record): bool => filled($record->proof_path)),
                TextColumn::make('spent_at')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('admin_note')
                    ->label('Catatan Admin')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('spent_at', 'desc')
              ->recordUrl(fn (LpjFinancialTransaction $record): string => route('admin.lpj-financial-transactions.detail', $record))
            ->recordActions([
                Action::make('markValid')
                    ->label('Valid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (LpjFinancialTransaction $record) => self::review($record, LpjFinancialTransaction::STATUS_VALID)),
                Action::make('requestRevision')
                    ->label('Revisi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->modalSubmitActionLabel('Minta Revisi')
                    ->form([
                        Textarea::make('admin_note')
                            ->label('Catatan untuk User')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(fn (LpjFinancialTransaction $record, array $data) => self::review($record, LpjFinancialTransaction::STATUS_NEEDS_REVISION, $data['admin_note'] ?? null)),
                Action::make('waitProof')
                    ->label('Minta Bukti')
                    ->icon('heroicon-o-paper-clip')
                    ->color('gray')
                    ->modalSubmitActionLabel('Minta Bukti')
                    ->form([
                        Textarea::make('admin_note')
                            ->label('Catatan untuk User')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(fn (LpjFinancialTransaction $record, array $data) => self::review($record, LpjFinancialTransaction::STATUS_WAITING_PROOF, $data['admin_note'] ?? null)),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalSubmitActionLabel('Tolak Transaksi')
                    ->form([
                        Textarea::make('admin_note')
                            ->label('Alasan penolakan')
                            ->maxLength(1000),
                    ])
                    ->action(fn (LpjFinancialTransaction $record, array $data) => self::review($record, LpjFinancialTransaction::STATUS_REJECTED, $data['admin_note'] ?? null)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpjFinancialTransactions::route('/'),
        ];
    }

    private static function review(LpjFinancialTransaction $record, string $status, ?string $adminNote = null): void
    {
        try {
            app(LpjReviewService::class)->reviewTransaction($record, Auth::user(), $status, $adminNote);

            Notification::make()
                ->title('Review transaksi tersimpan')
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            Notification::make()
                ->title($exception->validator->errors()->first() ?: 'Review transaksi belum tersimpan')
                ->danger()
                ->send();
        }
    }
}

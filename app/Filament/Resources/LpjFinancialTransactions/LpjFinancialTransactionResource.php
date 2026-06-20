<?php

namespace App\Filament\Resources\LpjFinancialTransactions;

use App\Filament\Resources\LpjFinancialTransactions\Pages\ListLpjFinancialTransactions;
use App\Models\LpjFinancialTransaction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpjFinancialTransactionResource extends Resource
{
    protected static ?string $model = LpjFinancialTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static ?string $navigationLabel = 'Transaksi Operasional';

    protected static ?string $modelLabel = 'Transaksi Operasional';

    protected static ?string $pluralModelLabel = 'Transaksi Operasional';

    protected static \UnitEnum|string|null $navigationGroup = 'Operasional Keuangan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lpj.code')
                    ->label('Kode LPJ')
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
            ])
            ->defaultSort('spent_at', 'desc')
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
}

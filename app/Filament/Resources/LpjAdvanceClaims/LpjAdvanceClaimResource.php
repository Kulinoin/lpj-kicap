<?php

namespace App\Filament\Resources\LpjAdvanceClaims;

use App\Filament\Resources\LpjAdvanceClaims\Pages\ListLpjAdvanceClaims;
use App\Models\LpjAdvanceClaim;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpjAdvanceClaimResource extends Resource
{
    protected static ?string $model = LpjAdvanceClaim::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?string $navigationLabel = 'Klaim Talangan';

    protected static ?string $modelLabel = 'Klaim Talangan';

    protected static ?string $pluralModelLabel = 'Klaim Talangan';

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
                TextColumn::make('transaction.category')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => LpjAdvanceClaim::statusLabels()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpjAdvanceClaims::route('/'),
        ];
    }
}

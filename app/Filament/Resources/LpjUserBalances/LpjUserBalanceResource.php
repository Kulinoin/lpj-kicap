<?php

namespace App\Filament\Resources\LpjUserBalances;

use App\Filament\Resources\LpjUserBalances\Pages\ListLpjUserBalances;
use App\Models\LpjUserBalance;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpjUserBalanceResource extends Resource
{
    protected static ?string $model = LpjUserBalance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $navigationLabel = 'Saldo User';

    protected static ?string $modelLabel = 'Saldo User per Event';

    protected static ?string $pluralModelLabel = 'Saldo User per Event';

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
                TextColumn::make('lpj.title')
                    ->label('Event')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpjUserBalances::route('/'),
        ];
    }
}

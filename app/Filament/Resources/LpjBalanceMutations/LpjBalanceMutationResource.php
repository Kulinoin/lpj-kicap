<?php

namespace App\Filament\Resources\LpjBalanceMutations;

use App\Filament\Resources\LpjBalanceMutations\Pages\CreateLpjBalanceMutation;
use App\Filament\Resources\LpjBalanceMutations\Pages\ListLpjBalanceMutations;
use App\Models\LpjBalanceMutation;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpjBalanceMutationResource extends Resource
{
    protected static ?string $model = LpjBalanceMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?string $navigationLabel = 'Mutasi Saldo';

    protected static ?string $modelLabel = 'Dana Pegangan User';

    protected static ?string $pluralModelLabel = 'Mutasi Saldo';

    protected static \UnitEnum|string|null $navigationGroup = 'Operasional Keuangan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('lpj_id')
                ->label('LPJ')
                ->relationship('lpj', 'title')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('user_id')
                ->label('User Penerima')
                ->relationship(
                    'user',
                    'name',
                    modifyQueryUsing: fn ($query) => $query->where('role', User::ROLE_USER)->where('is_active', true),
                )
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('amount')
                ->label('Nominal Dana Pegangan')
                ->numeric()
                ->required(),
            Textarea::make('note')
                ->label('Catatan')
                ->columnSpanFull(),
        ]);
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
                TextColumn::make('relatedUser.name')
                    ->label('User Terkait Transfer')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->searchable(),
                TextColumn::make('direction')
                    ->label('Arah')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('balance_after')
                    ->label('Saldo Setelah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('occurred_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpjBalanceMutations::route('/'),
            'create' => CreateLpjBalanceMutation::route('/create'),
        ];
    }
}

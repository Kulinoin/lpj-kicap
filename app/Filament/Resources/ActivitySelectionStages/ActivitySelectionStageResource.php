<?php

namespace App\Filament\Resources\ActivitySelectionStages;

use App\Filament\Resources\ActivitySelectionStages\Pages\ListActivitySelectionStages;
use App\Models\ActivitySelectionStage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitySelectionStageResource extends Resource
{
    protected static ?string $model = ActivitySelectionStage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationLabel = 'Tahapan Seleksi';

    protected static ?string $modelLabel = 'Tahapan Seleksi';

    protected static ?string $pluralModelLabel = 'Tahapan Seleksi';

    protected static \UnitEnum|string|null $navigationGroup = 'Operasional Seleksi';

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
                    ->limit(34)
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Tahap')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Kode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_elimination')
                    ->label('Sistem Gugur')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivitySelectionStages::route('/'),
        ];
    }
}
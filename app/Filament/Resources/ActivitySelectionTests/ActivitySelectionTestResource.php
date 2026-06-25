<?php

namespace App\Filament\Resources\ActivitySelectionTests;

use App\Filament\Resources\ActivitySelectionTests\Pages\ListActivitySelectionTests;
use App\Models\ActivitySelectionTest;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitySelectionTestResource extends Resource
{
    protected static ?string $model = ActivitySelectionTest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Item Tes Seleksi';

    protected static ?string $modelLabel = 'Item Tes Seleksi';

    protected static ?string $pluralModelLabel = 'Item Tes Seleksi';

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
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('stage.name')
                    ->label('Tahap')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Item Tes')
                    ->searchable(),
                TextColumn::make('result_label')
                    ->label('Label Hasil')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('note_label')
                    ->label('Label Catatan')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_elimination')
                    ->label('Gugur')
                    ->boolean(),
                IconColumn::make('requires_reason_on_fail')
                    ->label('Alasan Jika Gagal')
                    ->boolean(),
                IconColumn::make('requires_attachment_on_fail')
                    ->label('Lampiran Jika Gagal')
                    ->boolean(),
                IconColumn::make('allows_pass_with_note')
                    ->label('Lulus Catatan')
                    ->boolean(),
                IconColumn::make('requires_attachment_on_pass_with_note')
                    ->label('Lampiran Lulus Catatan')
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
            'index' => ListActivitySelectionTests::route('/'),
        ];
    }
}
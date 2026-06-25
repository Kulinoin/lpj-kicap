<?php

namespace App\Filament\Resources\ActivityDocumentations;

use App\Filament\Resources\ActivityDocumentations\Pages\ListActivityDocumentations;
use App\Models\ActivityDocumentation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityDocumentationResource extends Resource
{
    protected static ?string $model = ActivityDocumentation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Dokumentasi Event';

    protected static ?string $modelLabel = 'Dokumentasi Event';

    protected static ?string $pluralModelLabel = 'Dokumentasi Event';

    protected static \UnitEnum|string|null $navigationGroup = 'Data Pelaksanaan';

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
                    ->searchable()
                    ->limit(35),
                TextColumn::make('uploader.name')
                    ->label('Uploader')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn (?string $state): string => ActivityDocumentation::categoryOptions()[$state] ?? ($state ?: '-'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('caption')
                    ->label('Caption')
                    ->limit(45)
                    ->searchable(),
                IconColumn::make('include_in_report')
                    ->label('Masuk LPJ')
                    ->boolean(),
                TextColumn::make('mime_type')
                    ->label('Tipe File')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Upload')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ActivityDocumentation $record): string => route('admin.activity-documentations.detail', [
                'documentation' => $record,
                'back_url' => '/admin/activity-documentations',
                'back_label' => 'Daftar dokumentasi',
            ]))
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityDocumentations::route('/'),
        ];
    }
}
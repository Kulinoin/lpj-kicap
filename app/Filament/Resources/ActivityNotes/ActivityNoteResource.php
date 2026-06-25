<?php

namespace App\Filament\Resources\ActivityNotes;

use App\Filament\Resources\ActivityNotes\Pages\ListActivityNotes;
use App\Models\ActivityNote;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityNoteResource extends Resource
{
    protected static ?string $model = ActivityNote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Catatan Event';

    protected static ?string $modelLabel = 'Catatan Event';

    protected static ?string $pluralModelLabel = 'Catatan Event';

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
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis Catatan')
                    ->formatStateUsing(fn (?string $state): string => ActivityNote::typeLabels()[$state] ?? ($state ?: '-'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('content_status')
                    ->label('Status Isi')
                    ->state(fn (ActivityNote $record): string => filled($record->content) ? 'Terisi' : 'Belum diisi')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Terisi' ? 'success' : 'gray'),
                TextColumn::make('content')
                    ->label('Isi Catatan')
                    ->placeholder('Belum ada isi catatan')
                    ->limit(70)
                    ->searchable(),
                IconColumn::make('include_in_report')
                    ->label('Masuk LPJ')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordUrl(fn (ActivityNote $record): string => route('admin.activity-notes.detail', [
                'note' => $record,
                'back_url' => '/admin/activity-notes',
                'back_label' => 'Daftar catatan',
            ]))
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityNotes::route('/'),
        ];
    }
}
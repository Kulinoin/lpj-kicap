<?php

namespace App\Filament\Resources\ActivityAttachments;

use App\Filament\Resources\ActivityAttachments\Pages\ListActivityAttachments;
use App\Models\ActivityAttachment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityAttachmentResource extends Resource
{
    protected static ?string $model = ActivityAttachment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Lampiran Event';

    protected static ?string $modelLabel = 'Lampiran Event';

    protected static ?string $pluralModelLabel = 'Lampiran Event';

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
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Keterangan')
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
            ->recordUrl(fn (ActivityAttachment $record): string => route('admin.activity-attachments.detail', [
                'attachment' => $record,
                'back_url' => '/admin/activity-attachments',
                'back_label' => 'Daftar lampiran',
            ]))
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityAttachments::route('/'),
        ];
    }
}
<?php

namespace App\Filament\Resources\ActivityParticipants;

use App\Filament\Resources\ActivityParticipants\Pages\ListActivityParticipants;
use App\Models\ActivityParticipant;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityParticipantResource extends Resource
{
    protected static ?string $model = ActivityParticipant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Peserta Seleksi';

    protected static ?string $modelLabel = 'Peserta Seleksi';

    protected static ?string $pluralModelLabel = 'Peserta Seleksi';

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
                TextColumn::make('participant_number')
                    ->label('No. Peserta')
                    ->searchable()
                    ->placeholder('Belum ada'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('origin')
                    ->label('Asal')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('selection_registration_status')
                    ->label('Registrasi')
                    ->formatStateUsing(fn (?string $state): string => ActivityParticipant::registrationStatusLabels()[$state] ?? ($state ?: '-'))
                    ->badge(),
                TextColumn::make('selection_status')
                    ->label('Status Seleksi')
                    ->formatStateUsing(fn (?string $state): string => ActivityParticipant::selectionStatusLabels()[$state] ?? ($state ?: '-'))
                    ->badge(),
                TextColumn::make('eliminatedStage.name')
                    ->label('Tahap Gugur')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('eliminatedTest.name')
                    ->label('Tes Gugur')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('registeredBy.name')
                    ->label('Registrasi Oleh')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(45)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordUrl(fn (ActivityParticipant $record): string => route('admin.selection.participants.detail', [
                'participant' => $record,
                'back_url' => '/admin/activity-participants',
                'back_label' => 'Daftar peserta seleksi',
            ]))
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityParticipants::route('/'),
        ];
    }
}
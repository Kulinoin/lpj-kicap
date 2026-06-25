<?php

namespace App\Filament\Resources\ActivityParticipantTestResults;

use App\Filament\Resources\ActivityParticipantTestResults\Pages\ListActivityParticipantTestResults;
use App\Models\ActivityParticipantTestResult;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityParticipantTestResultResource extends Resource
{
    protected static ?string $model = ActivityParticipantTestResult::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Hasil Tes Peserta';

    protected static ?string $modelLabel = 'Hasil Tes Peserta';

    protected static ?string $pluralModelLabel = 'Hasil Tes Peserta';

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
                TextColumn::make('participant.participant_number')
                    ->label('No. Peserta')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('participant.name')
                    ->label('Nama Peserta')
                    ->searchable(),
                TextColumn::make('stage.name')
                    ->label('Tahap')
                    ->searchable(),
                TextColumn::make('test.name')
                    ->label('Item Tes')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (?string $state): string => ActivityParticipantTestResult::statusLabels()[$state] ?? ($state ?: '-'))
                    ->badge(),
                TextColumn::make('result_value')
                    ->label('Nilai/Hasil')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('note')
                    ->label('Keterangan')
                    ->limit(45)
                    ->placeholder('-'),
                TextColumn::make('updater.name')
                    ->label('Diupdate Oleh')
                    ->placeholder('-'),
                TextColumn::make('assessed_at')
                    ->label('Waktu Tes')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityParticipantTestResults::route('/'),
        ];
    }
}
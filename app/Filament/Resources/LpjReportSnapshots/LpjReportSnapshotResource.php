<?php

namespace App\Filament\Resources\LpjReportSnapshots;

use App\Filament\Resources\LpjReportSnapshots\Pages\ListLpjReportSnapshots;
use App\Models\LpjReportSnapshot;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpjReportSnapshotResource extends Resource
{
    protected static ?string $model = LpjReportSnapshot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $navigationLabel = 'Dokumen LPJ';

    protected static ?string $modelLabel = 'Dokumen LPJ';

    protected static ?string $pluralModelLabel = 'Dokumen LPJ';

    protected static \UnitEnum|string|null $navigationGroup = 'Output LPJ';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('snapshot_number')
                    ->label('No. Snapshot')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lpj.code')
                    ->label('Kode Event')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lpj.title')
                    ->label('Event')
                    ->searchable()
                    ->limit(42),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->formatStateUsing(fn (string $state): string => LpjReportSnapshot::sourceLabels()[$state] ?? $state)
                    ->badge()
                    ->searchable(),
                TextColumn::make('generator.name')
                    ->label('Dihasilkan Oleh')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('total_funds_received')
                    ->label('Dana Masuk')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_valid_expense')
                    ->label('Pengeluaran Valid')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_remaining_fund')
                    ->label('Sisa Dana')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('generated_at')
                    ->label('Dihasilkan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('generated_at', 'desc')
            ->recordActions([
                Action::make('print_snapshot')
                    ->label('Buka Snapshot')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (LpjReportSnapshot $record): string => route('admin.lpj-report-snapshots.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpjReportSnapshots::route('/'),
        ];
    }
}

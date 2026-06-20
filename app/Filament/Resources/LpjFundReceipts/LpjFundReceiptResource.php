<?php

namespace App\Filament\Resources\LpjFundReceipts;

use App\Filament\Resources\LpjFundReceipts\Pages\CreateLpjFundReceipt;
use App\Filament\Resources\LpjFundReceipts\Pages\ListLpjFundReceipts;
use App\Models\LpjFundReceipt;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LpjFundReceiptResource extends Resource
{
    protected static ?string $model = LpjFundReceipt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Dana Masuk Event';

    protected static ?string $modelLabel = 'Dana Masuk Event';

    protected static ?string $pluralModelLabel = 'Dana Masuk Event';

    protected static \UnitEnum|string|null $navigationGroup = 'Dana Kegiatan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('lpj_id')
                ->label('Event')
                ->relationship('lpj', 'title')
                ->placeholder('Pilih event')
                ->searchPrompt('Cari event')
                ->loadingMessage('Memuat event')
                ->noSearchResultsMessage('Event tidak ditemukan')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('source_name')
                ->label('Sumber Dana')
                ->options(LpjFundReceipt::sourceOptions())
                ->placeholder('Pilih sumber dana')
                ->native(false)
                ->required(),
            TextInput::make('amount')
                ->label('Nominal')
                ->numeric()
                ->required(),
            DatePicker::make('received_at')
                ->label('Tanggal Diterima')
                ->default(now())
                ->required(),
            Textarea::make('description')
                ->label('Keterangan')
                ->columnSpanFull(),
        ]);
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
                TextColumn::make('source_name')
                    ->label('Sumber')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('received_at')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('recorder.name')
                    ->label('Dicatat Oleh')
                    ->sortable(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpjFundReceipts::route('/'),
            'create' => CreateLpjFundReceipt::route('/create'),
        ];
    }
}

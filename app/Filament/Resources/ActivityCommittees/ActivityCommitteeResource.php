<?php

namespace App\Filament\Resources\ActivityCommittees;

use App\Filament\Resources\ActivityCommittees\Pages\ListActivityCommittees;
use App\Filament\Resources\ActivityCommittees\Pages\CreateActivityCommittee;
use App\Filament\Resources\ActivityCommittees\Pages\ViewActivityCommittee;
use App\Models\ActivityCommittee;
use App\Models\Lpj;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityCommitteeResource extends Resource
{
    protected static ?string $model = ActivityCommittee::class;

    protected static ?string $navigationLabel = 'Panitia/Pendamping';

    protected static ?string $modelLabel = 'Panitia/Pendamping';

    protected static ?string $pluralModelLabel = 'Panitia/Pendamping';

    protected static string | \UnitEnum | null $navigationGroup = 'Operasional Event';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('lpj_id')
                ->label('Event')
                ->options(fn (): array => Lpj::query()->latest('id')->pluck('title', 'id')->all())
                ->searchable()
                ->required(),

            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),

            TextInput::make('role')
                ->label('Jabatan/Peran')
                ->maxLength(255),

            TextInput::make('task')
                ->label('Tugas')
                ->maxLength(255),

            TextInput::make('contact')
                ->label('Kontak')
                ->maxLength(100),
        ]);
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('detail_heading')
                ->label('')
                ->state('Detail Panitia / Pendamping')
                ->columnSpanFull(),

            TextEntry::make('name')
                ->label('Nama')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('lpj.title')
                ->label('Event')
                ->placeholder('-'),

            TextEntry::make('role')
                ->label('Jabatan / Peran')
                ->placeholder('-'),

            TextEntry::make('task')
                ->label('Tugas')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('contact')
                ->label('Kontak')
                ->placeholder('-'),

            TextEntry::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y H:i'),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lpj.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Peran')
                    ->searchable(),

                TextColumn::make('task')
                    ->label('Tugas')
                    ->searchable(),

                TextColumn::make('contact')
                    ->label('Kontak')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordUrl(fn (ActivityCommittee $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityCommittees::route('/'),
            'create' => CreateActivityCommittee::route('/create'),
            'view' => ViewActivityCommittee::route('/{record}'),
        ];
    }
}
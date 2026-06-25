<?php

namespace App\Filament\Resources\ActivitySchedules;

use App\Filament\Resources\ActivitySchedules\Pages\ListActivitySchedules;
use App\Filament\Resources\ActivitySchedules\Pages\CreateActivitySchedule;
use App\Filament\Resources\ActivitySchedules\Pages\ViewActivitySchedule;
use App\Models\ActivitySchedule;
use App\Models\Lpj;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityScheduleResource extends Resource
{
    protected static ?string $model = ActivitySchedule::class;

    protected static ?string $navigationLabel = 'Rundown Event';

    protected static ?string $modelLabel = 'Rundown Event';

    protected static ?string $pluralModelLabel = 'Rundown Event';

    protected static string | \UnitEnum | null $navigationGroup = 'Operasional Event';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('lpj_id')
                ->label('Event')
                ->options(fn (): array => Lpj::query()->latest('id')->pluck('title', 'id')->all())
                ->searchable()
                ->required(),

            TextInput::make('activity_name')
                ->label('Nama Kegiatan / Agenda')
                ->required()
                ->maxLength(255),

            DateTimePicker::make('start_time')
                ->label('Mulai')
                ->seconds(false)
                ->required(),

            DateTimePicker::make('end_time')
                ->label('Selesai')
                ->seconds(false),

            TextInput::make('responsible_person')
                ->label('Penanggung Jawab')
                ->maxLength(255),

            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0),

            Select::make('status')
                ->label('Status Rundown')
                ->options(ActivitySchedule::statusLabels())
                ->default(ActivitySchedule::STATUS_NOT_STARTED)
                ->required(),

            Textarea::make('note')
                ->label('Catatan')
                ->rows(3)
                ->columnSpanFull(),

            Textarea::make('status_note')
                ->label('Catatan Status')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('agenda_heading')
                ->label('')
                ->state('Detail Rundown')
                ->columnSpanFull(),

            TextEntry::make('activity_name')
                ->label('Nama Kegiatan / Agenda')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('lpj.title')
                ->label('Event')
                ->placeholder('-'),

            TextEntry::make('responsible_person')
                ->label('Penanggung Jawab')
                ->placeholder('-'),

            TextEntry::make('start_time')
                ->label('Mulai')
                ->dateTime('d M Y H:i'),

            TextEntry::make('end_time')
                ->label('Selesai')
                ->dateTime('d M Y H:i')
                ->placeholder('-'),

            TextEntry::make('status')
                ->label('Status Rundown')
                ->formatStateUsing(fn (?string $state): string => ActivitySchedule::statusLabels()[$state] ?? ($state ?: '-'))
                ->badge(),

            TextEntry::make('sort_order')
                ->label('Urutan')
                ->placeholder('-'),

            TextEntry::make('note')
                ->label('Catatan')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('status_note')
                ->label('Catatan Status')
                ->placeholder('-')
                ->columnSpanFull(),

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

                TextColumn::make('start_time')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i'),

                TextColumn::make('activity_name')
                    ->label('Agenda')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('responsible_person')
                    ->label('PIC')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (?string $state): string => ActivitySchedule::statusLabels()[$state] ?? ($state ?: '-'))
                    ->badge(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->recordUrl(fn (ActivitySchedule $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivitySchedules::route('/'),
            'create' => CreateActivitySchedule::route('/create'),
            'view' => ViewActivitySchedule::route('/{record}'),
        ];
    }
}
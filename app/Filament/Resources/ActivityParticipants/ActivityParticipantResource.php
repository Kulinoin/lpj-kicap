<?php

namespace App\Filament\Resources\ActivityParticipants;

use App\Filament\Resources\ActivityParticipants\Pages\ListActivityParticipants;
use App\Filament\Resources\ActivityParticipants\Pages\CreateActivityParticipant;
use App\Filament\Resources\ActivityParticipants\Pages\ViewActivityParticipant;
use App\Filament\Resources\ActivityParticipants\Pages\EditActivityParticipant;
use App\Models\ActivityParticipant;
use App\Models\Lpj;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityParticipantResource extends Resource
{
    protected static ?string $model = ActivityParticipant::class;

    protected static ?string $navigationLabel = 'Peserta Seleksi';

    protected static ?string $modelLabel = 'Peserta Seleksi';

    protected static ?string $pluralModelLabel = 'Peserta Seleksi';

    protected static string | \UnitEnum | null $navigationGroup = 'Operasional Event';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('lpj_id')
                ->label('Event')
                ->options(fn (): array => Lpj::query()->latest('id')->pluck('title', 'id')->all())
                ->searchable()
                ->required(),

            TextInput::make('participant_number')
                ->label('No. Peserta')
                ->maxLength(100),

            TextInput::make('name')
                ->label('Nama Peserta')
                ->required()
                ->maxLength(255),

            TextInput::make('origin')
                ->label('Asal / Keterangan')
                ->maxLength(255),

            TextInput::make('whatsapp')
                ->label('WhatsApp')
                ->tel()
                ->maxLength(50),

            FileUpload::make('photo_path')
                ->label('Foto Peserta')
                ->image()
                ->disk('public')
                ->directory('activity-participants/photos')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(5120)
                ->downloadable()
                ->openable()
                ->columnSpanFull(),

            Select::make('selection_registration_status')
                ->label('Status Registrasi')
                ->options(ActivityParticipant::registrationStatusLabels())
                ->default(array_key_first(ActivityParticipant::registrationStatusLabels()) ?? 'draft')
                ->required(),

            Select::make('selection_status')
                ->label('Status Seleksi')
                ->options(ActivityParticipant::selectionStatusLabels())
                ->default(array_key_first(ActivityParticipant::selectionStatusLabels()) ?? 'pending')
                ->required(),

            Select::make('attendance_status')
                ->label('Status Kehadiran')
                ->options([
                    'belum_hadir' => 'Belum Hadir',
                    'hadir' => 'Hadir',
                    'izin' => 'Izin',
                    'tidak_hadir' => 'Tidak Hadir',
                ])
                ->default('belum_hadir'),

            Textarea::make('note')
                ->label('Catatan')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            ImageEntry::make('photo_path')
                ->getStateUsing(fn ($record): ?string => filled($record->photo_path) ? route('participant-photo.proxy.v3', $record) : null)
                ->label('Foto Peserta')
                ->disk('public')
                ->height(280)
                ->columnSpanFull(),

            TextEntry::make('identity_heading')
                ->label('')
                ->state('Identitas Peserta')
                ->columnSpanFull(),

            TextEntry::make('name')
                ->label('Nama Lengkap')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('participant_number')
                ->label('No. Peserta')
                ->placeholder('-'),

            TextEntry::make('lpj.title')
                ->label('Event')
                ->placeholder('-'),

            TextEntry::make('origin')
                ->label('Asal / Keterangan')
                ->placeholder('-'),

            TextEntry::make('whatsapp')
                ->label('WhatsApp')
                ->placeholder('-'),

            TextEntry::make('status_heading')
                ->label('')
                ->state('Status Peserta')
                ->columnSpanFull(),

            TextEntry::make('selection_registration_status')
                ->label('Status Registrasi')
                ->formatStateUsing(fn (?string $state): string => ActivityParticipant::registrationStatusLabels()[$state] ?? ($state ?: '-'))
                ->badge(),

            TextEntry::make('selection_status')
                ->label('Status Seleksi')
                ->formatStateUsing(fn (?string $state): string => ActivityParticipant::selectionStatusLabels()[$state] ?? ($state ?: '-'))
                ->badge(),

            TextEntry::make('attendance_status')
                ->label('Status Kehadiran')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'belum_hadir' => 'Belum Hadir',
                    'hadir' => 'Hadir',
                    'izin' => 'Izin',
                    'tidak_hadir' => 'Tidak Hadir',
                    default => $state ?: '-',
                })
                ->badge(),

            TextEntry::make('note_heading')
                ->label('')
                ->state('Catatan & Riwayat')
                ->columnSpanFull(),

            TextEntry::make('note')
                ->label('Catatan')
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

                TextColumn::make('participant_number')
                    ->label('No. Peserta')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('origin')
                    ->label('Asal')
                    ->searchable(),

                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable(),

                TextColumn::make('selection_registration_status')
                    ->label('Registrasi')
                    ->formatStateUsing(fn (?string $state): string => ActivityParticipant::registrationStatusLabels()[$state] ?? ($state ?: '-'))
                    ->badge(),

                TextColumn::make('selection_status')
                    ->label('Seleksi')
                    ->formatStateUsing(fn (?string $state): string => ActivityParticipant::selectionStatusLabels()[$state] ?? ($state ?: '-'))
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            // KICAP_ADMIN_PARTICIPANT_EDIT_DELETE_SAFE_01
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->label('Edit')
                    ->url(fn (ActivityParticipant $record): string => static::getUrl('edit', ['record' => $record])),

                \Filament\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus peserta?')
                    ->modalDescription('Peserta dan progress tes terkait akan ikut dihapus.')
                    ->before(function (ActivityParticipant $record): void {
                        if (method_exists($record, 'testResults')) {
                            $record->testResults()->delete();
                        }
                    }),
            ])
            ->recordUrl(fn (ActivityParticipant $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityParticipants::route('/'),
            'create' => CreateActivityParticipant::route('/create'),
            'view' => ViewActivityParticipant::route('/{record}'),
            'edit' => EditActivityParticipant::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources\Lpjs\Schemas;

use App\Models\Lpj;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LpjForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Event')
                    ->required()
                    ->maxLength(255),
                TextInput::make('title')
                    ->label('Judul Event')
                    ->required()
                    ->maxLength(255),
                Select::make('lpj_type_id')
                    ->label('Tipe Event')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Hidden::make('created_by'),
                Select::make('person_in_charge_id')
                    ->label('Penanggung Jawab')
                    ->relationship(
                        'personInCharge',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->where('role', User::ROLE_USER)->where('is_active', true),
                    )
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->label('Status Event')
                    ->options(Lpj::statusLabels())
                    ->required()
                    ->default('draft'),
                Select::make('completeness_status')
                    ->label('Kelengkapan')
                    ->options([
                        Lpj::COMPLETENESS_BELUM_LENGKAP => 'Belum Lengkap',
                        Lpj::COMPLETENESS_SIAP_REVIEW => 'Siap Review',
                        Lpj::COMPLETENESS_SIAP_FINALISASI => 'Siap Finalisasi',
                    ])
                    ->required()
                    ->default(Lpj::COMPLETENESS_BELUM_LENGKAP),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai'),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai'),
                TextInput::make('location')
                    ->label('Lokasi')
                    ->maxLength(255),
                TextInput::make('funding_source')
                    ->label('Sumber Dana')
                    ->maxLength(255),
                TextInput::make('assignment_letter_number')
                    ->label('Nomor Surat Tugas')
                    ->maxLength(255),
                TextInput::make('period_label')
                    ->label('Periode')
                    ->maxLength(255),
                TextInput::make('external_organizer')
                    ->label('Penyelenggara Eksternal')
                    ->maxLength(255),
                Textarea::make('organization_role')
                    ->label('Peran Lembaga')
                    ->columnSpanFull(),
                TextInput::make('total_funds_received')
                    ->label('Dana Diterima')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_valid_expense')
                    ->label('Pengeluaran Valid')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_remaining_fund')
                    ->label('Sisa Dana')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Repeater::make('assignedUsers')
                    ->label('User Ditugaskan')
                    ->relationship()
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship(
                                'user',
                                'name',
                                modifyQueryUsing: fn ($query) => $query->where('role', User::ROLE_USER)->where('is_active', true),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('role_label')
                            ->label('Peran')
                            ->maxLength(255),
                        Toggle::make('can_input_transaction')
                            ->label('Input Operasional')
                            ->default(true),
                        Toggle::make('can_upload_documentation')
                            ->label('Upload Dokumentasi')
                            ->default(true),
                        Toggle::make('can_edit_activity_data')
                            ->label('Edit Data Kegiatan')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                DateTimePicker::make('submitted_at')
                    ->label('Diajukan Pada'),
                DateTimePicker::make('approved_at')
                    ->label('Disetujui Pada'),
                TextInput::make('approved_by')
                    ->label('Disetujui Oleh')
                    ->numeric(),
                DateTimePicker::make('finalized_at')
                    ->label('Finish Pada'),
                TextInput::make('finalized_by')
                    ->label('Finish Oleh')
                    ->numeric(),
                DateTimePicker::make('archived_at')
                    ->label('Diarsipkan Pada'),
            ]);
    }
}

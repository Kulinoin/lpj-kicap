<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->maxLength(30),
                DateTimePicker::make('email_verified_at')
                    ->label('Email Terverifikasi'),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null)
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('role')
                    ->label('Role')
                    ->options([
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_USER => 'User',
                    ])
                    ->required()
                    ->default(User::ROLE_USER)
                    ->live(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
                Toggle::make('can_create_lpj')
                    ->label('Bisa Membuat Event')
                    ->helperText('Hanya Admin yang boleh membuat event/kegiatan pada MVP.')
                    ->disabled(fn ($get): bool => $get('role') !== User::ROLE_ADMIN)
                    ->dehydrated()
                    ->default(false),
                Toggle::make('can_transfer_balance')
                    ->label('Bisa Transfer Saldo')
                    ->required(),
            ]);
    }
}

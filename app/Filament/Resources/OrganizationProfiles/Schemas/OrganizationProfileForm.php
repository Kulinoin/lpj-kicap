<?php

namespace App\Filament\Resources\OrganizationProfiles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrganizationProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('institution_name')
                    ->required(),
                TextInput::make('institution_type'),
                TextInput::make('unit_name'),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('mobile'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('website')
                    ->url(),
                FileUpload::make('logo_path')
                    ->label('Logo Lembaga untuk LPJ')
                    ->disk('public')
                    ->directory('organization-logos')
                    ->image()
                    ->imageEditor()
                    ->openable()
                    ->downloadable()
                    ->helperText('Dipakai pada cover dan kop LPJ. Logo aplikasi tetap hardcode di public/icons/kicap-lpj.svg, public/favicon.ico, dan konfigurasi PWA vite.config.js.'),
                TextInput::make('footer_text'),
                TextInput::make('default_city'),
                TextInput::make('leader_name'),
                TextInput::make('leader_position'),
            ]);
    }
}

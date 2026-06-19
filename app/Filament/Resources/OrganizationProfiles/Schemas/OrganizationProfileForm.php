<?php

namespace App\Filament\Resources\OrganizationProfiles\Schemas;

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
                TextInput::make('logo_path'),
                TextInput::make('footer_text'),
                TextInput::make('default_city'),
                TextInput::make('leader_name'),
                TextInput::make('leader_position'),
            ]);
    }
}

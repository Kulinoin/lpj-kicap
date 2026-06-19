<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component as Component;
use Filament\Forms\Components\TextInput;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username / Email')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes([
                'type' => 'text',
                'inputmode' => 'text',
                'autocapitalize' => 'none',
                'spellcheck' => 'false',
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => trim((string) ($data['email'] ?? '')),
            'password' => $data['password'] ?? '',
        ];
    }
}

<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Kicap LPJ';
    }

    public function authenticate(): ?LoginResponseContract
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);
        $remember = (bool) ($data['remember'] ?? false);

        if (! Filament::auth()->attempt($credentials, $remember)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (! $user?->is_active) {
            Filament::auth()->logout();
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponseContract::class);
    }

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

<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->userMenuItems([
            'profile' => Action::make('profile')
                ->label('Profil Saya')
                ->icon('heroicon-o-user-circle')
                ->modalHeading('Profil Saya')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Simpan Profil')
                ->fillForm(fn (): array => [
                    'profile_photo_path' => Auth::user()?->profile_photo_path,
                    'username' => Auth::user()?->username,
                    'email' => Auth::user()?->email,
                    'name' => Auth::user()?->name,
                    'whatsapp' => Auth::user()?->whatsapp,
                    'password' => null,
                    'password_confirmation' => null,
                ])
                ->form([
                    FileUpload::make('profile_photo_path')
                        ->label('Foto Profil')
                        ->disk('public')
                        ->directory('profile-photos')
                        ->image()
                        ->avatar()
                        ->maxSize(2048)
                        ->columnSpanFull()
                        ->extraAttributes([
                            'class' => 'kicap-profile-photo-center',
                            'style' => 'display:flex;flex-direction:column;align-items:center;text-align:center;',
                        ])
                        ->helperText('Ganti profile avatar.'),
                    TextInput::make('username')
                        ->label('Username')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('email')
                        ->label('Email')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('whatsapp')
                        ->label('WhatsApp')
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('password')
                        ->label('Password Baru')
                        ->password()
                        ->revealable()
                        ->maxLength(255)
                        ->helperText('Kosongkan jika tidak ingin mengganti password. Minimal 8 karakter.'),
                    TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password Baru')
                        ->password()
                        ->revealable()
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    $user = Auth::user();

                    if (! $user) {
                        return;
                    }

                    $password = (string) ($data['password'] ?? '');
                    $passwordConfirmation = (string) ($data['password_confirmation'] ?? '');

                    if ($password !== '') {
                        if (strlen($password) < 8) {
                            Notification::make()
                                ->title('Password minimal 8 karakter')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($password !== $passwordConfirmation) {
                            Notification::make()
                                ->title('Konfirmasi password tidak sama')
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    $payload = [
                        'name' => $data['name'] ?? $user->name,
                        'whatsapp' => $data['whatsapp'] ?? null,
                        'profile_photo_path' => $data['profile_photo_path'] ?? $user->profile_photo_path,
                    ];

                    if ($password !== '') {
                        $payload['password'] = Hash::make($password);
                    }

                    $user->forceFill($payload)->save();

                    Notification::make()
                        ->title('Profil berhasil diperbarui')
                        ->success()
                        ->send();
                }),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

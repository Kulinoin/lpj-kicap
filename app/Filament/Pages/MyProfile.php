<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;

class MyProfile extends Page
{
    use WithFileUploads;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-circle';

    protected static \UnitEnum|string|null $navigationGroup = 'Akun';

    protected static ?string $navigationLabel = 'Profil Saya';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.my-profile';

    public ?string $name = null;

    public ?string $username = null;

    public ?string $email = null;

    public ?string $whatsapp = null;

    public ?string $profile_photo_path = null;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public $photo = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->whatsapp = $user->whatsapp;
        $this->profile_photo_path = $user->profile_photo_path;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'same:password_confirmation'],
        ]);

        $user = Auth::user();
        $profilePhotoPath = $user->profile_photo_path;
        $profilePhotoDisk = $user->profile_photo_disk;

        if ($this->photo) {
            if ($profilePhotoPath) {
                app(\App\Services\AppFileStorageService::class)->delete($profilePhotoPath, $profilePhotoDisk);
            }

            $stored = app(\App\Services\AppFileStorageService::class)->store($this->photo, 'profile-photos');

            $profilePhotoPath = $stored['path'];
            $profilePhotoDisk = $stored['disk'];
        }

        $payload = [
            'name' => $this->name,
            'whatsapp' => $this->whatsapp,
            'profile_photo_path' => $profilePhotoPath,
            'profile_photo_disk' => $profilePhotoDisk,
        ];

        if ($this->password) {
            $payload['password'] = Hash::make($this->password);
        }

        $user->forceFill($payload)->save();

        $this->profile_photo_path = $profilePhotoPath;
        $this->photo = null;
        $this->password = null;
        $this->password_confirmation = null;

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }
}

<?php

namespace App\Http\Responses\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportRedirects\Redirector;

class RoleLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Auth::user();

        if ($user instanceof User && $user->isUser()) {
            return redirect()->to('/app');
        }

        return redirect()->intended('/admin');
    }
}

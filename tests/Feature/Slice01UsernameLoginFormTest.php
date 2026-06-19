<?php

namespace Tests\Feature;

use App\Auth\UsernameEmailUserProvider;
use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Database\Seeders\KicapUserSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class Slice01UsernameLoginFormTest extends TestCase
{
    use DatabaseTransactions;

    public function test_custom_login_page_is_registered_for_panel(): void
    {
        $this->assertSame(Login::class, Filament::getCurrentOrDefaultPanel()->getLoginRouteAction());
    }

    public function test_username_email_provider_can_find_user_from_login_field(): void
    {
        $this->seed(KicapUserSeeder::class);

        $provider = new UsernameEmailUserProvider(app('hash'), User::class);

        $this->assertNotNull($provider->retrieveByCredentials([
            'email' => 'admin',
            'password' => 'password',
        ]));

        $this->assertNotNull($provider->retrieveByCredentials([
            'email' => 'admin@kicap.id',
            'password' => 'password',
        ]));
    }

    public function test_auth_attempt_accepts_username_in_email_field(): void
    {
        $this->seed(KicapUserSeeder::class);

        $this->assertTrue(Auth::attempt([
            'email' => 'admin',
            'password' => 'password',
        ]));
    }
}

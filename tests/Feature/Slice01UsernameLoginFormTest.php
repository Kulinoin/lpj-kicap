<?php

namespace Tests\Feature;

use App\Auth\UsernameEmailUserProvider;
use App\Filament\Pages\Auth\Login;
use App\Http\Responses\Auth\RoleLoginResponse;
use App\Models\User;
use Database\Seeders\KicapUserSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function test_remember_me_attempt_sets_remember_token(): void
    {
        $this->seed(KicapUserSeeder::class);

        $this->assertTrue(Auth::attempt([
            'email' => 'user',
            'password' => 'password',
        ], true));

        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();

        $this->assertNotEmpty($user->remember_token);
    }

    public function test_role_login_response_redirects_user_to_pwa(): void
    {
        $this->seed(KicapUserSeeder::class);

        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/admin/login');

        $loginResponse = (new RoleLoginResponse)->toResponse(request());

        $this->assertSame(url('/app'), $loginResponse->getTargetUrl());
    }

    public function test_guest_pwa_redirects_to_single_login_route(): void
    {
        $this->get('/app')->assertRedirect('/login');
        $this->get('/login')->assertRedirect('/admin/login');
    }

    public function test_user_cannot_open_admin_panel(): void
    {
        $this->seed(KicapUserSeeder::class);

        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_opening_pwa_is_redirected_to_admin_panel(): void
    {
        $this->seed(KicapUserSeeder::class);

        $admin = User::query()->where('email', 'admin@kicap.id')->firstOrFail();

        $this->actingAs($admin)->get('/app')->assertRedirect('/admin');
    }

    public function test_user_can_logout_from_pwa(): void
    {
        $this->seed(KicapUserSeeder::class);

        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();

        $this->actingAs($user)
            ->post('/app/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_user_can_read_pwa_profile(): void
    {
        $this->seed(KicapUserSeeder::class);

        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();

        $this->actingAs($user)
            ->getJson('/api/app/profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'User Lapangan')
            ->assertJsonPath('data.username', 'user')
            ->assertJsonPath('data.email', 'user@kicap.id');
    }

    public function test_user_can_update_pwa_profile_avatar_and_password(): void
    {
        Storage::fake('public');
        $this->seed(KicapUserSeeder::class);

        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();

        $this->actingAs($user)
            ->post('/api/app/profile', [
                'name' => 'User Operasional',
                'whatsapp' => '08123456789',
                'profile_photo' => UploadedFile::fake()->create('avatar.jpg', 120, 'image/jpeg'),
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'User Operasional')
            ->assertJsonPath('data.whatsapp', '08123456789');

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
        $this->assertTrue(Hash::check('password-baru', $user->password));
    }
}

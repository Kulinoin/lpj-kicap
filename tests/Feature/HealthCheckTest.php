<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\KicapUserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use DatabaseTransactions;

    public function test_health_endpoint_returns_ok_response(): void
    {
        $response = $this->get('/health');

        $response
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'app' => 'Kicap LPJ',
                'slice' => '00',
                'database' => 'ok',
                'timezone' => 'Asia/Jakarta',
            ]);
    }

    public function test_pwa_shell_loads(): void
    {
        $this->seed(KicapUserSeeder::class);

        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();

        $this->actingAs($user)->get('/app')->assertOk();
    }

    public function test_admin_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertStatus(302);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
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
        $this->get('/app')->assertOk();
    }

    public function test_admin_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertStatus(302);
    }
}

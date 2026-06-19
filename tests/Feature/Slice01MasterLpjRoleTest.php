<?php

namespace Tests\Feature;

use App\Models\Lpj;
use App\Models\LpjType;
use App\Models\OrganizationProfile;
use App\Models\User;
use Database\Seeders\KicapUserSeeder;
use Database\Seeders\LpjTypeSeeder;
use Database\Seeders\OrganizationProfileSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Slice01MasterLpjRoleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_master_seed_data_is_available(): void
    {
        $this->seed([
            KicapUserSeeder::class,
            OrganizationProfileSeeder::class,
            LpjTypeSeeder::class,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@kicap.id',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user@kicap.id',
            'role' => User::ROLE_USER,
        ]);

        $this->assertDatabaseHas('organization_profiles', [
            'institution_name' => 'PT. Kazoku Indonesia Center',
            'institution_type' => 'Lembaga Pelatihan Kerja',
        ]);

        $this->assertSame(5, LpjType::query()->active()->count());
    }

    public function test_role_helpers_work_for_admin_and_user(): void
    {
        $this->seed(KicapUserSeeder::class);

        $admin = User::query()->where('email', 'admin@kicap.id')->firstOrFail();
        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isUser());

        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());
    }

    public function test_initial_lpj_can_be_created_with_draft_status(): void
    {
        $this->seed([
            KicapUserSeeder::class,
            LpjTypeSeeder::class,
        ]);

        $admin = User::query()->where('email', 'admin@kicap.id')->firstOrFail();
        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();
        $type = LpjType::query()->where('slug', 'penyelenggaraan-event')->firstOrFail();

        $lpj = Lpj::query()->create([
            'code' => 'LPJ-TEST-001',
            'title' => 'Test LPJ Slice 01',
            'lpj_type_id' => $type->id,
            'created_by' => $admin->id,
            'person_in_charge_id' => $user->id,
            'status' => Lpj::STATUS_DRAFT,
            'completeness_status' => Lpj::COMPLETENESS_BELUM_LENGKAP,
        ]);

        $this->assertSame(Lpj::STATUS_DRAFT, $lpj->status);
        $this->assertSame('Penyelenggaraan Event', $lpj->type->name);
    }

    public function test_lpj_type_endpoint_returns_active_types(): void
    {
        $this->seed(LpjTypeSeeder::class);

        $response = $this->getJson('/api/master/lpj-types');

        $response->assertOk();
        $response->assertJsonFragment([
            'slug' => 'pendampingan-peserta-seleksi',
            'is_external_event' => true,
        ]);
    }
}

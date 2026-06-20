<?php

namespace Tests\Feature;

use App\Models\Lpj;
use App\Models\LpjAssignedUser;
use App\Models\LpjType;
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

    public function test_seeded_users_follow_mvp_role_boundaries(): void
    {
        $this->seed(KicapUserSeeder::class);

        $admin = User::query()->where('email', 'admin@kicap.id')->firstOrFail();
        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();
        $pendamping = User::query()->where('email', 'pendamping@kicap.id')->firstOrFail();

        $this->assertTrue($admin->can_create_lpj);
        $this->assertFalse($user->can_create_lpj);
        $this->assertFalse($pendamping->can_create_lpj);
    }

    public function test_lpj_status_contract_matches_revision(): void
    {
        $this->assertSame([
            'draft',
            'aktif',
            'finish',
            'arsipkan',
        ], Lpj::statuses());
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

    public function test_user_lpj_endpoint_only_returns_assigned_active_and_finish_lpjs(): void
    {
        $this->seed([
            KicapUserSeeder::class,
            LpjTypeSeeder::class,
        ]);

        $admin = User::query()->where('email', 'admin@kicap.id')->firstOrFail();
        $user = User::query()->where('email', 'user@kicap.id')->firstOrFail();
        $type = LpjType::query()->where('slug', 'kegiatan-internal')->firstOrFail();

        $active = $this->makeLpj('LPJ-VISIBLE-AKTIF', Lpj::STATUS_AKTIF, $admin, $type);
        $finish = $this->makeLpj('LPJ-VISIBLE-FINISH', Lpj::STATUS_FINISH, $admin, $type);
        $draft = $this->makeLpj('LPJ-HIDDEN-DRAFT', Lpj::STATUS_DRAFT, $admin, $type);
        $archived = $this->makeLpj('LPJ-HIDDEN-ARSIP', Lpj::STATUS_ARSIPKAN, $admin, $type);
        $unassigned = $this->makeLpj('LPJ-HIDDEN-UNASSIGNED', Lpj::STATUS_AKTIF, $admin, $type);

        foreach ([$active, $finish, $draft, $archived] as $lpj) {
            LpjAssignedUser::query()->create([
                'lpj_id' => $lpj->id,
                'user_id' => $user->id,
            ]);
        }

        $response = $this->actingAs($user)->getJson('/api/app/lpjs');

        $response->assertOk();
        $response->assertJsonFragment(['code' => $active->code]);
        $response->assertJsonFragment(['code' => $finish->code]);
        $response->assertJsonMissing(['code' => $draft->code]);
        $response->assertJsonMissing(['code' => $archived->code]);
        $response->assertJsonMissing(['code' => $unassigned->code]);

        $statuses = collect($response->json('data'))->pluck('status')->unique()->values()->all();

        $this->assertEmpty(array_diff($statuses, Lpj::userVisibleStatuses()));
    }

    private function makeLpj(string $code, string $status, User $admin, LpjType $type): Lpj
    {
        return Lpj::query()->create([
            'code' => $code,
            'title' => $code,
            'lpj_type_id' => $type->id,
            'created_by' => $admin->id,
            'status' => $status,
            'completeness_status' => Lpj::COMPLETENESS_BELUM_LENGKAP,
        ]);
    }
}

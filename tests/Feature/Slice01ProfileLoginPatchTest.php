<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\KicapUserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Slice01ProfileLoginPatchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_profile_columns_are_available(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'username'));
        $this->assertTrue(Schema::hasColumn('users', 'whatsapp'));
        $this->assertTrue(Schema::hasColumn('users', 'profile_photo_path'));
    }

    public function test_admin_can_authenticate_with_email_or_username(): void
    {
        $this->seed(KicapUserSeeder::class);

        $this->assertTrue(Auth::attempt([
            'email' => 'admin@kicap.id',
            'password' => 'password',
        ]));

        Auth::logout();

        $this->assertTrue(Auth::attempt([
            'email' => 'admin',
            'password' => 'password',
        ]));
    }

    public function test_profile_update_does_not_require_changing_username_or_email(): void
    {
        $this->seed(KicapUserSeeder::class);

        $user = User::query()->where('username', 'user')->firstOrFail();

        $oldUsername = $user->username;
        $oldEmail = $user->email;

        $user->forceFill([
            'name' => 'User Lapangan Update',
            'whatsapp' => '081234567890',
        ])->save();

        $user->refresh();

        $this->assertSame($oldUsername, $user->username);
        $this->assertSame($oldEmail, $user->email);
        $this->assertSame('User Lapangan Update', $user->name);
        $this->assertSame('081234567890', $user->whatsapp);
    }
}

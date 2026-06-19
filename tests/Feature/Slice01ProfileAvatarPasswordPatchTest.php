<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\KicapUserSeeder;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Slice01ProfileAvatarPasswordPatchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_model_supports_filament_avatar(): void
    {
        $this->assertContains(HasAvatar::class, class_implements(User::class));
    }

    public function test_filament_avatar_url_uses_profile_photo_path(): void
    {
        $user = new User([
            'name' => 'Admin Kicap',
            'profile_photo_path' => 'profile-photos/admin.jpg',
        ]);

        $this->assertStringContainsString('/storage/profile-photos/admin.jpg', $user->getFilamentAvatarUrl());
    }

    public function test_user_password_can_be_updated_from_profile_payload(): void
    {
        $this->seed(KicapUserSeeder::class);

        $user = User::query()->where('username', 'admin')->firstOrFail();

        $user->forceFill([
            'password' => Hash::make('password-baru'),
        ])->save();

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }
}

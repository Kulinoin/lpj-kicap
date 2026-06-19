<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class KicapUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@kicap.id'],
            [
                'name' => 'Admin Kicap',
                'username' => 'admin',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'can_create_lpj' => true,
                'can_transfer_balance' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'user@kicap.id'],
            [
                'name' => 'User Lapangan',
                'username' => 'user',
                'password' => 'password',
                'role' => User::ROLE_USER,
                'is_active' => true,
                'can_create_lpj' => true,
                'can_transfer_balance' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'pendamping@kicap.id'],
            [
                'name' => 'Pendamping Lapangan',
                'username' => 'pendamping',
                'password' => 'password',
                'role' => User::ROLE_USER,
                'is_active' => true,
                'can_create_lpj' => false,
                'can_transfer_balance' => true,
            ]
        );
    }
}

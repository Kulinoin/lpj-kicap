<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KicapUserSeeder::class,
            OrganizationProfileSeeder::class,
            LpjTypeSeeder::class,
            NarrativeTemplateSeeder::class,
            LpjDemoSeeder::class,
        ]);
    }
}

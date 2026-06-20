<?php

namespace Database\Seeders;

use App\Models\OrganizationProfile;
use Illuminate\Database\Seeder;

class OrganizationProfileSeeder extends Seeder
{
    public function run(): void
    {
        OrganizationProfile::query()->updateOrCreate(
            ['id' => 1],
            [
                'institution_name' => 'PT. Kazoku Indonesia Center',
                'institution_type' => 'Lembaga Pelatihan Kerja',
                'unit_name' => null,
                'address' => '[Diisi kemudian]',
                'phone' => '[Diisi kemudian]',
                'mobile' => '[Diisi kemudian]',
                'email' => '[Diisi kemudian]',
                'website' => '[Diisi kemudian]',
                'logo_path' => null,
                'footer_text' => 'Kicap Event - PT. Kazoku Indonesia Center',
                'default_city' => '[Diisi kemudian]',
                'leader_name' => '[Diisi kemudian]',
                'leader_position' => '[Diisi kemudian]',
            ]
        );
    }
}

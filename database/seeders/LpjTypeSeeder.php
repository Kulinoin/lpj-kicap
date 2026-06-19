<?php

namespace Database\Seeders;

use App\Models\LpjType;
use Illuminate\Database\Seeder;

class LpjTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Penyelenggaraan Event',
                'slug' => 'penyelenggaraan-event',
                'description' => 'Untuk kegiatan yang diselenggarakan sendiri.',
                'is_external_event' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pendampingan Peserta Seleksi',
                'slug' => 'pendampingan-peserta-seleksi',
                'description' => 'Untuk mendampingi peserta mengikuti seleksi/lomba/audisi/kegiatan pihak luar.',
                'is_external_event' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Delegasi / Perwakilan',
                'slug' => 'delegasi-perwakilan',
                'description' => 'Untuk mengirim peserta/tim mewakili lembaga atau organisasi.',
                'is_external_event' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Bantuan Dana / Sponsorship',
                'slug' => 'bantuan-dana-sponsorship',
                'description' => 'Untuk pertanggungjawaban penggunaan dana bantuan atau sponsorship.',
                'is_external_event' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Kegiatan Internal',
                'slug' => 'kegiatan-internal',
                'description' => 'Untuk rapat, pelatihan, workshop, program internal, atau operasional lembaga.',
                'is_external_event' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            LpjType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                array_merge($type, ['is_active' => true])
            );
        }
    }
}

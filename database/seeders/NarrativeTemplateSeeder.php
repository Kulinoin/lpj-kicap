<?php

namespace Database\Seeders;

use App\Models\LpjNarrative;
use App\Models\LpjType;
use App\Models\NarrativeTemplate;
use Illuminate\Database\Seeder;

class NarrativeTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'penyelenggaraan-event' => [
                LpjNarrative::SECTION_BACKGROUND => 'Kegiatan {{ judul_lpj }} dilaksanakan sebagai bagian dari program {{ tipe_lpj }} yang dirancang untuk mendukung kebutuhan lembaga dan peserta.',
                LpjNarrative::SECTION_PURPOSE => 'Tujuan kegiatan ini adalah memastikan penyelenggaraan berjalan tertib, terukur, dan memberi manfaat nyata bagi peserta serta lembaga.',
                LpjNarrative::SECTION_OBJECTIVE => 'Sasaran kegiatan meliputi peserta, panitia, dan pihak terkait yang terlibat langsung dalam pelaksanaan di {{ lokasi }}.',
                LpjNarrative::SECTION_EXECUTION => 'Kegiatan dilaksanakan pada {{ tanggal_mulai }} sampai {{ tanggal_selesai }} di {{ lokasi }} dengan dukungan pendanaan dari {{ sumber_dana }}.',
                LpjNarrative::SECTION_RESULT => 'Hasil kegiatan dicatat berdasarkan capaian pelaksanaan, kehadiran peserta, dokumentasi, dan keluaran yang diperoleh selama kegiatan berlangsung.',
                LpjNarrative::SECTION_EVALUATION => 'Evaluasi mencakup hal yang berjalan baik, kendala di lapangan, serta saran perbaikan untuk kegiatan berikutnya.',
                LpjNarrative::SECTION_CLOSING => 'Demikian narasi laporan pertanggungjawaban ini disusun sebagai dasar dokumentasi dan evaluasi kegiatan.',
            ],
            'pendampingan-peserta-seleksi' => [
                LpjNarrative::SECTION_BACKGROUND => 'Pendampingan {{ judul_lpj }} dilakukan untuk membantu peserta mengikuti proses seleksi yang diselenggarakan oleh {{ penyelenggara_eksternal }}.',
                LpjNarrative::SECTION_PURPOSE => 'Tujuan pendampingan adalah memastikan peserta siap secara administrasi, teknis, dan koordinasi selama mengikuti seleksi.',
                LpjNarrative::SECTION_OBJECTIVE => 'Sasaran pendampingan adalah peserta yang ditugaskan serta petugas yang mendampingi proses seleksi di {{ lokasi }}.',
                LpjNarrative::SECTION_EXECUTION => 'Pendampingan berlangsung pada {{ tanggal_mulai }} sampai {{ tanggal_selesai }} di {{ lokasi }} dengan peran organisasi sebagai {{ peran_organisasi }}.',
                LpjNarrative::SECTION_RESULT => 'Hasil pendampingan dicatat dari kelancaran proses seleksi, kebutuhan peserta yang terpenuhi, dan informasi hasil yang tersedia.',
                LpjNarrative::SECTION_EVALUATION => 'Evaluasi berisi catatan kesiapan peserta, kendala koordinasi, dan rekomendasi pendampingan selanjutnya.',
                LpjNarrative::SECTION_CLOSING => 'Narasi ini menjadi bagian dari laporan pertanggungjawaban pendampingan peserta seleksi.',
            ],
            'delegasi-perwakilan' => [
                LpjNarrative::SECTION_BACKGROUND => '{{ judul_lpj }} dilaksanakan sebagai bentuk delegasi atau perwakilan lembaga dalam kegiatan yang relevan dengan program kerja.',
                LpjNarrative::SECTION_PURPOSE => 'Tujuan delegasi adalah membawa mandat lembaga, membangun jejaring, dan mengikuti kegiatan sesuai penugasan.',
                LpjNarrative::SECTION_OBJECTIVE => 'Sasaran kegiatan adalah delegasi yang ditugaskan, pihak penyelenggara, dan mitra yang terlibat.',
                LpjNarrative::SECTION_EXECUTION => 'Delegasi dilaksanakan pada {{ tanggal_mulai }} sampai {{ tanggal_selesai }} di {{ lokasi }} dengan peran organisasi sebagai {{ peran_organisasi }}.',
                LpjNarrative::SECTION_RESULT => 'Hasil delegasi meliputi kegiatan yang diikuti, informasi yang diperoleh, dan tindak lanjut yang perlu dilakukan.',
                LpjNarrative::SECTION_EVALUATION => 'Evaluasi mencakup manfaat penugasan, kendala perjalanan atau koordinasi, dan saran tindak lanjut.',
                LpjNarrative::SECTION_CLOSING => 'Laporan ini disusun sebagai pertanggungjawaban atas penugasan delegasi atau perwakilan lembaga.',
            ],
            'bantuan-dana-sponsorship' => [
                LpjNarrative::SECTION_BACKGROUND => '{{ judul_lpj }} disusun untuk mempertanggungjawabkan penggunaan bantuan dana atau sponsorship dari {{ sumber_dana }}.',
                LpjNarrative::SECTION_PURPOSE => 'Tujuan laporan adalah menjelaskan penggunaan dana secara transparan, tertib, dan sesuai kebutuhan kegiatan.',
                LpjNarrative::SECTION_OBJECTIVE => 'Sasaran kegiatan adalah penerima manfaat, pelaksana kegiatan, dan pihak pemberi dukungan dana.',
                LpjNarrative::SECTION_EXECUTION => 'Pelaksanaan kegiatan dilakukan pada {{ tanggal_mulai }} sampai {{ tanggal_selesai }} di {{ lokasi }} sesuai rencana penggunaan dana.',
                LpjNarrative::SECTION_RESULT => 'Hasil kegiatan dicatat berdasarkan realisasi manfaat, penggunaan dana, dan bukti pendukung yang tersedia.',
                LpjNarrative::SECTION_EVALUATION => 'Evaluasi mencakup efektivitas penggunaan dana, kendala realisasi, dan rekomendasi pengelolaan berikutnya.',
                LpjNarrative::SECTION_CLOSING => 'Demikian laporan narasi ini disusun sebagai bentuk pertanggungjawaban penggunaan bantuan dana atau sponsorship.',
            ],
            'kegiatan-internal' => [
                LpjNarrative::SECTION_BACKGROUND => '{{ judul_lpj }} merupakan kegiatan internal yang dilaksanakan untuk mendukung operasional, koordinasi, dan penguatan program lembaga.',
                LpjNarrative::SECTION_PURPOSE => 'Tujuan kegiatan adalah memperkuat koordinasi internal, menyelesaikan kebutuhan operasional, dan mendukung capaian program lembaga.',
                LpjNarrative::SECTION_OBJECTIVE => 'Sasaran kegiatan adalah tim internal, peserta, atau unit kerja yang terlibat sesuai kebutuhan kegiatan.',
                LpjNarrative::SECTION_EXECUTION => 'Kegiatan berlangsung pada {{ tanggal_mulai }} sampai {{ tanggal_selesai }} di {{ lokasi }} dengan dukungan pendanaan dari {{ sumber_dana }}.',
                LpjNarrative::SECTION_RESULT => 'Hasil kegiatan dicatat berdasarkan keputusan, keluaran kerja, dokumentasi, dan tindak lanjut yang dihasilkan.',
                LpjNarrative::SECTION_EVALUATION => 'Evaluasi berisi catatan pelaksanaan, kendala internal, dan saran perbaikan untuk kegiatan selanjutnya.',
                LpjNarrative::SECTION_CLOSING => 'Narasi ini disusun sebagai bagian dari laporan pertanggungjawaban kegiatan internal lembaga.',
            ],
        ];

        foreach ($templates as $typeSlug => $sections) {
            $type = LpjType::query()->where('slug', $typeSlug)->first();

            if (! $type) {
                continue;
            }

            foreach (LpjNarrative::sections() as $index => $section) {
                NarrativeTemplate::query()->updateOrCreate(
                    [
                        'lpj_type_id' => $type->id,
                        'section' => $section,
                    ],
                    [
                        'title' => LpjNarrative::sectionLabels()[$section],
                        'content' => $sections[$section],
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}

<?php

namespace App\Services;

use App\Models\Lpj;
use App\Models\LpjNarrative;
use App\Models\NarrativeTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LpjNarrativeService
{
    public function ensureForLpj(Lpj $lpj): Collection
    {
        $lpj->loadMissing('type');

        $existing = $lpj->narratives()->get()->keyBy('section');
        $templates = NarrativeTemplate::query()
            ->where('lpj_type_id', $lpj->lpj_type_id)
            ->active()
            ->get()
            ->keyBy('section');

        foreach (LpjNarrative::sections() as $section) {
            if ($existing->has($section)) {
                continue;
            }

            $template = $templates->get($section);

            $existing->put($section, $lpj->narratives()->create([
                'section' => $section,
                'content' => $template
                    ? $this->render($template->content, $lpj)
                    : $this->defaultContent($section, $lpj),
                'generated_from_template_id' => $template?->id,
                'is_edited' => false,
            ]));
        }

        return $this->sortBySection($existing->values());
    }

    public function payload(Collection $narratives): array
    {
        $labels = LpjNarrative::sectionLabels();

        return $this->sortBySection($narratives)->map(fn (LpjNarrative $narrative): array => [
            'section' => $narrative->section,
            'label' => $labels[$narrative->section] ?? Str::headline($narrative->section),
            'content' => $narrative->content,
            'is_edited' => $narrative->is_edited,
            'updated_at' => $narrative->updated_at?->toISOString(),
        ])->values()->all();
    }

    private function sortBySection(Collection $narratives): Collection
    {
        $order = array_flip(LpjNarrative::sections());

        return $narratives->sortBy(fn (LpjNarrative $narrative): int => $order[$narrative->section] ?? 999)->values();
    }

    private function render(string $content, Lpj $lpj): string
    {
        $values = [
            'judul_lpj' => $lpj->title,
            'kode_lpj' => $lpj->code,
            'tipe_lpj' => $lpj->type?->name ?? 'kegiatan',
            'tanggal_mulai' => $lpj->start_date?->translatedFormat('d F Y') ?? 'tanggal yang ditentukan',
            'tanggal_selesai' => $lpj->end_date?->translatedFormat('d F Y') ?? 'tanggal yang ditentukan',
            'lokasi' => $lpj->location ?: 'lokasi kegiatan',
            'sumber_dana' => $lpj->funding_source ?: 'sumber dana kegiatan',
            'penyelenggara_eksternal' => $lpj->external_organizer ?: 'penyelenggara terkait',
            'peran_organisasi' => $lpj->organization_role ?: 'peran organisasi',
        ];

        foreach ($values as $key => $value) {
            $content = str_replace('{{ '.$key.' }}', $value, $content);
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }

        return $content;
    }

    private function defaultContent(string $section, Lpj $lpj): string
    {
        $label = LpjNarrative::sectionLabels()[$section] ?? 'Narasi';

        return $label.' '.$lpj->title.' dapat dilengkapi oleh petugas sesuai kondisi lapangan.';
    }
}

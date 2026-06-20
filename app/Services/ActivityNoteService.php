<?php

namespace App\Services;

use App\Models\ActivityNote;
use App\Models\Lpj;
use App\Models\User;
use Illuminate\Support\Collection;

class ActivityNoteService
{
    public function ensureForUser(Lpj $lpj, User $user): Collection
    {
        $existing = $lpj->activityNotes()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('type');

        foreach (ActivityNote::types() as $type) {
            if ($existing->has($type)) {
                continue;
            }

            $existing->put($type, $lpj->activityNotes()->create([
                'user_id' => $user->id,
                'type' => $type,
                'content' => '',
                'include_in_report' => false,
            ]));
        }

        return $this->sortByType($existing->values());
    }

    public function payload(Collection $notes): array
    {
        $labels = ActivityNote::typeLabels();

        return $this->sortByType($notes)->map(fn (ActivityNote $note): array => [
            'type' => $note->type,
            'label' => $labels[$note->type] ?? $note->type,
            'content' => $note->content,
            'include_in_report' => $note->include_in_report,
            'updated_at' => $note->updated_at?->toISOString(),
        ])->values()->all();
    }

    private function sortByType(Collection $notes): Collection
    {
        $order = array_flip(ActivityNote::types());

        return $notes->sortBy(fn (ActivityNote $note): int => $order[$note->type] ?? 999)->values();
    }
}

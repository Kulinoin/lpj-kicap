<?php

namespace App\Services;

use App\Models\ActivityParticipant;
use App\Models\ActivitySelectionStage;
use App\Models\Lpj;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ActivitySelectionPayloadService
{
    public function payload(Lpj $lpj, User $user): array
    {
        app(ActivitySelectionService::class)->ensureDefaultForLpj($lpj);

        $assignment = $lpj->assignedUsers()
            ->where('user_id', $user->id)
            ->first();

        $participants = ActivityParticipant::query()
            ->where('lpj_id', $lpj->id)
            ->with([
                'creator:id,name',
                'registeredBy:id,name',
                'currentSelectionStage:id,name',
                'eliminatedStage:id,name',
                'eliminatedTest:id,name',
                'testResults.stage:id,name,sort_order',
                'testResults.test:id,name,sort_order,result_label,note_label',
                'testResults.creator:id,name',
                'testResults.updater:id,name',
            ])
            ->orderByRaw('participant_number is null')
            ->orderBy('participant_number')
            ->orderBy('name')
            ->get();

        foreach ($participants as $participant) {
            app(ActivitySelectionService::class)->ensureParticipantResults($participant);
        }

        $participants = ActivityParticipant::query()
            ->where('lpj_id', $lpj->id)
            ->with([
                'creator:id,name',
                'registeredBy:id,name',
                'currentSelectionStage:id,name',
                'eliminatedStage:id,name',
                'eliminatedTest:id,name',
                'testResults.stage:id,name,sort_order',
                'testResults.test:id,name,sort_order,result_label,note_label',
                'testResults.creator:id,name',
                'testResults.updater:id,name',
            ])
            ->orderByRaw('participant_number is null')
            ->orderBy('participant_number')
            ->orderBy('name')
            ->get();

        $stages = ActivitySelectionStage::query()
            ->where('lpj_id', $lpj->id)
            ->with(['tests' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $summary = [
            'total' => $participants->count(),
            'belum_registrasi' => $participants->where('selection_registration_status', ActivityParticipant::REGISTRATION_STATUS_UNREGISTERED)->count(),
            'sudah_registrasi' => $participants->where('selection_registration_status', ActivityParticipant::REGISTRATION_STATUS_REGISTERED)->count(),
            'aktif' => $participants->where('selection_status', ActivityParticipant::SELECTION_STATUS_ACTIVE)->count(),
            'gugur' => $participants->where('selection_status', ActivityParticipant::SELECTION_STATUS_ELIMINATED)->count(),
            'lulus' => $participants->where('selection_status', ActivityParticipant::SELECTION_STATUS_PASSED)->count(),
        ];

        return [
            'can_manage_selection' => (bool) $assignment,
            'registration_status_labels' => ActivityParticipant::registrationStatusLabels(),
            'selection_status_labels' => ActivityParticipant::selectionStatusLabels(),
            'summary' => $summary,
            'stages' => $stages->map(fn ($stage): array => [
                'id' => $stage->id,
                'name' => $stage->name,
                'slug' => $stage->slug,
                'sort_order' => $stage->sort_order,
                'is_elimination' => (bool) $stage->is_elimination,
                'tests' => $stage->tests->map(fn ($test): array => [
                    'id' => $test->id,
                    'name' => $test->name,
                    'slug' => $test->slug,
                    'sort_order' => $test->sort_order,
                    'result_label' => $test->result_label,
                    'note_label' => $test->note_label,
                    'is_elimination' => (bool) $test->is_elimination,
                    'requires_reason_on_fail' => (bool) $test->requires_reason_on_fail,
                    'requires_attachment_on_fail' => (bool) $test->requires_attachment_on_fail,
                    'allows_pass_with_note' => (bool) $test->allows_pass_with_note,
                    'requires_attachment_on_pass_with_note' => (bool) $test->requires_attachment_on_pass_with_note,
                ])->values(),
            ])->values(),
            'participants' => $participants->map(fn (ActivityParticipant $participant): array => $this->participantPayload($participant))->values(),
        ];
    }

    public function createParticipant(Lpj $lpj, User $user, array $data): ActivityParticipant
    {
        return DB::transaction(function () use ($lpj, $user, $data): ActivityParticipant {
            $participant = ActivityParticipant::query()->create([
                'lpj_id' => $lpj->id,
                'created_by' => $user->id,
                'name' => $data['name'],
                'origin' => $data['origin'] ?? null,
                'participant_number' => $data['participant_number'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'attendance_status' => 'hadir',
                'result_status' => null,
                'selection_registration_status' => filled($data['participant_number'] ?? null)
                    ? ActivityParticipant::REGISTRATION_STATUS_REGISTERED
                    : ActivityParticipant::REGISTRATION_STATUS_UNREGISTERED,
                'selection_status' => filled($data['participant_number'] ?? null)
                    ? ActivityParticipant::SELECTION_STATUS_ACTIVE
                    : ActivityParticipant::SELECTION_STATUS_NOT_STARTED,
                'registered_by' => filled($data['participant_number'] ?? null) ? $user->id : null,
                'registered_at' => filled($data['participant_number'] ?? null) ? now() : null,
                'note' => $data['note'] ?? null,
            ]);

            app(ActivitySelectionService::class)->ensureParticipantResults($participant);

            return $participant->fresh();
        });
    }

    public function updateRegistration(
        Lpj $lpj,
        ActivityParticipant $participant,
        User $user,
        array $data,
        ?UploadedFile $photo
    ): ActivityParticipant {
        abort_unless($participant->lpj_id === $lpj->id, 404);

        return DB::transaction(function () use ($participant, $user, $data, $photo): ActivityParticipant {
            $payload = [
                'participant_number' => $data['participant_number'] ?? $participant->participant_number,
                'whatsapp' => $data['whatsapp'] ?? null,
                'note' => $data['note'] ?? null,
            ];

            if (filled($payload['participant_number'])) {
                $payload['selection_registration_status'] = ActivityParticipant::REGISTRATION_STATUS_REGISTERED;
                $payload['selection_status'] = $participant->selection_status === ActivityParticipant::SELECTION_STATUS_NOT_STARTED
                    ? ActivityParticipant::SELECTION_STATUS_ACTIVE
                    : $participant->selection_status;
                $payload['registered_by'] = $participant->registered_by ?: $user->id;
                $payload['registered_at'] = $participant->registered_at ?: now();
            } else {
                $payload['selection_registration_status'] = ActivityParticipant::REGISTRATION_STATUS_UNREGISTERED;
                $payload['selection_status'] = ActivityParticipant::SELECTION_STATUS_NOT_STARTED;
                $payload['registered_by'] = null;
                $payload['registered_at'] = null;
            }

            if ($photo) {
                if (filled($participant->photo_path)) {
                    app(AppFileStorageService::class)->delete($participant->photo_path, $participant->photo_disk);
                }

                $stored = app(AppFileStorageService::class)->store($photo, 'participant-photos');

                $payload['photo_path'] = $stored['path'];
                $payload['photo_disk'] = $stored['disk'];
                $payload['photo_original_name'] = $photo->getClientOriginalName();
                $payload['photo_mime_type'] = $stored['mime_type'] ?? $photo->getMimeType();
                $payload['photo_size'] = $stored['size'] ?? $photo->getSize();
            }

            $participant->forceFill($payload)->save();

            app(ActivitySelectionService::class)->ensureParticipantResults($participant->fresh());

            return $participant->fresh();
        });
    }

    private function participantPayload(ActivityParticipant $participant): array
    {
        $photoUrl = filled($participant->photo_path)
            ? app(AppFileStorageService::class)->url($participant->photo_path, $participant->photo_disk)
            : null;

        return [
            'id' => $participant->id,
            'name' => $participant->name,
            'origin' => $participant->origin,
            'participant_number' => $participant->participant_number,
            'whatsapp' => $participant->whatsapp,
            'photo_url' => $photoUrl,
            'note' => $participant->note,
            'registration_status' => $participant->selection_registration_status,
            'registration_status_label' => ActivityParticipant::registrationStatusLabels()[$participant->selection_registration_status] ?? $participant->selection_registration_status,
            'selection_status' => $participant->selection_status,
            'selection_status_label' => ActivityParticipant::selectionStatusLabels()[$participant->selection_status] ?? $participant->selection_status,
            'registered_at' => optional($participant->registered_at)->toDateTimeString(),
            'created_by' => $participant->creator?->name,
            'registered_by' => $participant->registeredBy?->name,
            'current_stage' => $participant->currentSelectionStage?->name,
            'eliminated_stage' => $participant->eliminatedStage?->name,
            'eliminated_test' => $participant->eliminatedTest?->name,
            'test_results' => $participant->testResults
                ->sortBy([
                    fn ($a, $b) => ($a->stage?->sort_order ?? 999) <=> ($b->stage?->sort_order ?? 999),
                    fn ($a, $b) => ($a->test?->sort_order ?? 999) <=> ($b->test?->sort_order ?? 999),
                ])
                ->map(fn ($result): array => [
                    'id' => $result->id,
                    'stage_id' => $result->activity_selection_stage_id,
                    'stage_name' => $result->stage?->name,
                    'test_id' => $result->activity_selection_test_id,
                    'test_name' => $result->test?->name,
                    'status' => $result->status,
                    'status_label' => \App\Models\ActivityParticipantTestResult::statusLabels()[$result->status] ?? $result->status,
                    'result_value' => $result->result_value,
                    'note' => $result->note,
                    'updated_by' => $result->updater?->name,
                    'assessed_at' => optional($result->assessed_at)->toDateTimeString(),
                ])
                ->values(),
        ];
    }
}

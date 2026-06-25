<?php

namespace App\Services;

use App\Models\ActivityParticipant;
use App\Models\ActivityParticipantTestResult;
use App\Models\ActivitySelectionStage;
use App\Models\ActivitySelectionTest;
use App\Models\Lpj;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActivitySelectionService
{
    public function defaultTemplate(): array
    {
        return [
            [
                'name' => 'Ujian Tulis',
                'slug' => 'ujian_tulis',
                'sort_order' => 1,
                'is_elimination' => true,
                'tests' => [
                    [
                        'name' => 'Matematika',
                        'slug' => 'matematika',
                        'sort_order' => 1,
                        'result_label' => 'Nilai Matematika',
                        'note_label' => 'Keterangan Ujian Tulis',
                        'is_elimination' => true,
                        'requires_reason_on_fail' => true,
                        'requires_attachment_on_fail' => false,
                    ],
                ],
            ],
            [
                'name' => 'Kesemaptaan',
                'slug' => 'kesemaptaan',
                'sort_order' => 2,
                'is_elimination' => true,
                'tests' => [
                    [
                        'name' => 'Pemeriksaan Fisik',
                        'slug' => 'pemeriksaan_fisik',
                        'sort_order' => 1,
                        'result_label' => 'Hasil Pemeriksaan',
                        'note_label' => 'Gagal Karena / Catatan Kesehatan',
                        'is_elimination' => true,
                        'requires_reason_on_fail' => true,
                        'requires_attachment_on_fail' => false,
                        'allows_pass_with_note' => true,
                        'requires_attachment_on_pass_with_note' => true,
                    ],
                ],
            ],
            [
                'name' => 'Ujian Fisik',
                'slug' => 'ujian_fisik',
                'sort_order' => 3,
                'is_elimination' => true,
                'tests' => [
                    [
                        'name' => 'Lari',
                        'slug' => 'lari',
                        'sort_order' => 1,
                        'result_label' => 'Hasil Lari',
                        'note_label' => 'Keterangan / Alasan Gagal',
                        'is_elimination' => true,
                        'requires_reason_on_fail' => true,
                        'requires_attachment_on_fail' => true,
                    ],
                    [
                        'name' => 'Push Up',
                        'slug' => 'push_up',
                        'sort_order' => 2,
                        'result_label' => 'Jumlah Push Up',
                        'note_label' => 'Keterangan / Alasan Gagal',
                        'is_elimination' => true,
                        'requires_reason_on_fail' => true,
                        'requires_attachment_on_fail' => true,
                    ],
                    [
                        'name' => 'Sit Up',
                        'slug' => 'sit_up',
                        'sort_order' => 3,
                        'result_label' => 'Jumlah Sit Up',
                        'note_label' => 'Keterangan / Alasan Gagal',
                        'is_elimination' => true,
                        'requires_reason_on_fail' => true,
                        'requires_attachment_on_fail' => true,
                    ],
                ],
            ],
            [
                'name' => 'Wawancara',
                'slug' => 'wawancara',
                'sort_order' => 4,
                'is_elimination' => true,
                'tests' => [
                    [
                        'name' => 'Wawancara',
                        'slug' => 'wawancara',
                        'sort_order' => 1,
                        'result_label' => 'Hasil Wawancara',
                        'note_label' => 'Catatan Wawancara',
                        'is_elimination' => true,
                        'requires_reason_on_fail' => true,
                        'requires_attachment_on_fail' => false,
                    ],
                ],
            ],
        ];
    }

    public function ensureDefaultForLpj(Lpj $lpj): void
    {
        DB::transaction(function () use ($lpj): void {
            foreach ($this->defaultTemplate() as $stageData) {
                $tests = $stageData['tests'];
                unset($stageData['tests']);

                $stage = ActivitySelectionStage::query()->firstOrCreate(
                    [
                        'lpj_id' => $lpj->id,
                        'slug' => $stageData['slug'],
                    ],
                    [
                        'name' => $stageData['name'],
                        'sort_order' => $stageData['sort_order'],
                        'is_elimination' => $stageData['is_elimination'],
                        'is_active' => true,
                    ],
                );

                foreach ($tests as $testData) {
                    ActivitySelectionTest::query()->firstOrCreate(
                        [
                            'lpj_id' => $lpj->id,
                            'activity_selection_stage_id' => $stage->id,
                            'slug' => $testData['slug'],
                        ],
                        [
                            'name' => $testData['name'],
                            'sort_order' => $testData['sort_order'],
                            'is_required' => $testData['is_required'] ?? true,
                            'is_elimination' => $testData['is_elimination'] ?? true,
                            'requires_reason_on_fail' => $testData['requires_reason_on_fail'] ?? true,
                            'requires_attachment_on_fail' => $testData['requires_attachment_on_fail'] ?? false,
                            'allows_pass_with_note' => $testData['allows_pass_with_note'] ?? false,
                            'requires_attachment_on_pass_with_note' => $testData['requires_attachment_on_pass_with_note'] ?? false,
                            'result_label' => $testData['result_label'] ?? null,
                            'note_label' => $testData['note_label'] ?? null,
                        ],
                    );
                }
            }
        });
    }

    public function ensureParticipantResults(ActivityParticipant $participant): void
    {
        $participant->loadMissing('lpj');

        DB::transaction(function () use ($participant): void {
            $this->ensureDefaultForLpj($participant->lpj);

            $tests = ActivitySelectionTest::query()
                ->where('lpj_id', $participant->lpj_id)
                ->with('stage')
                ->orderBy('sort_order')
                ->get();

            foreach ($tests as $test) {
                ActivityParticipantTestResult::query()->firstOrCreate(
                    [
                        'activity_participant_id' => $participant->id,
                        'activity_selection_test_id' => $test->id,
                    ],
                    [
                        'lpj_id' => $participant->lpj_id,
                        'activity_selection_stage_id' => $test->activity_selection_stage_id,
                        'status' => ActivityParticipantTestResult::STATUS_PENDING,
                    ],
                );
            }

            $this->recalculateParticipantStatus($participant->fresh());
            return;
        });
    }

    public function registerParticipant(ActivityParticipant $participant, string $participantNumber, ?User $user = null): void
    {
        $participant->forceFill([
            'participant_number' => $participantNumber,
            'selection_registration_status' => ActivityParticipant::REGISTRATION_STATUS_REGISTERED,
            'selection_status' => ActivityParticipant::SELECTION_STATUS_ACTIVE,
            'registered_by' => $user?->id,
            'registered_at' => $participant->registered_at ?? now(),
        ])->save();

        $this->ensureParticipantResults($participant);
    }

    public function recalculateParticipantStatus(ActivityParticipant $participant): void
    {
        $participant->loadMissing('testResults');

        $failed = $participant->testResults()
            ->whereIn('status', [
                ActivityParticipantTestResult::STATUS_FAILED,
                ActivityParticipantTestResult::STATUS_ABSENT,
            ])
            ->with(['stage', 'test'])
            ->join('activity_selection_stages', 'activity_selection_stages.id', '=', 'activity_participant_test_results.activity_selection_stage_id')
            ->join('activity_selection_tests', 'activity_selection_tests.id', '=', 'activity_participant_test_results.activity_selection_test_id')
            ->orderBy('activity_selection_stages.sort_order')
            ->orderBy('activity_selection_tests.sort_order')
            ->select('activity_participant_test_results.*')
            ->first();

        if ($failed) {
            $participant->forceFill([
                'selection_status' => ActivityParticipant::SELECTION_STATUS_ELIMINATED,
                'current_selection_stage_id' => $failed->activity_selection_stage_id,
                'eliminated_selection_stage_id' => $failed->activity_selection_stage_id,
                'eliminated_selection_test_id' => $failed->activity_selection_test_id,
                'result_status' => ActivityParticipant::SELECTION_STATUS_ELIMINATED,
            ])->save();

            return;
        }

        $pending = $participant->testResults()
            ->where('status', ActivityParticipantTestResult::STATUS_PENDING)
            ->exists();

        $isRegistered = $participant->selection_registration_status === ActivityParticipant::REGISTRATION_STATUS_REGISTERED
            || filled($participant->participant_number);

        $participant->forceFill([
            'selection_registration_status' => $isRegistered ? ActivityParticipant::REGISTRATION_STATUS_REGISTERED : ActivityParticipant::REGISTRATION_STATUS_UNREGISTERED,
            'selection_status' => ! $isRegistered
                ? ActivityParticipant::SELECTION_STATUS_NOT_STARTED
                : ($pending ? ActivityParticipant::SELECTION_STATUS_ACTIVE : ActivityParticipant::SELECTION_STATUS_PASSED),
            'eliminated_selection_stage_id' => null,
            'eliminated_selection_test_id' => null,
            'result_status' => $pending ? $participant->result_status : ActivityParticipant::SELECTION_STATUS_PASSED,
        ])->save();
    }
}
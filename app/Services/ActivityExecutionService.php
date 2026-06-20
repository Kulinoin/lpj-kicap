<?php

namespace App\Services;

use App\Models\ActivityAttachment;
use App\Models\ActivityCommittee;
use App\Models\ActivityDocumentation;
use App\Models\ActivityParticipant;
use App\Models\ActivitySchedule;
use App\Models\Lpj;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ActivityExecutionService
{
    public function replaceExecutionData(Lpj $lpj, User $user, array $data): void
    {
        $this->ensureUserCanEditActivity($lpj, $user);

        DB::transaction(function () use ($lpj, $user, $data): void {
            $lpj->participants()->delete();
            foreach ($this->filledRows($data['participants'] ?? [], 'name') as $participant) {
                $lpj->participants()->create([
                    'created_by' => $user->id,
                    'name' => $participant['name'],
                    'origin' => $participant['origin'] ?? null,
                    'participant_number' => $participant['participant_number'] ?? null,
                    'attendance_status' => $participant['attendance_status'] ?? ActivityParticipant::ATTENDANCE_PRESENT,
                    'result_status' => $participant['result_status'] ?? null,
                    'note' => $participant['note'] ?? null,
                ]);
            }

            $lpj->committees()->delete();
            foreach ($this->filledRows($data['committees'] ?? [], 'name') as $committee) {
                $lpj->committees()->create([
                    'created_by' => $user->id,
                    'name' => $committee['name'],
                    'role' => $committee['role'] ?? 'Petugas',
                    'task' => $committee['task'] ?? null,
                    'contact' => $committee['contact'] ?? null,
                ]);
            }

            $lpj->schedules()->delete();
            foreach ($this->filledRows($data['schedules'] ?? [], 'activity_name') as $index => $schedule) {
                $lpj->schedules()->create([
                    'created_by' => $user->id,
                    'start_time' => $schedule['start_time'] ?? null,
                    'end_time' => $schedule['end_time'] ?? null,
                    'activity_name' => $schedule['activity_name'],
                    'responsible_person' => $schedule['responsible_person'] ?? null,
                    'note' => $schedule['note'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
        });
    }

    public function storeDocumentation(Lpj $lpj, User $user, array $data, UploadedFile $file): ActivityDocumentation
    {
        $this->ensureUserCanUploadDocumentation($lpj, $user);

        return $lpj->documentations()->create([
            'uploaded_by' => $user->id,
            'category' => $data['category'],
            ...$this->filePayload($file, 'activity-documentations'),
            'original_name' => $file->getClientOriginalName(),
            'caption' => $data['caption'] ?? null,
            'include_in_report' => (bool) ($data['include_in_report'] ?? false),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function storeAttachment(Lpj $lpj, User $user, array $data, UploadedFile $file): ActivityAttachment
    {
        $this->ensureUserCanUploadDocumentation($lpj, $user);

        return $lpj->attachments()->create([
            'uploaded_by' => $user->id,
            'title' => $data['title'],
            ...$this->filePayload($file, 'activity-attachments'),
            'original_name' => $file->getClientOriginalName(),
            'description' => $data['description'] ?? null,
            'include_in_report' => (bool) ($data['include_in_report'] ?? false),
        ]);
    }

    public function payload(Lpj $lpj, User $user): array
    {
        $assignment = $lpj->assignedUsers()
            ->where('user_id', $user->id)
            ->first();

        return [
            'can_edit_activity_data' => $lpj->status === Lpj::STATUS_AKTIF && (bool) $assignment?->can_edit_activity_data,
            'can_upload_documentation' => $lpj->status === Lpj::STATUS_AKTIF && (bool) $assignment?->can_upload_documentation,
            'attendance_options' => ActivityParticipant::attendanceOptions(),
            'documentation_categories' => ActivityDocumentation::categoryOptions(),
            'participants' => $lpj->participants()
                ->oldest('id')
                ->get()
                ->map(fn (ActivityParticipant $participant): array => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'origin' => $participant->origin,
                    'participant_number' => $participant->participant_number,
                    'attendance_status' => $participant->attendance_status,
                    'attendance_label' => ActivityParticipant::attendanceOptions()[$participant->attendance_status] ?? $participant->attendance_status,
                    'result_status' => $participant->result_status,
                    'note' => $participant->note,
                ])
                ->values(),
            'committees' => $lpj->committees()
                ->oldest('id')
                ->get()
                ->map(fn (ActivityCommittee $committee): array => [
                    'id' => $committee->id,
                    'name' => $committee->name,
                    'role' => $committee->role,
                    'task' => $committee->task,
                    'contact' => $committee->contact,
                ])
                ->values(),
            'schedules' => $lpj->schedules()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (ActivitySchedule $schedule): array => [
                    'id' => $schedule->id,
                    'start_time' => $schedule->start_time?->format('Y-m-d\TH:i'),
                    'end_time' => $schedule->end_time?->format('Y-m-d\TH:i'),
                    'activity_name' => $schedule->activity_name,
                    'responsible_person' => $schedule->responsible_person,
                    'note' => $schedule->note,
                    'sort_order' => $schedule->sort_order,
                ])
                ->values(),
            'documentations' => $lpj->documentations()
                ->orderBy('sort_order')
                ->latest('id')
                ->get()
                ->map(fn (ActivityDocumentation $documentation): array => [
                    'id' => $documentation->id,
                    'category' => $documentation->category,
                    'category_label' => ActivityDocumentation::categoryOptions()[$documentation->category] ?? $documentation->category,
                    'caption' => $documentation->caption,
                    'include_in_report' => $documentation->include_in_report,
                    'original_name' => $documentation->original_name,
                    'mime_type' => $documentation->mime_type,
                    'is_image' => str_starts_with((string) $documentation->mime_type, 'image/'),
                    'url' => app(AppFileStorageService::class)->url($documentation->file_path, $documentation->file_disk),
                ])
                ->values(),
            'attachments' => $lpj->attachments()
                ->latest('id')
                ->get()
                ->map(fn (ActivityAttachment $attachment): array => [
                    'id' => $attachment->id,
                    'title' => $attachment->title,
                    'description' => $attachment->description,
                    'include_in_report' => $attachment->include_in_report,
                    'original_name' => $attachment->original_name,
                    'mime_type' => $attachment->mime_type,
                    'is_image' => str_starts_with((string) $attachment->mime_type, 'image/'),
                    'url' => app(AppFileStorageService::class)->url($attachment->file_path, $attachment->file_disk),
                ])
                ->values(),
        ];
    }

    private function ensureUserCanEditActivity(Lpj $lpj, User $user): void
    {
        abort_unless($lpj->status === Lpj::STATUS_AKTIF, 403);

        $assignment = $lpj->assignedUsers()
            ->where('user_id', $user->id)
            ->first();

        abort_unless($assignment && $assignment->can_edit_activity_data, 403);
    }

    private function ensureUserCanUploadDocumentation(Lpj $lpj, User $user): void
    {
        abort_unless($lpj->status === Lpj::STATUS_AKTIF, 403);

        $assignment = $lpj->assignedUsers()
            ->where('user_id', $user->id)
            ->first();

        abort_unless($assignment && $assignment->can_upload_documentation, 403);
    }

    private function filledRows(array $rows, string $requiredKey): array
    {
        return array_values(array_filter($rows, fn (array $row): bool => filled($row[$requiredKey] ?? null)));
    }

    private function filePayload(UploadedFile $file, string $directory): array
    {
        $stored = app(AppFileStorageService::class)->store($file, $directory);

        return [
            'file_path' => $stored['path'],
            'file_disk' => $stored['disk'],
            'mime_type' => $stored['mime_type'],
            'file_size' => $stored['size'],
        ];
    }
}

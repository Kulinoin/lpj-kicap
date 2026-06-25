<?php

use App\Models\ActivityNote;
use App\Models\ActivityDocumentation;
use App\Models\Lpj;
use App\Models\LpjFinancialTransaction;
use App\Models\LpjReportSnapshot;
use App\Models\LpjType;
use App\Models\OrganizationProfile;
use App\Models\User;
use App\Services\ActivityExecutionService;
use App\Services\ActivityNoteService;
use App\Services\AppFileStorageService;
use App\Services\LpjFinanceService;
use App\Services\LpjReportService;
use App\Services\LpjReviewService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

Route::get('/', fn () => redirect('/app'));

Route::get('/login', fn () => redirect(Filament::getLoginUrl()))->name('login');

Route::get('/health', function () {
    try {
        DB::select('select 1 as ok');

        return response()->json([
            'ok' => true,
            'app' => 'Kicap Event',
            'slice' => '00',
            'database' => 'ok',
            'timezone' => config('app.timezone'),
        ]);
    } catch (Throwable $exception) {
        return response()->json([
            'ok' => false,
            'app' => 'Kicap Event',
            'slice' => '00',
            'database' => 'error',
            'message' => $exception->getMessage(),
        ], 500);
    }
});

Route::middleware('auth')->group(function (): void {
    




Route::get('/admin/activity-participants/{participant}/detail', function (\Illuminate\Http\Request $request, \App\Models\ActivityParticipant $participant) {
    $user = $request->user();

    abort_unless($user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (($user->role ?? null) === 'admin')
    ), 403);

    $participant->loadMissing(['lpj', 'creator']);

    $rawBackUrl = (string) $request->query('back_url', '');
    $backUrl = str_starts_with($rawBackUrl, '/admin/')
        ? url($rawBackUrl)
        : url('/admin/activity-participants');

    $backLabel = trim((string) $request->query('back_label', 'Daftar peserta'));
    $backLabel = $backLabel !== '' ? $backLabel : 'Daftar peserta';

    return view('admin.activity-operational-detail', [
        'kind' => 'participant',
        'record' => $participant,
        'backUrl' => $backUrl,
        'backLabel' => $backLabel,
        'title' => $participant->name ?: 'Peserta Event',
        'subtitle' => 'Peserta Event',
        'primaryLabel' => 'Nama Peserta',
        'primaryValue' => $participant->name,
        'secondaryLabel' => 'Asal',
        'secondaryValue' => $participant->origin,
        'items' => [
            ['label' => 'Nomor Peserta', 'value' => $participant->participant_number],
            ['label' => 'Kehadiran', 'value' => \App\Models\ActivityParticipant::attendanceOptions()[$participant->attendance_status] ?? $participant->attendance_status],
            ['label' => 'Hasil', 'value' => $participant->result_status],
            ['label' => 'Input Oleh', 'value' => $participant->creator?->name],
        ],
        'noteLabel' => 'Catatan',
        'noteValue' => $participant->note,
    ]);
})->middleware('auth')->name('admin.activity-participants.detail');

Route::get('/admin/activity-committees/{committee}/detail', function (\Illuminate\Http\Request $request, \App\Models\ActivityCommittee $committee) {
    $user = $request->user();

    abort_unless($user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (($user->role ?? null) === 'admin')
    ), 403);

    $committee->loadMissing(['lpj', 'creator']);

    $rawBackUrl = (string) $request->query('back_url', '');
    $backUrl = str_starts_with($rawBackUrl, '/admin/')
        ? url($rawBackUrl)
        : url('/admin/activity-committees');

    $backLabel = trim((string) $request->query('back_label', 'Daftar panitia/pendamping'));
    $backLabel = $backLabel !== '' ? $backLabel : 'Daftar panitia/pendamping';

    return view('admin.activity-operational-detail', [
        'kind' => 'committee',
        'record' => $committee,
        'backUrl' => $backUrl,
        'backLabel' => $backLabel,
        'title' => $committee->name ?: 'Panitia/Pendamping',
        'subtitle' => 'Panitia/Pendamping',
        'primaryLabel' => 'Nama',
        'primaryValue' => $committee->name,
        'secondaryLabel' => 'Peran',
        'secondaryValue' => $committee->role,
        'items' => [
            ['label' => 'Kontak', 'value' => $committee->contact],
            ['label' => 'Input Oleh', 'value' => $committee->creator?->name],
        ],
        'noteLabel' => 'Tugas',
        'noteValue' => $committee->task,
    ]);
})->middleware('auth')->name('admin.activity-committees.detail');

Route::get('/admin/activity-schedules/{schedule}/detail', function (\Illuminate\Http\Request $request, \App\Models\ActivitySchedule $schedule) {
    $user = $request->user();

    abort_unless($user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (($user->role ?? null) === 'admin')
    ), 403);

    $schedule->loadMissing(['lpj', 'creator']);

    $rawBackUrl = (string) $request->query('back_url', '');
    $backUrl = str_starts_with($rawBackUrl, '/admin/')
        ? url($rawBackUrl)
        : url('/admin/activity-schedules');

    $backLabel = trim((string) $request->query('back_label', 'Daftar rundown'));
    $backLabel = $backLabel !== '' ? $backLabel : 'Daftar rundown';

    return view('admin.activity-operational-detail', [
        'kind' => 'schedule',
        'record' => $schedule,
        'backUrl' => $backUrl,
        'backLabel' => $backLabel,
        'title' => $schedule->activity_name ?: 'Rundown Event',
        'subtitle' => 'Rundown Event',
        'primaryLabel' => 'Kegiatan',
        'primaryValue' => $schedule->activity_name,
        'secondaryLabel' => 'PIC',
        'secondaryValue' => $schedule->responsible_person,
        'items' => [
            ['label' => 'Mulai', 'value' => optional($schedule->start_time)->format('d/m/Y H:i')],
            ['label' => 'Selesai', 'value' => optional($schedule->end_time)->format('d/m/Y H:i')],
            ['label' => 'Urutan', 'value' => $schedule->sort_order],
            ['label' => 'Input Oleh', 'value' => $schedule->creator?->name],
        ],
        'noteLabel' => 'Catatan',
        'noteValue' => $schedule->note,
    ]);
})->middleware('auth')->name('admin.activity-schedules.detail');
Route::get('/admin/activity-notes/{note}/detail', function (\Illuminate\Http\Request $request, \App\Models\ActivityNote $note) {
    $user = $request->user();

    abort_unless($user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (($user->role ?? null) === 'admin')
    ), 403);

    $note->loadMissing(['lpj', 'user']);

    $rawBackUrl = (string) $request->query('back_url', '');
    $backUrl = str_starts_with($rawBackUrl, '/admin/')
        ? url($rawBackUrl)
        : url('/admin/activity-notes');

    $backLabel = trim((string) $request->query('back_label', 'Daftar catatan'));
    $backLabel = $backLabel !== '' ? $backLabel : 'Daftar catatan';

    return view('admin.activity-note-detail', [
        'record' => $note,
        'backUrl' => $backUrl,
        'backLabel' => $backLabel,
        'typeLabels' => \App\Models\ActivityNote::typeLabels(),
    ]);
})->middleware('auth')->name('admin.activity-notes.detail');
Route::get('/admin/activity-documentations/{documentation}/detail', function (\Illuminate\Http\Request $request, \App\Models\ActivityDocumentation $documentation) {
    $user = $request->user();

    abort_unless($user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (($user->role ?? null) === 'admin')
    ), 403);

    $documentation->loadMissing(['lpj', 'uploader']);

    $fileUrl = app(\App\Services\AppFileStorageService::class)->url($documentation->file_path, $documentation->file_disk);

    $rawBackUrl = (string) $request->query('back_url', '');
    $backUrl = str_starts_with($rawBackUrl, '/admin/')
        ? url($rawBackUrl)
        : url('/admin/activity-documentations');

    $backLabel = trim((string) $request->query('back_label', 'Daftar dokumentasi'));
    $backLabel = $backLabel !== '' ? $backLabel : 'Daftar dokumentasi';

    return view('admin.activity-file-detail', [
        'kind' => 'documentation',
        'record' => $documentation,
        'fileUrl' => $fileUrl,
        'backUrl' => $backUrl,
        'backLabel' => $backLabel,
        'title' => $documentation->caption ?: $documentation->original_name ?: 'Dokumentasi Event',
        'subtitle' => 'Dokumentasi Event',
        'primaryLabel' => 'Kategori',
        'primaryValue' => \App\Models\ActivityDocumentation::categoryOptions()[$documentation->category] ?? $documentation->category,
        'descriptionLabel' => 'Caption',
        'descriptionValue' => $documentation->caption,
    ]);
})->middleware('auth')->name('admin.activity-documentations.detail');

Route::get('/admin/activity-attachments/{attachment}/detail', function (\Illuminate\Http\Request $request, \App\Models\ActivityAttachment $attachment) {
    $user = $request->user();

    abort_unless($user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (($user->role ?? null) === 'admin')
    ), 403);

    $attachment->loadMissing(['lpj', 'uploader']);

    $fileUrl = app(\App\Services\AppFileStorageService::class)->url($attachment->file_path, $attachment->file_disk);

    $rawBackUrl = (string) $request->query('back_url', '');
    $backUrl = str_starts_with($rawBackUrl, '/admin/')
        ? url($rawBackUrl)
        : url('/admin/activity-attachments');

    $backLabel = trim((string) $request->query('back_label', 'Daftar lampiran'));
    $backLabel = $backLabel !== '' ? $backLabel : 'Daftar lampiran';

    return view('admin.activity-file-detail', [
        'kind' => 'attachment',
        'record' => $attachment,
        'fileUrl' => $fileUrl,
        'backUrl' => $backUrl,
        'backLabel' => $backLabel,
        'title' => $attachment->title ?: $attachment->original_name ?: 'Lampiran Event',
        'subtitle' => 'Lampiran Event',
        'primaryLabel' => 'Judul',
        'primaryValue' => $attachment->title,
        'descriptionLabel' => 'Keterangan',
        'descriptionValue' => $attachment->description,
    ]);
})->middleware('auth')->name('admin.activity-attachments.detail');
Route::get('/admin/lpj-financial-transactions/{transaction}/detail', function (Request $request, LpjFinancialTransaction $transaction) {
    $user = $request->user();

    abort_unless($user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (($user->role ?? null) === 'admin')
    ), 403);

    $transaction->loadMissing(['lpj', 'user', 'reviewer', 'advanceClaim']);

    $proofUrl = null;

    if (filled($transaction->proof_path)) {
        $proofUrl = app(\App\Services\AppFileStorageService::class)->url($transaction->proof_path, $transaction->proof_disk);
    }

    // KICAP_TRANSACTION_DETAIL_CONTEXT_BACK
    $rawBackUrl = (string) $request->query('back_url', '');
    $backUrl = str_starts_with($rawBackUrl, '/admin/')
        ? url($rawBackUrl)
        : url('/admin/lpj-financial-transactions');

    $backLabel = trim((string) $request->query('back_label', 'Daftar transaksi'));
    $backLabel = $backLabel !== '' ? $backLabel : 'Daftar transaksi';

    return view('admin.lpj-financial-transaction-detail', [
        'record' => $transaction,
        'proofUrl' => $proofUrl,
        'backUrl' => $backUrl,
        'backLabel' => $backLabel,
        'statusLabels' => LpjFinancialTransaction::statusLabels(),
        'sourceLabels' => LpjFinancialTransaction::sourceLabels(),
        'claimStatusLabels' => \App\Models\LpjAdvanceClaim::statusLabels(),
    ]);
})->middleware('auth')->name('admin.lpj-financial-transactions.detail');
Route::get('/admin/reports/lpjs/{lpj}/print', function (Request $request, Lpj $lpj, LpjReportService $reportService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isAdmin(), 403);

        try {
            $report = $reportService->reportData($lpj);
        } catch (ValidationException $exception) {
            abort(403, $exception->validator->errors()->first());
        }

        $html = view('reports.lpj-final', [
            'report' => $report,
            'forPdf' => false,
        ])->render();

        $reportService->createSnapshot($lpj, $user, LpjReportSnapshot::SOURCE_ADMIN_PRINT, $html);

        return response($html);
    })->name('admin.lpjs.report.print');

    Route::get('/admin/report-snapshots/{snapshot}/print', function (Request $request, LpjReportSnapshot $snapshot) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isAdmin(), 403);

        return response($snapshot->snapshot_html);
    })->name('admin.lpj-report-snapshots.print');

    Route::get('/admin/reports/lpjs/{lpj}/pdf', function (Request $request, Lpj $lpj, LpjReportService $reportService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isAdmin(), 403);

        try {
            $report = $reportService->reportData($lpj);
        } catch (ValidationException $exception) {
            abort(403, $exception->validator->errors()->first());
        }

        $html = view('reports.lpj-final', [
            'report' => $report,
            'forPdf' => true,
        ])->render();

        $snapshotHtml = view('reports.lpj-final', [
            'report' => $report,
            'forPdf' => false,
        ])->render();

        $reportService->createSnapshot($lpj, $user, LpjReportSnapshot::SOURCE_ADMIN_PDF, $snapshotHtml);

        $options = new Options();
        $options->set('chroot', base_path());
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $filename = 'LPJ-'.$lpj->code.'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    })->name('admin.lpjs.report.pdf');

    Route::get('/app/lpjs/{lpj}/print', function (Request $request, Lpj $lpj, LpjReportService $reportService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        try {
            $report = $reportService->reportData($lpj);
        } catch (ValidationException $exception) {
            abort(403, $exception->validator->errors()->first());
        }

        $html = view('reports.lpj-final', [
            'report' => $report,
            'forPdf' => false,
        ])->render();

        $reportService->createSnapshot($lpj, $user, LpjReportSnapshot::SOURCE_USER_PRINT, $html);

        return response($html);
    })->name('app.lpjs.report.print');

    Route::get('/app/{any?}', function () {
        $user = auth()->user();

        if ($user instanceof User && $user->isAdmin()) {
            return redirect('/admin');
        }

        return view('app');
    })->where('any', '.*');

    Route::post('/app/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    })->name('app.logout');

    Route::get('/api/app/lpjs', function (Request $request) {
        /** @var User $user */
        $user = $request->user();

        if (! $user->isUser()) {
            return response()->json(['data' => []]);
        }

        $lpjs = Lpj::query()
            ->visibleToAssignedUser($user)
            ->with([
                'assignedUsers' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->select('id', 'lpj_id', 'user_id', 'role_label'),
                'financialTransactions' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(1),
                'type:id,name',
                'personInCharge:id,name',
            ])
            ->withCount(['participants', 'documentations', 'attachments'])
            ->latest()
            ->get()
            ->map(function (Lpj $lpj): array {
                $latestTransaction = $lpj->financialTransactions->first();
                $progress = $lpj->status === Lpj::STATUS_FINISH
                    ? 100
                    : min(95, 30
                        + ($lpj->participants_count > 0 ? 20 : 0)
                        + ($lpj->documentations_count > 0 ? 20 : 0)
                        + ($lpj->attachments_count > 0 ? 10 : 0)
                        + ($latestTransaction ? 15 : 0));

                return [
                    'id' => $lpj->id,
                    'code' => $lpj->code,
                    'title' => $lpj->title,
                    'type' => $lpj->type?->name,
                    'status' => $lpj->status,
                    'status_label' => Lpj::statusLabels()[$lpj->status] ?? $lpj->status,
                    'start_date' => $lpj->start_date?->toDateString(),
                    'end_date' => $lpj->end_date?->toDateString(),
                    'location' => $lpj->location,
                    'person_in_charge' => $lpj->personInCharge?->name,
                    'assignment_role' => $lpj->assignedUsers->first()?->role_label ?? 'Petugas Lapangan',
                    'participant_count' => $lpj->participants_count,
                    'progress_label' => $lpj->status === Lpj::STATUS_FINISH ? 'LPJ Final' : 'Kelengkapan Lapangan',
                    'progress_percentage' => $progress,
                    'latest_transaction' => $latestTransaction ? [
                        'category' => $latestTransaction->category,
                        'description' => $latestTransaction->description,
                        'amount' => (float) $latestTransaction->amount,
                        'status' => $latestTransaction->status,
                        'status_label' => LpjFinancialTransaction::statusLabels()[$latestTransaction->status] ?? $latestTransaction->status,
                    ] : null,
                ];
            });

        return response()->json(['data' => $lpjs]);
    });

    Route::get('/api/app/lpjs/{lpj}', function (Request $request, Lpj $lpj, ActivityNoteService $activityNoteService, ActivityExecutionService $executionService, LpjFinanceService $financeService, LpjReviewService $reviewService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->with(['type:id,name,slug', 'personInCharge:id,name'])
            ->findOrFail($lpj->id);

        $assignment = $lpj->assignedUsers()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $activityNotes = $activityNoteService->ensureForUser($lpj, $user);

        return response()->json([
            'data' => [
                'id' => $lpj->id,
                'code' => $lpj->code,
                'title' => $lpj->title,
                'type' => $lpj->type?->name,
                'type_slug' => $lpj->type?->slug,
                'status' => $lpj->status,
                'status_label' => Lpj::statusLabels()[$lpj->status] ?? $lpj->status,
                'completeness_status' => $lpj->completeness_status,
                'completeness_label' => Lpj::completenessLabels()[$lpj->completeness_status] ?? $lpj->completeness_status,
                'submitted_at' => $lpj->submitted_at?->toDateTimeString(),
                'start_date' => $lpj->start_date?->toDateString(),
                'end_date' => $lpj->end_date?->toDateString(),
                'location' => $lpj->location,
                'funding_source' => $lpj->funding_source,
                'assignment_letter_number' => $lpj->assignment_letter_number,
                'period_label' => $lpj->period_label,
                'external_organizer' => $lpj->external_organizer,
                'organization_role' => $lpj->organization_role,
                'person_in_charge' => $lpj->personInCharge?->name,
                'can_input_operational_data' => $lpj->status === Lpj::STATUS_AKTIF && $assignment->can_edit_activity_data,
                'can_submit_review' => $lpj->status === Lpj::STATUS_AKTIF,
                'can_print_report' => $lpj->status === Lpj::STATUS_FINISH,
                'report_print_url' => $lpj->status === Lpj::STATUS_FINISH ? route('app.lpjs.report.print', $lpj) : null,
                'review' => $reviewService->reviewPayload($lpj),
                'activity_notes' => $activityNoteService->payload($activityNotes),
                'execution' => $executionService->payload($lpj, $user),
                'finance_category_options' => array_values(LpjFinancialTransaction::categoryOptions()),
                'finance' => $financeService->financePayload($lpj, $user),
            ],
        ]);
    });

    Route::post('/api/app/lpjs/{lpj}/submit-review', function (Request $request, Lpj $lpj, LpjReviewService $reviewService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $lpj = $reviewService->submitForReview($lpj, $user);

        return response()->json([
            'data' => [
                'completeness_status' => $lpj->completeness_status,
                'completeness_label' => Lpj::completenessLabels()[$lpj->completeness_status] ?? $lpj->completeness_status,
                'submitted_at' => $lpj->submitted_at?->toDateTimeString(),
                'review' => $reviewService->reviewPayload($lpj),
            ],
        ]);
    });

    Route::post('/api/app/lpjs/{lpj}/activity-notes', function (Request $request, Lpj $lpj, ActivityNoteService $activityNoteService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $assignment = $lpj->assignedUsers()
            ->where('user_id', $user->id)
            ->firstOrFail();

        abort_unless($lpj->status === Lpj::STATUS_AKTIF && $assignment->can_edit_activity_data, 403);

        $validated = $request->validate([
            'notes' => ['required', 'array', 'min:1'],
            'notes.*.type' => ['required', 'string', Rule::in(ActivityNote::types())],
            'notes.*.content' => ['nullable', 'string', 'max:5000'],
            'notes.*.include_in_report' => ['sometimes', 'boolean'],
        ]);

        $activityNoteService->ensureForUser($lpj, $user);

        foreach ($validated['notes'] as $item) {
            $lpj->activityNotes()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $item['type'],
                ],
                [
                    'content' => $item['content'] ?? '',
                    'include_in_report' => (bool) ($item['include_in_report'] ?? false),
                ]
            );
        }

        return response()->json([
            'data' => [
                'activity_notes' => $activityNoteService->payload(
                    $lpj->activityNotes()->where('user_id', $user->id)->get()
                ),
            ],
        ]);
    });

    Route::post('/api/app/lpjs/{lpj}/execution-data', function (Request $request, Lpj $lpj, ActivityExecutionService $executionService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $validated = $request->validate([
            'participants' => ['sometimes', 'array'],
            'participants.*.name' => ['nullable', 'string', 'max:255'],
            'participants.*.origin' => ['nullable', 'string', 'max:255'],
            'participants.*.participant_number' => ['nullable', 'string', 'max:100'],
            'participants.*.attendance_status' => ['nullable', 'string', Rule::in(array_keys(\App\Models\ActivityParticipant::attendanceOptions()))],
            'participants.*.result_status' => ['nullable', 'string', 'max:255'],
            'participants.*.note' => ['nullable', 'string', 'max:1000'],
            'committees' => ['sometimes', 'array'],
            'committees.*.name' => ['nullable', 'string', 'max:255'],
            'committees.*.role' => ['nullable', 'string', 'max:255'],
            'committees.*.task' => ['nullable', 'string', 'max:1000'],
            'committees.*.contact' => ['nullable', 'string', 'max:100'],
            'schedules' => ['sometimes', 'array'],
            'schedules.*.start_time' => ['nullable', 'date'],
            'schedules.*.end_time' => ['nullable', 'date'],
            'schedules.*.activity_name' => ['nullable', 'string', 'max:255'],
            'schedules.*.responsible_person' => ['nullable', 'string', 'max:255'],
            'schedules.*.note' => ['nullable', 'string', 'max:1000'],
        ]);

        $executionService->replaceExecutionData($lpj, $user, $validated);

        return response()->json([
            'data' => [
                'execution' => $executionService->payload($lpj->fresh(), $user),
            ],
        ]);
    });

    Route::post('/api/app/lpjs/{lpj}/documentations', function (Request $request, Lpj $lpj, ActivityExecutionService $executionService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(ActivityDocumentation::categoryOptions()))],
            'caption' => ['nullable', 'string', 'max:1000'],
            'include_in_report' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $executionService->storeDocumentation($lpj, $user, $validated, $request->file('file'));

        return response()->json([
            'data' => [
                'execution' => $executionService->payload($lpj->fresh(), $user),
            ],
        ], 201);
    });

    Route::post('/api/app/lpjs/{lpj}/attachments', function (Request $request, Lpj $lpj, ActivityExecutionService $executionService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'include_in_report' => ['sometimes', 'boolean'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        $executionService->storeAttachment($lpj, $user, $validated, $request->file('file'));

        return response()->json([
            'data' => [
                'execution' => $executionService->payload($lpj->fresh(), $user),
            ],
        ], 201);
    });

    Route::post('/api/app/lpjs/{lpj}/expenses', function (Request $request, Lpj $lpj, LpjFinanceService $financeService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(LpjFinancialTransaction::categoryOptions()))],
            'description' => ['required', 'string', 'max:5000'],
            'amount' => ['required', 'numeric', 'min:1'],
            'spent_at' => ['required', 'date'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'no_proof_reason' => ['required_without:proof', 'nullable', 'string', 'max:1000'],
        ]);

        $transaction = $financeService->createExpense($lpj, $user, $validated, $request->file('proof'));

        return response()->json([
            'data' => [
                'transaction_id' => $transaction->id,
                'finance' => $financeService->financePayload($lpj->fresh(), $user),
            ],
        ], 201);
    });

    Route::post('/api/app/lpjs/{lpj}/advance-expenses', function (Request $request, Lpj $lpj, LpjFinanceService $financeService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(LpjFinancialTransaction::categoryOptions()))],
            'description' => ['required', 'string', 'max:5000'],
            'amount' => ['required', 'numeric', 'min:1'],
            'spent_at' => ['required', 'date'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'no_proof_reason' => ['required_without:proof', 'nullable', 'string', 'max:1000'],
        ]);

        $transaction = $financeService->createAdvanceExpense($lpj, $user, $validated, $request->file('proof'));

        return response()->json([
            'data' => [
                'transaction_id' => $transaction->id,
                'finance' => $financeService->financePayload($lpj->fresh(), $user),
            ],
        ], 201);
    });

    Route::post('/api/app/lpjs/{lpj}/balance-transfers', function (Request $request, Lpj $lpj, LpjFinanceService $financeService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $validated = $request->validate([
            'recipient_user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $recipient = User::query()->findOrFail($validated['recipient_user_id']);

        $financeService->transferBalance($lpj, $user, $recipient, $validated);

        return response()->json([
            'data' => [
                'finance' => $financeService->financePayload($lpj->fresh(), $user),
            ],
        ], 201);
    });



    Route::get('/api/app/lpjs/{lpj}/financial-transactions/{transaction}/proof', function (Request $request, Lpj $lpj, LpjFinancialTransaction $transaction) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $transaction = LpjFinancialTransaction::query()
            ->where('lpj_id', $lpj->id)
            ->whereKey($transaction->id)
            ->firstOrFail();

        abort_unless((bool) $transaction->proof_path, 404);

        $url = app(\App\Services\AppFileStorageService::class)->url($transaction->proof_path, $transaction->proof_disk);

        abort_unless(filled($url), 404);

        return redirect()->away($url);
    });
    Route::get('/api/app/lpjs/{lpj}/financial-transactions/{transaction}', function (Request $request, Lpj $lpj, LpjFinancialTransaction $transaction) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $transaction = LpjFinancialTransaction::query()
            ->with(['user', 'reviewer', 'advanceClaim'])
            ->where('lpj_id', $lpj->id)
            ->whereKey($transaction->id)
            ->firstOrFail();

        $statusLabels = LpjFinancialTransaction::statusLabels();
        $sourceLabels = LpjFinancialTransaction::sourceLabels();
        $claimStatusLabels = \App\Models\LpjAdvanceClaim::statusLabels();

        $proof = null;

        if ($transaction->proof_path) {
            $proofDisk = $transaction->proof_disk ?: config('filesystems.default', 'public');
            $proofUrl = app(\App\Services\AppFileStorageService::class)->url($transaction->proof_path, $proofDisk);
            $proofName = \Illuminate\Support\Str::afterLast($transaction->proof_path, '/');
            $proofExt = strtolower(pathinfo($transaction->proof_path, PATHINFO_EXTENSION));

            $proof = [
                'url' => $proofUrl,
                'name' => $proofName ?: 'Bukti transaksi',
                'type' => $proofExt === 'pdf' ? 'pdf' : 'image',
                'extension' => $proofExt,
            ];
        }

        return response()->json([
            'data' => [
                'transaction' => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'type_label' => 'Pengeluaran',
                    'source_type' => $transaction->source_type,
                    'source_label' => $sourceLabels[$transaction->source_type] ?? $transaction->source_type,
                    'status' => $transaction->status,
                    'status_label' => $statusLabels[$transaction->status] ?? $transaction->status,
                    'category' => $transaction->category,
                    'description' => $transaction->description,
                    'amount' => (float) $transaction->amount,
                    'spent_at' => optional($transaction->spent_at)->toDateString(),
                    'no_proof_reason' => $transaction->no_proof_reason,
                    'admin_note' => $transaction->admin_note,
                    'reviewed_at' => optional($transaction->reviewed_at)->toDateTimeString(),
                    'proof' => $proof,
                    'user' => $transaction->user ? [
                        'id' => $transaction->user->id,
                        'name' => $transaction->user->name,
                    ] : null,
                    'reviewer' => $transaction->reviewer ? [
                        'id' => $transaction->reviewer->id,
                        'name' => $transaction->reviewer->name,
                    ] : null,
                    'advance_claim' => $transaction->advanceClaim ? [
                        'id' => $transaction->advanceClaim->id,
                        'amount' => (float) $transaction->advanceClaim->amount,
                        'status' => $transaction->advanceClaim->status,
                        'status_label' => $claimStatusLabels[$transaction->advanceClaim->status] ?? $transaction->advanceClaim->status,
                        'admin_note' => $transaction->advanceClaim->admin_note,
                        'verified_at' => optional($transaction->advanceClaim->verified_at)->toDateTimeString(),
                        'paid_at' => optional($transaction->advanceClaim->paid_at)->toDateTimeString(),
                    ] : null,
                ],
            ],
        ]);
    });
    Route::post('/api/app/lpjs/{lpj}/financial-transactions/{transaction}/revision', function (Request $request, Lpj $lpj, LpjFinancialTransaction $transaction, LpjReviewService $reviewService, LpjFinanceService $financeService) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $lpj = Lpj::query()
            ->visibleToAssignedUser($user)
            ->findOrFail($lpj->id);

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(LpjFinancialTransaction::categoryOptions()))],
            'description' => ['required', 'string', 'max:5000'],
            'spent_at' => ['required', 'date'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'no_proof_reason' => ['required_without:proof', 'nullable', 'string', 'max:1000'],
        ]);

        $reviewService->submitTransactionRevision($lpj, $transaction, $user, $validated, $request->file('proof'));

        return response()->json([
            'data' => [
                'finance' => $financeService->financePayload($lpj->fresh(), $user),
            ],
        ]);
    });

    Route::get('/api/app/profile', function (Request $request) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        return response()->json([
            'data' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'role_label' => 'Petugas Lapangan',
                'organization_name' => OrganizationProfile::query()->first()?->institution_name ?? 'PT. Kazoku Indonesia Center',
                'member_since' => $user->created_at?->format('Y'),
                'avatar_url' => app(AppFileStorageService::class)->url($user->profile_photo_path, $user->profile_photo_disk),
            ],
        ]);
    });

    Route::post('/api/app/profile', function (Request $request) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isUser(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'whatsapp' => $validated['whatsapp'] ?? null,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                app(AppFileStorageService::class)->delete($user->profile_photo_path, $user->profile_photo_disk);
            }

            $stored = app(AppFileStorageService::class)->store($request->file('profile_photo'), 'profile-photos');

            $payload['profile_photo_path'] = $stored['path'];
            $payload['profile_photo_disk'] = $stored['disk'];
        }

        if (filled($validated['password'] ?? null)) {
            $payload['password'] = $validated['password'];
        }

        $user->forceFill($payload)->save();

        return response()->json([
            'data' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'role_label' => 'Petugas Lapangan',
                'organization_name' => OrganizationProfile::query()->first()?->institution_name ?? 'PT. Kazoku Indonesia Center',
                'member_since' => $user->created_at?->format('Y'),
                'avatar_url' => app(AppFileStorageService::class)->url($user->profile_photo_path, $user->profile_photo_disk),
            ],
        ]);
    });
});

// slice-01-master-lpj-types
Route::get('/api/master/lpj-types', function () {
    return LpjType::query()
        ->active()
        ->get(['id', 'name', 'slug', 'description', 'is_external_event', 'sort_order']);
});


Route::get('/admin/selection/participants/{participant}/detail', function (\Illuminate\Http\Request $request, \App\Models\ActivityParticipant $participant) {
    $user = $request->user();

    abort_unless($user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (($user->role ?? null) === 'admin')
    ), 403);

    $participant->loadMissing([
        'lpj',
        'registeredBy',
        'currentSelectionStage',
        'eliminatedStage',
        'eliminatedTest',
        'testResults.stage',
        'testResults.test',
        'testResults.updater',
    ]);

    $rawBackUrl = (string) $request->query('back_url', '');
    $backUrl = str_starts_with($rawBackUrl, '/admin/')
        ? url($rawBackUrl)
        : url('/admin/activity-participants');

    $backLabel = trim((string) $request->query('back_label', 'Daftar peserta seleksi'));
    $backLabel = $backLabel !== '' ? $backLabel : 'Daftar peserta seleksi';

    $photoUrl = null;

    if (filled($participant->photo_path)) {
        try {
            $storage = app(\App\Services\AppFileStorageService::class);
            $method = new \ReflectionMethod($storage, 'url');

            $photoUrl = $method->getNumberOfParameters() >= 2
                ? $storage->url($participant->photo_path, $participant->photo_disk)
                : $storage->url($participant->photo_path);
        } catch (\Throwable $e) {
            try {
                $photoUrl = \Illuminate\Support\Facades\Storage::disk($participant->photo_disk ?: config('filesystems.default'))
                    ->url($participant->photo_path);
            } catch (\Throwable $ignored) {
                $photoUrl = null;
            }
        }
    }

    $stages = \App\Models\ActivitySelectionStage::query()
        ->where('lpj_id', $participant->lpj_id)
        ->with(['tests' => fn ($query) => $query->orderBy('sort_order')])
        ->orderBy('sort_order')
        ->get();

    return view('admin.selection-participant-detail', [
        'participant' => $participant,
        'stages' => $stages,
        'resultsByTestId' => $participant->testResults->keyBy('activity_selection_test_id'),
        'photoUrl' => $photoUrl,
        'backUrl' => $backUrl,
        'backLabel' => $backLabel,
    ]);
})->middleware('auth')->name('admin.selection.participants.detail');


Route::get('/api/app/lpjs/{lpj}/selection', function (\Illuminate\Http\Request $request, \App\Models\Lpj $lpj, \App\Services\ActivitySelectionPayloadService $selectionService) {
    /** @var \App\Models\User $user */
    $user = $request->user();

    abort_unless($user && $user->isUser(), 403);

    $lpj = \App\Models\Lpj::query()
        ->visibleToAssignedUser($user)
        ->findOrFail($lpj->id);

    return response()->json([
        'data' => [
            'selection' => $selectionService->payload($lpj, $user),
        ],
    ]);
})->middleware('auth');

Route::post('/api/app/lpjs/{lpj}/selection/participants', function (\Illuminate\Http\Request $request, \App\Models\Lpj $lpj, \App\Services\ActivitySelectionPayloadService $selectionService) {
    /** @var \App\Models\User $user */
    $user = $request->user();

    abort_unless($user && $user->isUser(), 403);

    $lpj = \App\Models\Lpj::query()
        ->visibleToAssignedUser($user)
        ->findOrFail($lpj->id);

    abort_unless($lpj->status === \App\Models\Lpj::STATUS_AKTIF, 403);

    $assignment = $lpj->assignedUsers()->where('user_id', $user->id)->first();
    abort_unless($assignment, 403);

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'origin' => ['nullable', 'string', 'max:255'],
        'participant_number' => [
            'nullable',
            'string',
            'max:100',
            \Illuminate\Validation\Rule::unique('activity_participants', 'participant_number')
                ->where(fn ($query) => $query->where('lpj_id', $lpj->id)),
        ],
        'whatsapp' => ['nullable', 'string', 'max:50'],
        'note' => ['nullable', 'string', 'max:5000'],
    ]);

    $selectionService->createParticipant($lpj, $user, $validated);

    return response()->json([
        'data' => [
            'selection' => $selectionService->payload($lpj->fresh(), $user),
        ],
    ], 201);
})->middleware('auth');

Route::post('/api/app/lpjs/{lpj}/selection/participants/{participant}/registration', function (\Illuminate\Http\Request $request, \App\Models\Lpj $lpj, \App\Models\ActivityParticipant $participant, \App\Services\ActivitySelectionPayloadService $selectionService) {
    /** @var \App\Models\User $user */
    $user = $request->user();

    abort_unless($user && $user->isUser(), 403);

    $lpj = \App\Models\Lpj::query()
        ->visibleToAssignedUser($user)
        ->findOrFail($lpj->id);

    abort_unless($lpj->status === \App\Models\Lpj::STATUS_AKTIF, 403);

    $assignment = $lpj->assignedUsers()->where('user_id', $user->id)->first();
    abort_unless($assignment, 403);
    abort_unless($participant->lpj_id === $lpj->id, 404);

    $validated = $request->validate([
        'participant_number' => [
            'nullable',
            'string',
            'max:100',
            \Illuminate\Validation\Rule::unique('activity_participants', 'participant_number')
                ->where(fn ($query) => $query->where('lpj_id', $lpj->id))
                ->ignore($participant->id),
        ],
        'whatsapp' => ['nullable', 'string', 'max:50'],
        'note' => ['nullable', 'string', 'max:5000'],
        'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
    ]);

    $selectionService->updateRegistration($lpj, $participant, $user, $validated, $request->file('photo'));

    return response()->json([
        'data' => [
            'selection' => $selectionService->payload($lpj->fresh(), $user),
        ],
    ]);
})->middleware('auth');


Route::get('/api/app/lpjs/{lpj}/operational-schedules', function (\Illuminate\Http\Request $request, \App\Models\Lpj $lpj) {
    /** @var \App\Models\User $user */
    $user = $request->user();

    abort_unless($user && $user->isUser(), 403);

    $lpj = \App\Models\Lpj::query()
        ->visibleToAssignedUser($user)
        ->findOrFail($lpj->id);

    $assignment = $lpj->assignedUsers()->where('user_id', $user->id)->first();

    $schedules = \App\Models\ActivitySchedule::query()
        ->where('lpj_id', $lpj->id)
        ->with(['creator:id,name', 'statusUpdater:id,name'])
        ->orderBy('sort_order')
        ->orderBy('start_time')
        ->orderBy('id')
        ->get()
        ->map(fn (\App\Models\ActivitySchedule $schedule): array => [
            'id' => $schedule->id,
            'activity_name' => $schedule->activity_name,
            'start_time' => $schedule->start_time ? (string) $schedule->start_time : null,
            'end_time' => $schedule->end_time ? (string) $schedule->end_time : null,
            'responsible_person' => $schedule->responsible_person,
            'note' => $schedule->note,
            'sort_order' => $schedule->sort_order,
            'status' => $schedule->status ?? \App\Models\ActivitySchedule::STATUS_NOT_STARTED,
            'status_label' => \App\Models\ActivitySchedule::statusLabels()[$schedule->status ?? \App\Models\ActivitySchedule::STATUS_NOT_STARTED] ?? ($schedule->status ?? '-'),
            'status_note' => $schedule->status_note,
            'status_updated_by' => $schedule->statusUpdater?->name,
            'status_updated_at' => optional($schedule->status_updated_at)->toDateTimeString(),
            'created_by' => $schedule->creator?->name,
        ])
        ->values();

    return response()->json([
        'data' => [
            'can_manage_rundown' => (bool) $assignment,
            'schedules' => $schedules,
        ],
    ]);
})->middleware('auth');

Route::post('/api/app/lpjs/{lpj}/operational-schedules', function (\Illuminate\Http\Request $request, \App\Models\Lpj $lpj) {
    /** @var \App\Models\User $user */
    $user = $request->user();

    abort_unless($user && $user->isUser(), 403);

    $lpj = \App\Models\Lpj::query()
        ->visibleToAssignedUser($user)
        ->findOrFail($lpj->id);

    abort_unless($lpj->status === \App\Models\Lpj::STATUS_AKTIF, 403);

    $assignment = $lpj->assignedUsers()->where('user_id', $user->id)->first();
    abort_unless($assignment, 403);

    $validated = $request->validate([
        'activity_name' => ['required', 'string', 'max:255'],
        'start_time' => ['nullable', 'date'],
        'end_time' => ['nullable', 'date'],
        'responsible_person' => ['nullable', 'string', 'max:255'],
        'note' => ['nullable', 'string', 'max:5000'],
        'sort_order' => ['nullable', 'integer', 'min:0'],
    ]);

    $sortOrder = $validated['sort_order'] ?? (
        ((int) \App\Models\ActivitySchedule::query()
            ->where('lpj_id', $lpj->id)
            ->max('sort_order')) + 1
    );

    \App\Models\ActivitySchedule::query()->create([
        'lpj_id' => $lpj->id,
        'created_by' => $user->id,
        'activity_name' => $validated['activity_name'],
        'start_time' => $validated['start_time'] ?? null,
        'end_time' => $validated['end_time'] ?? null,
        'responsible_person' => $validated['responsible_person'] ?? null,
        'note' => $validated['note'] ?? null,
        'sort_order' => $sortOrder,
    ]);

    $schedules = \App\Models\ActivitySchedule::query()
        ->where('lpj_id', $lpj->id)
        ->with(['creator:id,name', 'statusUpdater:id,name'])
        ->orderBy('sort_order')
        ->orderBy('start_time')
        ->orderBy('id')
        ->get()
        ->map(fn (\App\Models\ActivitySchedule $schedule): array => [
            'id' => $schedule->id,
            'activity_name' => $schedule->activity_name,
            'start_time' => $schedule->start_time ? (string) $schedule->start_time : null,
            'end_time' => $schedule->end_time ? (string) $schedule->end_time : null,
            'responsible_person' => $schedule->responsible_person,
            'note' => $schedule->note,
            'sort_order' => $schedule->sort_order,
            'status' => $schedule->status ?? \App\Models\ActivitySchedule::STATUS_NOT_STARTED,
            'status_label' => \App\Models\ActivitySchedule::statusLabels()[$schedule->status ?? \App\Models\ActivitySchedule::STATUS_NOT_STARTED] ?? ($schedule->status ?? '-'),
            'status_note' => $schedule->status_note,
            'status_updated_by' => $schedule->statusUpdater?->name,
            'status_updated_at' => optional($schedule->status_updated_at)->toDateTimeString(),
            'created_by' => $schedule->creator?->name,
        ])
        ->values();

    return response()->json([
        'data' => [
            'can_manage_rundown' => true,
            'schedules' => $schedules,
        ],
    ], 201);
})->middleware('auth');

Route::post('/api/app/lpjs/{lpj}/operational-schedules/{schedule}/status', function (\Illuminate\Http\Request $request, \App\Models\Lpj $lpj, \App\Models\ActivitySchedule $schedule) {
    /** @var \App\Models\User $user */
    $user = $request->user();

    abort_unless($user && $user->isUser(), 403);

    $lpj = \App\Models\Lpj::query()
        ->visibleToAssignedUser($user)
        ->findOrFail($lpj->id);

    abort_unless($lpj->status === \App\Models\Lpj::STATUS_AKTIF, 403);

    $assignment = $lpj->assignedUsers()->where('user_id', $user->id)->first();
    abort_unless($assignment, 403);
    abort_unless($schedule->lpj_id === $lpj->id, 404);

    $validated = $request->validate([
        'status' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(\App\Models\ActivitySchedule::statusLabels()))],
        'status_note' => ['nullable', 'string', 'max:5000'],
    ]);

    $schedule->forceFill([
        'status' => $validated['status'],
        'status_note' => $validated['status_note'] ?? null,
        'status_updated_by' => $user->id,
        'status_updated_at' => now(),
    ])->save();

    $schedules = \App\Models\ActivitySchedule::query()
        ->where('lpj_id', $lpj->id)
        ->with(['creator:id,name', 'statusUpdater:id,name'])
        ->orderBy('sort_order')
        ->orderBy('start_time')
        ->orderBy('id')
        ->get()
        ->map(fn (\App\Models\ActivitySchedule $schedule): array => [
            'id' => $schedule->id,
            'activity_name' => $schedule->activity_name,
            'start_time' => $schedule->start_time ? (string) $schedule->start_time : null,
            'end_time' => $schedule->end_time ? (string) $schedule->end_time : null,
            'responsible_person' => $schedule->responsible_person,
            'note' => $schedule->note,
            'sort_order' => $schedule->sort_order,
            'status' => $schedule->status ?? \App\Models\ActivitySchedule::STATUS_NOT_STARTED,
            'status_label' => \App\Models\ActivitySchedule::statusLabels()[$schedule->status ?? \App\Models\ActivitySchedule::STATUS_NOT_STARTED] ?? ($schedule->status ?? '-'),
            'status_note' => $schedule->status_note,
            'status_updated_by' => $schedule->statusUpdater?->name,
            'status_updated_at' => optional($schedule->status_updated_at)->toDateTimeString(),
            'created_by' => $schedule->creator?->name,
        ])
        ->values();

    return response()->json([
        'data' => [
            'can_manage_rundown' => true,
            'schedules' => $schedules,
        ],
    ]);
})->middleware('auth');



Route::post('/api/app/lpjs/{lpj}/documentations/{documentation}/update', function (\Illuminate\Http\Request $request, \App\Models\Lpj $lpj, \App\Models\ActivityDocumentation $documentation) {
    /** @var \App\Models\User $user */
    $user = $request->user();

    abort_unless($user && $user->isUser(), 403);

    $lpj = \App\Models\Lpj::query()
        ->visibleToAssignedUser($user)
        ->findOrFail($lpj->id);

    abort_unless($lpj->status === \App\Models\Lpj::STATUS_AKTIF, 403);

    $assignment = $lpj->assignedUsers()->where('user_id', $user->id)->first();
    abort_unless($assignment, 403);
    abort_unless($documentation->lpj_id === $lpj->id, 404);

    $validated = $request->validate([
        'category' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(\App\Models\ActivityDocumentation::categoryOptions()))],
        'caption' => ['nullable', 'string', 'max:5000'],
        'include_in_report' => ['nullable', 'boolean'],
        'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
    ]);

    $payload = [
        'category' => $validated['category'],
        'caption' => $validated['caption'] ?? null,
        'include_in_report' => $request->boolean('include_in_report', true),
    ];

    if ($request->hasFile('file')) {
        if (filled($documentation->file_path)) {
            app(\App\Services\AppFileStorageService::class)->delete($documentation->file_path, $documentation->file_disk);
        }

        $stored = app(\App\Services\AppFileStorageService::class)->store($request->file('file'), 'activity-documentations');

        $payload = array_merge($payload, [
            'file_path' => $stored['path'],
            'file_disk' => $stored['disk'],
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime_type' => $stored['mime_type'] ?? $request->file('file')->getMimeType(),
            'file_size' => $stored['size'] ?? $request->file('file')->getSize(),
        ]);
    }

    $documentation->forceFill($payload)->save();

    $detail = app(\App\Services\ActivityExecutionService::class)->payload($lpj->fresh(), $user);

    return response()->json([
        'data' => [
            'execution' => $detail,
        ],
    ]);
})->middleware('auth');

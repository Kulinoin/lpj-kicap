<?php

// KICAP_PARTICIPANT_PHOTO_FAST_REDIRECT_START
\Illuminate\Support\Facades\Route::middleware(['auth'])->get('/participant-photo-fast/{participant}/photo', function (
    \Illuminate\Http\Request $request,
    \App\Models\ActivityParticipant $participant
) {
    $user = $request->user();

    if (! $user) {
        abort(403);
    }

    $participant->loadMissing(['lpj.assignedUsers']);

    $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
    $isDirektur = method_exists($user, 'isDirektur') && $user->isDirektur();

    $isAssignedUser = $participant->lpj
        && $participant->lpj->assignedUsers
            ->contains(fn ($assignment): bool => (int) $assignment->user_id === (int) $user->id);

    $isPersonInCharge = $participant->lpj
        && (int) $participant->lpj->person_in_charge_id === (int) $user->id;

    if (! $isAdmin && ! $isDirektur && ! $isAssignedUser && ! $isPersonInCharge) {
        abort(403);
    }

    if (blank($participant->photo_path)) {
        abort(404);
    }

    $disk = $participant->photo_disk ?: 'public';
    $path = $participant->photo_path;

    $publicUrl = app(\App\Services\AppFileStorageService::class)->url($path, $disk);

    if (blank($publicUrl)) {
        abort(404);
    }

    return redirect()->away($publicUrl, 302, [
        'Cache-Control' => 'private, max-age=3600',
        'X-Kicap-Photo-Proxy' => 'fast-redirect',
    ]);
})->name('participant-photo.fast');
// KICAP_PARTICIPANT_PHOTO_FAST_REDIRECT_END


// KICAP_PARTICIPANT_PHOTO_PROXY_V3_START
\Illuminate\Support\Facades\Route::middleware(['auth'])->get('/participant-photo/{participant}/photo', function (
    \Illuminate\Http\Request $request,
    \App\Models\ActivityParticipant $participant
) {
    $user = $request->user();

    if (! $user) {
        abort(403);
    }

    $participant->loadMissing(['lpj.assignedUsers']);

    $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
    $isDirektur = method_exists($user, 'isDirektur') && $user->isDirektur();

    $isAssignedUser = $participant->lpj
        && $participant->lpj->assignedUsers
            ->contains(fn ($assignment): bool => (int) $assignment->user_id === (int) $user->id);

    $isPersonInCharge = $participant->lpj
        && (int) $participant->lpj->person_in_charge_id === (int) $user->id;

    if (! $isAdmin && ! $isDirektur && ! $isAssignedUser && ! $isPersonInCharge) {
        abort(403);
    }

    if (blank($participant->photo_path)) {
        abort(404);
    }

    $disk = $participant->photo_disk ?: 'public';
    $path = $participant->photo_path;
    $mime = $participant->photo_mime_type ?: 'image/jpeg';
    $content = null;

    try {
        $storage = \Illuminate\Support\Facades\Storage::disk($disk);

        if ($storage->exists($path)) {
            $content = $storage->get($path);
            $mime = $participant->photo_mime_type ?: ($storage->mimeType($path) ?: $mime);
        }
    } catch (\Throwable $e) {
        report($e);
    }

    if ($content === null) {
        try {
            $publicUrl = app(\App\Services\AppFileStorageService::class)->url($path, $disk);

            if (filled($publicUrl)) {
                $response = \Illuminate\Support\Facades\Http::timeout(20)
                    ->withHeaders([
                        'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    ])
                    ->get($publicUrl);

                if ($response->successful() && str_starts_with((string) $response->header('content-type'), 'image/')) {
                    $content = $response->body();
                    $mime = (string) $response->header('content-type');
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    if ($content === null) {
        abort(404);
    }

    $fileName = $participant->photo_original_name ?: ('participant-'.$participant->id.'.jpg');

    return response($content, 200)
        ->header('Content-Type', $mime)
        ->header('Content-Disposition', 'inline; filename="'.str_replace('"', '', $fileName).'"')
        ->header('Cache-Control', 'private, max-age=300')
        ->header('X-Content-Type-Options', 'nosniff');
})->name('participant-photo.proxy.v3');
// KICAP_PARTICIPANT_PHOTO_PROXY_V3_END


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

    // KICAP_DIRECTOR_ENDPOINTS_SAFE_01
    Route::get('/api/app/director/events', function (Request $request) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user && method_exists($user, 'isDirektur') && $user->isDirektur() && $user->is_active, 403);

        $statusLabels = [
            'draft' => 'Draft',
            'aktif' => 'Aktif',
            'review' => 'Diajukan',
            'diajukan' => 'Diajukan',
            'submitted' => 'Diajukan',
            'revisi' => 'Perlu Revisi',
            'perlu_revisi' => 'Perlu Revisi',
            'approved' => 'Disetujui',
            'disetujui' => 'Disetujui',
            'finish' => 'Selesai',
            'selesai' => 'Selesai',
        ];

        $events = \Illuminate\Support\Facades\DB::table('lpjs')
            ->leftJoin('lpj_types', 'lpj_types.id', '=', 'lpjs.lpj_type_id')
            ->leftJoin('users as pic', 'pic.id', '=', 'lpjs.person_in_charge_id')
            ->select([
                'lpjs.id',
                'lpjs.code',
                'lpjs.title',
                'lpjs.status',
                'lpjs.start_date',
                'lpjs.end_date',
                'lpjs.location',
                'lpj_types.name as type_name',
                'pic.name as person_in_charge_name',
            ])
            ->orderByDesc('lpjs.id')
            ->get()
            ->map(function ($event) use ($statusLabels): array {
                $participantCount = \Illuminate\Support\Facades\DB::table('activity_participants')
                    ->where('lpj_id', $event->id)
                    ->count();

                $documentationCount = \Illuminate\Support\Facades\DB::table('activity_documentations')
                    ->where('lpj_id', $event->id)
                    ->count();

                $transactionCount = \Illuminate\Support\Facades\DB::table('lpj_financial_transactions')
                    ->where('lpj_id', $event->id)
                    ->count();

                $progress = $event->status === 'finish' || $event->status === 'selesai'
                    ? 100
                    : min(95, 30
                        + ($participantCount > 0 ? 20 : 0)
                        + ($documentationCount > 0 ? 20 : 0)
                        + ($transactionCount > 0 ? 15 : 0));

                return [
                    'id' => $event->id,
                    'code' => $event->code,
                    'title' => $event->title,
                    'type' => $event->type_name,
                    'status' => $event->status,
                    'status_label' => $statusLabels[$event->status] ?? ucfirst((string) $event->status),
                    'start_date' => $event->start_date ? \Illuminate\Support\Carbon::parse($event->start_date)->toDateString() : null,
                    'end_date' => $event->end_date ? \Illuminate\Support\Carbon::parse($event->end_date)->toDateString() : null,
                    'location' => $event->location,
                    'person_in_charge' => $event->person_in_charge_name,
                    'assignment_role' => 'Monitoring',
                    'participant_count' => $participantCount,
                    'documentation_count' => $documentationCount,
                    'transaction_count' => $transactionCount,
                    'progress_label' => 'Monitoring Event',
                    'progress_percentage' => $progress,
                    'latest_transaction' => null,
                ];
            })
            ->values();

        return response()->json(['data' => $events]);
    });

    Route::get('/api/app/director/events/{lpj}', function (Request $request, Lpj $lpj) {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user && method_exists($user, 'isDirektur') && $user->isDirektur() && $user->is_active, 403);

        $event = \Illuminate\Support\Facades\DB::table('lpjs')
            ->leftJoin('lpj_types', 'lpj_types.id', '=', 'lpjs.lpj_type_id')
            ->leftJoin('users as pic', 'pic.id', '=', 'lpjs.person_in_charge_id')
            ->where('lpjs.id', $lpj->id)
            ->select([
                'lpjs.*',
                'lpj_types.name as type_name',
                'lpj_types.slug as type_slug',
                'pic.name as person_in_charge_name',
            ])
            ->first();

        abort_unless($event, 404);

        $documentations = \Illuminate\Support\Facades\DB::table('activity_documentations')
            ->where('lpj_id', $event->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $transactions = \Illuminate\Support\Facades\DB::table('lpj_financial_transactions')
            ->where('lpj_id', $event->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $schedules = \Illuminate\Support\Facades\DB::table('activity_schedules')
            ->where('lpj_id', $event->id)
            ->orderBy('id')
            ->get();

        $participants = \Illuminate\Support\Facades\DB::table('activity_participants')
            ->where('lpj_id', $event->id)
            ->orderBy('id')
            ->limit(100)
            ->get();

        return response()->json([
            'data' => [
                'id' => $event->id,
                'code' => $event->code,
                'title' => $event->title,
                'type' => $event->type_name,
                'type_slug' => $event->type_slug,
                'status' => $event->status,
                'status_label' => ucfirst((string) $event->status),
                'start_date' => $event->start_date ? \Illuminate\Support\Carbon::parse($event->start_date)->toDateString() : null,
                'end_date' => $event->end_date ? \Illuminate\Support\Carbon::parse($event->end_date)->toDateString() : null,
                'location' => $event->location,
                'person_in_charge' => $event->person_in_charge_name,
                'can_input_operational_data' => false,
                'can_submit_review' => false,
                'can_print_report' => in_array($event->status, ['finish', 'selesai'], true),
                'report_print_url' => in_array($event->status, ['finish', 'selesai'], true) ? route('app.lpjs.report.print', $event->id) : null,
                'is_director_view' => true,
                'activity_notes' => [],
                'execution' => [
                    'schedules' => $schedules,
                    'participants' => $participants,
                ],
                'finance' => [
                    'transactions' => $transactions,
                ],
                'documentations' => $documentations,
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

    // KICAP_PARTICIPANT_PHOTO_ADMIN_DETAIL_PROXY_01
    if (filled($participant->photo_path)) {
        $photoUrl = route('participant-photo.fast', $participant);
    }

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

// KICAP_PWA_PROGRESS_TEST_UPDATE_01
Route::post('/api/app/lpjs/{lpj}/selection/participants/{participant}/test-results/{result}', function (
    \Illuminate\Http\Request $request,
    \App\Models\Lpj $lpj,
    \App\Models\ActivityParticipant $participant,
    \App\Models\ActivityParticipantTestResult $result,
    \App\Services\ActivitySelectionPayloadService $selectionService
) {
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
    abort_unless($result->lpj_id === $lpj->id, 404);
    abort_unless($result->activity_participant_id === $participant->id, 404);

    $failed = $participant->testResults()
        ->where('status', \App\Models\ActivityParticipantTestResult::STATUS_FAILED)
        ->with(['stage', 'test'])
        ->join('activity_selection_stages', 'activity_selection_stages.id', '=', 'activity_participant_test_results.activity_selection_stage_id')
        ->join('activity_selection_tests', 'activity_selection_tests.id', '=', 'activity_participant_test_results.activity_selection_test_id')
        ->orderBy('activity_selection_stages.sort_order')
        ->orderBy('activity_selection_tests.sort_order')
        ->select('activity_participant_test_results.*')
        ->first();

    if ($failed && (int) $failed->id !== (int) $result->id) {
        $result->loadMissing(['stage', 'test']);

        $failedOrder = [
            $failed->stage?->sort_order ?? 999999,
            $failed->test?->sort_order ?? 999999,
        ];

        $targetOrder = [
            $result->stage?->sort_order ?? 999999,
            $result->test?->sort_order ?? 999999,
        ];

        if ($targetOrder > $failedOrder) {
            return response()->json([
                'message' => 'Peserta sudah gugur, tes berikutnya terkunci.',
            ], 422);
        }
    }
    $validated = $request->validate([
        'status' => [
            'required',
            \Illuminate\Validation\Rule::in([
                \App\Models\ActivityParticipantTestResult::STATUS_PENDING,
                \App\Models\ActivityParticipantTestResult::STATUS_PASSED,
                \App\Models\ActivityParticipantTestResult::STATUS_PASSED_WITH_NOTE,
                \App\Models\ActivityParticipantTestResult::STATUS_FAILED,
            ]),
        ],
        'note' => ['nullable', 'string', 'max:5000'],
    ]);

    $result->forceFill([
        'status' => $validated['status'],
        'note' => $validated['note'] ?? null,
        'updated_by' => $user->id,
        'assessed_at' => $validated['status'] === \App\Models\ActivityParticipantTestResult::STATUS_PENDING
            ? null
            : now(),
    ])->save();

    app(\App\Services\ActivitySelectionService::class)->recalculateParticipantStatus($participant->fresh());

    return response()->json([
        'data' => [
            'selection' => $selectionService->payload($lpj->fresh(), $user),
        ],
    ]);
})->middleware('auth');

// KICAP_ADMIN_IMPORT_PESERTA_CSV_02
Route::middleware(['auth'])->prefix('admin/tools')->name('admin.tools.')->group(function () {
    $ensureAdmin = function () {
        $user = auth()->user();

        $isAdmin = $user && (
            (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (($user->role ?? null) === 'admin')
        );

        abort_unless($isAdmin, 403);
    };

    $renderImportPage = function (?array $result = null, ?array $errors = null) {
        $lpjs = \App\Models\Lpj::query()
            ->orderByDesc('id')
            ->get(['id', 'title', 'status', 'start_date'])
            ->map(function ($lpj) {
                $date = $lpj->start_date ? ' · '.$lpj->start_date : '';

                return [
                    'id' => $lpj->id,
                    'label' => '#'.$lpj->id.' · '.$lpj->title.$date.' · '.$lpj->status,
                ];
            });

        $csrf = csrf_token();
        $templateUrl = route('admin.tools.import-peserta.template');
        $actionUrl = route('admin.tools.import-peserta.store');

        $options = $lpjs
            ->map(fn ($lpj) => '<option value="'.e($lpj['id']).'">'.e($lpj['label']).'</option>')
            ->implode('');

        $resultHtml = '';

        if ($result) {
            $rows = '';

            foreach (($result['messages'] ?? []) as $message) {
                $rows .= '<li>'.e($message).'</li>';
            }

            $resultHtml = '
                <section class="card result">
                    <h2>Hasil Import</h2>
                    <div class="stats">
                        <div><strong>'.e($result['total_rows'] ?? 0).'</strong><span>Total baris</span></div>
                        <div><strong>'.e($result['created'] ?? 0).'</strong><span>Peserta baru</span></div>
                        <div><strong>'.e($result['updated'] ?? 0).'</strong><span>Diupdate</span></div>
                        <div><strong>'.e($result['skipped'] ?? 0).'</strong><span>Diskip</span></div>
                    </div>
                    '.($rows ? '<ul>'.$rows.'</ul>' : '').'
                </section>';
        }

        $errorHtml = '';

        if ($errors) {
            $items = '';

            foreach ($errors as $error) {
                $items .= '<li>'.e($error).'</li>';
            }

            $errorHtml = '
                <section class="card error">
                    <h2>Perlu dicek</h2>
                    <ul>'.$items.'</ul>
                </section>';
        }

        return response(<<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import Peserta · Kicap Event</title>
    <style>
        body { margin: 0; background: #f8fafc; color: #0f172a; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .wrap { width: min(920px, calc(100vw - 32px)); margin: 32px auto; display: grid; gap: 18px; }
        .top { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; flex-wrap: wrap; }
        h1 { margin: 0; font-size: clamp(1.5rem, 4vw, 2.2rem); letter-spacing: -.04em; }
        h2 { margin-top: 0; }
        p { color: #64748b; line-height: 1.55; }
        .card { background: #fff; border: 1px solid rgba(15,23,42,.08); border-radius: 24px; padding: 20px; box-shadow: 0 18px 48px rgba(15,23,42,.08); }
        form { display: grid; gap: 16px; }
        label { display: grid; gap: 7px; font-weight: 850; color: #334155; }
        select, input[type=file] { width: 100%; box-sizing: border-box; border: 1px solid rgba(15,23,42,.12); border-radius: 16px; background: #fff; padding: 12px 14px; color: #0f172a; font-weight: 750; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        button, a.button { appearance: none; border: 0; border-radius: 999px; padding: 12px 18px; font-weight: 900; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        button { background: #0f766e; color: #fff; }
        a.button { background: rgba(15,118,110,.10); color: #0f766e; }
        a.back { background: rgba(15,23,42,.06); color: #334155; }
        code { background: rgba(15,23,42,.06); padding: 2px 6px; border-radius: 8px; }
        .hint { background: rgba(20,184,166,.09); border-color: rgba(15,118,110,.14); }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin: 12px 0; }
        .stats div { border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 12px; background: #f8fafc; }
        .stats strong { display: block; font-size: 1.4rem; }
        .stats span { color: #64748b; font-weight: 800; font-size: .78rem; }
        .error { border-color: rgba(185,28,28,.22); background: #fff7f7; }
        ul { margin: 10px 0 0; padding-left: 20px; color: #475569; }
        @media (max-width: 560px) { .stats { grid-template-columns: repeat(2, minmax(0,1fr)); } .wrap { margin: 18px auto; } }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="top">
            <div>
                <h1>Import Peserta</h1>
                <p>Upload daftar peserta ke event tertentu. Gunakan CSV dari Excel/Spreadsheet.</p>
            </div>
            <a class="button back" href="/admin">Kembali ke Admin</a>
        </div>

        {$errorHtml}
        {$resultHtml}

        <section class="card hint">
            <strong>Format kolom CSV</strong>
            <p>Kolom yang dikenali: <code>nama_peserta</code> wajib, lalu opsional <code>asal</code>, <code>nomor_peserta</code>, <code>whatsapp</code>, <code>catatan</code>.</p>
            <p>Kalau <code>nomor_peserta</code> sudah ada di event yang sama, data peserta akan diupdate, bukan dibuat dobel.</p>
        </section>

        <section class="card">
            <form method="post" action="{$actionUrl}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{$csrf}">
                <label>
                    Pilih Event
                    <select name="lpj_id" required>
                        <option value="">Pilih event...</option>
                        {$options}
                    </select>
                </label>

                <label>
                    File CSV
                    <input type="file" name="file" accept=".csv,text/csv,text/plain" required>
                </label>

                <div class="actions">
                    <button type="submit">Import Peserta</button>
                    <a class="button" href="{$templateUrl}">Download Template CSV</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
HTML);
    };

    Route::get('/import-peserta', function () use ($ensureAdmin, $renderImportPage) {
        $ensureAdmin();

        return $renderImportPage();
    })->name('import-peserta.index');

    Route::get('/import-peserta/template.csv', function () use ($ensureAdmin) {
        $ensureAdmin();

        $csv = "\xEF\xBB\xBFnama_peserta,asal,nomor_peserta,whatsapp,catatan\nContoh Peserta,SMK Contoh,001,081234567890,Catatan opsional\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_peserta_kicap.csv"',
        ]);
    })->name('import-peserta.template');

    Route::post('/import-peserta', function (\Illuminate\Http\Request $request) use ($ensureAdmin, $renderImportPage) {
        $ensureAdmin();

        $validated = $request->validate([
            'lpj_id' => ['required', 'integer', 'exists:lpjs,id'],
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: '');

        if (! in_array($extension, ['csv', 'txt'], true)) {
            return $renderImportPage(null, ['File harus CSV. Dari Excel, gunakan Save As / Export ke CSV.']);
        }

        $lpj = \App\Models\Lpj::query()->findOrFail($validated['lpj_id']);

        $handle = fopen($file->getRealPath(), 'rb');

        if (! $handle) {
            return $renderImportPage(null, ['File tidak bisa dibaca.']);
        }

        $firstLine = fgets($handle) ?: '';
        rewind($handle);

        $delimiter = ',';
        $bestCount = 0;

        foreach ([',', ';', "\t"] as $candidate) {
            $count = count(str_getcsv($firstLine, $candidate));

            if ($count > $bestCount) {
                $bestCount = $count;
                $delimiter = $candidate;
            }
        }

        $headers = fgetcsv($handle, 0, $delimiter);

        if (! $headers) {
            fclose($handle);

            return $renderImportPage(null, ['Header CSV tidak ditemukan.']);
        }

        $normalize = function ($value) {
            $value = trim((string) $value);
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);

            return \Illuminate\Support\Str::of($value)
                ->lower()
                ->ascii()
                ->replace([' ', '-', '.', '/', '\\'], '_')
                ->replaceMatches('/_+/', '_')
                ->trim('_')
                ->toString();
        };

        $headers = array_map($normalize, $headers);

        $aliases = [
            'name' => ['nama_peserta', 'nama', 'name', 'peserta'],
            'origin' => ['asal', 'asal_lembaga', 'lembaga', 'origin', 'instansi', 'sekolah'],
            'participant_number' => ['nomor_peserta', 'no_peserta', 'nomor', 'no', 'participant_number'],
            'whatsapp' => ['whatsapp', 'wa', 'no_wa', 'hp', 'phone', 'telepon'],
            'note' => ['catatan', 'note', 'keterangan'],
        ];

        $findValue = function (array $row, string $field) use ($headers, $aliases) {
            foreach ($aliases[$field] ?? [] as $alias) {
                $index = array_search($alias, $headers, true);

                if ($index !== false) {
                    return trim((string) ($row[$index] ?? ''));
                }
            }

            return '';
        };

        if (! array_intersect($headers, $aliases['name'])) {
            fclose($handle);

            return $renderImportPage(null, ['Kolom nama_peserta wajib ada.']);
        }

        $schema = \Illuminate\Support\Facades\Schema::getColumnListing('activity_participants');
        $hasColumn = fn (string $column) => in_array($column, $schema, true);

        $result = [
            'total_rows' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'messages' => [],
        ];

        $rowNumber = 1;

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $handle,
            $delimiter,
            $findValue,
            $lpj,
            $hasColumn,
            &$result,
            &$rowNumber
        ) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;
                $result['total_rows']++;

                $name = $findValue($row, 'name');
                $origin = $findValue($row, 'origin');
                $participantNumber = $findValue($row, 'participant_number');
                $whatsapp = $findValue($row, 'whatsapp');
                $note = $findValue($row, 'note');

                if ($name === '' && $origin === '' && $participantNumber === '' && $whatsapp === '' && $note === '') {
                    $result['skipped']++;
                    continue;
                }

                if ($name === '') {
                    $result['skipped']++;
                    $result['messages'][] = 'Baris '.$rowNumber.' diskip: nama_peserta kosong.';
                    continue;
                }

                $query = \App\Models\ActivityParticipant::query()->where('lpj_id', $lpj->id);

                if ($participantNumber !== '') {
                    $query->where('participant_number', $participantNumber);
                } elseif ($whatsapp !== '') {
                    $query->where('name', $name)->where('whatsapp', $whatsapp);
                } else {
                    $query->where('name', $name)->where(function ($q) {
                        $q->whereNull('participant_number')->orWhere('participant_number', '');
                    });
                }

                $participant = $query->first();

                $data = [
                    'lpj_id' => $lpj->id,
                    'name' => $name,
                ];

                if ($hasColumn('origin')) {
                    $data['origin'] = $origin ?: null;
                }

                if ($hasColumn('participant_number')) {
                    $data['participant_number'] = $participantNumber ?: null;
                }

                if ($hasColumn('whatsapp')) {
                    $data['whatsapp'] = $whatsapp ?: null;
                }

                if ($hasColumn('note')) {
                    $data['note'] = $note ?: null;
                }

                if ($hasColumn('created_by') && ! $participant) {
                    $data['created_by'] = auth()->id();
                }

                if ($hasColumn('updated_by')) {
                    $data['updated_by'] = auth()->id();
                }

                if ($hasColumn('attendance_status') && ! $participant) {
                    $data['attendance_status'] = 'hadir';
                }

                if ($participant) {
                    $participant->forceFill($data)->save();
                    $result['updated']++;
                } else {
                    $participant = new \App\Models\ActivityParticipant();
                    $participant->forceFill($data)->save();
                    $result['created']++;
                }

                $service = app(\App\Services\ActivitySelectionService::class);

                if (method_exists($service, 'recalculateParticipantStatus')) {
                    try {
                        $service->recalculateParticipantStatus($participant->fresh());
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }
            }
        });

        fclose($handle);

        $result['messages'][] = 'Import selesai untuk event: '.$lpj->title.'.';

        return $renderImportPage($result);
    })->name('import-peserta.store');
});



// KICAP_PWA_RUNTIME_POLISH_ROUTES_START
Route::get('/kicap-pwa-reset.html', function () {
    return response(file_get_contents(public_path('kicap-pwa-reset.html')), 200)
        ->header('Content-Type', 'text/html; charset=UTF-8')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
});

Route::get('/kicap-pwa-reset', function () {
    return response(file_get_contents(public_path('kicap-pwa-reset.html')), 200)
        ->header('Content-Type', 'text/html; charset=UTF-8')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
});

Route::get('/kicap-pwa-runtime-polish.css', function () {
    return response(file_get_contents(public_path('kicap-pwa-runtime-polish.css')), 200)
        ->header('Content-Type', 'text/css; charset=UTF-8')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
});

Route::get('/kicap-pwa-runtime-polish.js', function () {
    return response(file_get_contents(public_path('kicap-pwa-runtime-polish.js')), 200)
        ->header('Content-Type', 'application/javascript; charset=UTF-8')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
});
// KICAP_PWA_RUNTIME_POLISH_ROUTES_END


// KICAP_ACTIVITY_HISTORY_API_START
\Illuminate\Support\Facades\Route::middleware(['auth'])->get('/api/app/activity-history', function () {
    $user = auth()->user();

    if (! $user || ! method_exists($user, 'canAccessPwa') || ! $user->canAccessPwa()) {
        abort(403);
    }

    $time = function ($value): ?string {
        if (! $value) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    };

    $money = function ($value): string {
        $number = (float) ($value ?? 0);

        if ($number <= 0) {
            return '';
        }

        return 'Rp ' . number_format($number, 0, ',', '.');
    };

    $status = function ($value): string {
        if (! $value) {
            return '';
        }

        return \Illuminate\Support\Str::of((string) $value)
            ->replace('_', ' ')
            ->title()
            ->toString();
    };

    $label = function ($value): string {
        if (! $value) {
            return '';
        }

        return \Illuminate\Support\Str::of((string) $value)
            ->replace('_', ' ')
            ->title()
            ->toString();
    };

    $activities = [];

    $push = function (array $activity) use (&$activities): void {
        if (empty($activity['at'])) {
            return;
        }

        $activities[] = [
            'id' => $activity['id'] ?? ('activity-' . count($activities)),
            'icon' => $activity['icon'] ?? '•',
            'title' => $activity['title'] ?? 'Aktivitas user',
            'subtitle' => $activity['subtitle'] ?? 'Event',
            'meta' => $activity['meta'] ?? '',
            'at' => $activity['at'],
        ];
    };

    $lpjs = \App\Models\Lpj::query()
        ->visibleToAssignedUser($user)
        ->select(['id', 'title', 'status'])
        ->get();

    $lpjIds = $lpjs->pluck('id')->all();
    $lpjTitles = $lpjs->pluck('title', 'id');

    if (empty($lpjIds)) {
        return response()->json([
            'data' => [
                'activities' => [],
            ],
        ]);
    }

    $eventTitle = function ($lpjId) use ($lpjTitles): string {
        return (string) ($lpjTitles[$lpjId] ?? 'Event');
    };

    // Transaksi user terkait
    \App\Models\LpjFinancialTransaction::query()
        ->whereIn('lpj_id', $lpjIds)
        ->where('user_id', $user->id)
        ->latest('created_at')
        ->get()
        ->each(function ($transaction) use ($push, $eventTitle, $time, $money, $status): void {
            $isAdvance = $transaction->source_type === \App\Models\LpjFinancialTransaction::SOURCE_ADVANCE;

            $push([
                'id' => 'transaction-' . $transaction->id,
                'icon' => $isAdvance ? '🧾' : '💸',
                'title' => $isAdvance ? 'Mencatat dana talangan' : 'Mencatat pengeluaran',
                'subtitle' => $eventTitle($transaction->lpj_id),
                'meta' => collect([
                    $transaction->description ?: $transaction->category,
                    $money($transaction->amount),
                    $transaction->status ? 'Status ' . $status($transaction->status) : null,
                ])->filter()->implode(' • '),
                'at' => $time($transaction->created_at ?? $transaction->spent_at),
            ]);
        });

    // Transfer/mutasi saldo milik user terkait
    \App\Models\LpjBalanceMutation::query()
        ->with(['relatedUser:id,name'])
        ->whereIn('lpj_id', $lpjIds)
        ->where('user_id', $user->id)
        ->whereIn('type', [
            \App\Models\LpjBalanceMutation::TYPE_TRANSFER_IN,
            \App\Models\LpjBalanceMutation::TYPE_TRANSFER_OUT,
        ])
        ->latest('occurred_at')
        ->get()
        ->each(function ($mutation) use ($push, $eventTitle, $time, $money): void {
            $isOut = $mutation->type === \App\Models\LpjBalanceMutation::TYPE_TRANSFER_OUT;
            $relatedName = $mutation->relatedUser?->name;

            $push([
                'id' => 'transfer-' . $mutation->id,
                'icon' => '⇄',
                'title' => $isOut ? 'Transfer saldo keluar' : 'Transfer saldo masuk',
                'subtitle' => $eventTitle($mutation->lpj_id),
                'meta' => collect([
                    $relatedName ? ($isOut ? 'Ke ' . $relatedName : 'Dari ' . $relatedName) : null,
                    $money($mutation->amount),
                    $mutation->note,
                ])->filter()->implode(' • '),
                'at' => $time($mutation->occurred_at ?? $mutation->created_at),
            ]);
        });

    // Klaim dana talangan milik user
    \App\Models\LpjAdvanceClaim::query()
        ->whereIn('lpj_id', $lpjIds)
        ->where('user_id', $user->id)
        ->latest('created_at')
        ->get()
        ->each(function ($claim) use ($push, $eventTitle, $time, $money, $status): void {
            $push([
                'id' => 'advance-claim-' . $claim->id,
                'icon' => '🧾',
                'title' => 'Klaim dana talangan',
                'subtitle' => $eventTitle($claim->lpj_id),
                'meta' => collect([
                    $money($claim->amount),
                    $claim->status ? 'Status ' . $status($claim->status) : null,
                ])->filter()->implode(' • '),
                'at' => $time($claim->created_at),
            ]);
        });

    // Dokumentasi yang diupload user terkait
    \App\Models\ActivityDocumentation::query()
        ->whereIn('lpj_id', $lpjIds)
        ->where('uploaded_by', $user->id)
        ->latest('created_at')
        ->get()
        ->each(function ($documentation) use ($push, $eventTitle, $time, $label): void {
            $push([
                'id' => 'documentation-' . $documentation->id,
                'icon' => '📷',
                'title' => 'Upload dokumentasi',
                'subtitle' => $eventTitle($documentation->lpj_id),
                'meta' => collect([
                    $documentation->caption ?: $label($documentation->category),
                    $documentation->include_in_report ? 'Masuk LPJ' : 'Internal',
                ])->filter()->implode(' • '),
                'at' => $time($documentation->created_at ?? $documentation->updated_at),
            ]);
        });

    // Lampiran yang diupload user terkait
    \App\Models\ActivityAttachment::query()
        ->whereIn('lpj_id', $lpjIds)
        ->where('uploaded_by', $user->id)
        ->latest('created_at')
        ->get()
        ->each(function ($attachment) use ($push, $eventTitle, $time): void {
            $push([
                'id' => 'attachment-' . $attachment->id,
                'icon' => '📎',
                'title' => 'Upload lampiran',
                'subtitle' => $eventTitle($attachment->lpj_id),
                'meta' => collect([
                    $attachment->title ?: $attachment->original_name,
                    $attachment->include_in_report ? 'Masuk LPJ' : 'Internal',
                ])->filter()->implode(' • '),
                'at' => $time($attachment->created_at ?? $attachment->updated_at),
            ]);
        });

    // Catatan milik user terkait
    \App\Models\ActivityNote::query()
        ->whereIn('lpj_id', $lpjIds)
        ->where('user_id', $user->id)
        ->latest('created_at')
        ->get()
        ->each(function ($note) use ($push, $eventTitle, $time, $label): void {
            $push([
                'id' => 'note-' . $note->id,
                'icon' => '📝',
                'title' => 'Membuat catatan',
                'subtitle' => $eventTitle($note->lpj_id),
                'meta' => collect([
                    $label($note->type),
                    $note->content,
                    $note->include_in_report ? 'Masuk LPJ' : null,
                ])->filter()->implode(' • '),
                'at' => $time($note->created_at ?? $note->updated_at),
            ]);
        });

    // Peserta hanya jika dibuat/diupdate user terkait
    if (class_exists(\App\Models\ActivityParticipant::class)) {
        $participantQuery = \App\Models\ActivityParticipant::query()
            ->whereIn('lpj_id', $lpjIds);

        $participantQuery->where(function ($query) use ($user): void {
            $hasCreatedBy = \Illuminate\Support\Facades\Schema::hasColumn('activity_participants', 'created_by');
            $hasUpdatedBy = \Illuminate\Support\Facades\Schema::hasColumn('activity_participants', 'updated_by');

            if ($hasCreatedBy) {
                $query->orWhere('created_by', $user->id);
            }

            if ($hasUpdatedBy) {
                $query->orWhere('updated_by', $user->id);
            }
        });

        $participantQuery->latest('updated_at')
            ->get()
            ->each(function ($participant) use ($push, $eventTitle, $time, $status): void {
                $push([
                    'id' => 'participant-' . $participant->id,
                    'icon' => '👥',
                    'title' => 'Update data peserta',
                    'subtitle' => $eventTitle($participant->lpj_id),
                    'meta' => collect([
                        $participant->name,
                        $participant->attendance_status ? 'Kehadiran ' . $status($participant->attendance_status) : null,
                        $participant->result_status ? 'Status ' . $status($participant->result_status) : null,
                    ])->filter()->implode(' • '),
                    'at' => $time($participant->updated_at ?? $participant->created_at),
                ]);
            });
    }

    // Rundown/pelaksanaan hanya jika dibuat/diupdate user terkait
    if (class_exists(\App\Models\ActivitySchedule::class)) {
        $scheduleQuery = \App\Models\ActivitySchedule::query()
            ->whereIn('lpj_id', $lpjIds);

        $scheduleQuery->where(function ($query) use ($user): void {
            $hasCreatedBy = \Illuminate\Support\Facades\Schema::hasColumn('activity_schedules', 'created_by');
            $hasUpdatedBy = \Illuminate\Support\Facades\Schema::hasColumn('activity_schedules', 'updated_by');
            $hasStatusUpdatedBy = \Illuminate\Support\Facades\Schema::hasColumn('activity_schedules', 'status_updated_by');

            if ($hasCreatedBy) {
                $query->orWhere('created_by', $user->id);
            }

            if ($hasUpdatedBy) {
                $query->orWhere('updated_by', $user->id);
            }

            if ($hasStatusUpdatedBy) {
                $query->orWhere('status_updated_by', $user->id);
            }
        });

        $scheduleQuery->latest('updated_at')
            ->get()
            ->each(function ($schedule) use ($push, $eventTitle, $time, $status): void {
                $push([
                    'id' => 'schedule-' . $schedule->id,
                    'icon' => '✅',
                    'title' => 'Update rundown/pelaksanaan',
                    'subtitle' => $eventTitle($schedule->lpj_id),
                    'meta' => collect([
                        $schedule->activity_name,
                        isset($schedule->status) && $schedule->status ? 'Status ' . $status($schedule->status) : null,
                    ])->filter()->implode(' • '),
                    'at' => $time($schedule->updated_at ?? $schedule->created_at),
                ]);
            });
    }

    // Hasil tes peserta hanya jika dibuat/diupdate user terkait
    if (class_exists(\App\Models\ActivityParticipantTestResult::class)) {
        \App\Models\ActivityParticipantTestResult::query()
            ->with(['participant:id,lpj_id,name'])
            ->where(function ($query) use ($user): void {
                $query
                    ->where('created_by', $user->id)
                    ->orWhere('updated_by', $user->id);
            })
            ->latest('updated_at')
            ->get()
            ->filter(fn ($result) => $result->participant && in_array($result->participant->lpj_id, $lpjIds, true))
            ->each(function ($result) use ($push, $eventTitle, $time, $status): void {
                $push([
                    'id' => 'test-result-' . $result->id,
                    'icon' => '✅',
                    'title' => 'Update hasil tes peserta',
                    'subtitle' => $eventTitle($result->participant->lpj_id),
                    'meta' => collect([
                        $result->participant?->name,
                        isset($result->status) && $result->status ? 'Status ' . $status($result->status) : null,
                    ])->filter()->implode(' • '),
                    'at' => $time($result->updated_at ?? $result->created_at),
                ]);
            });
    }

    usort($activities, function ($left, $right): int {
        return strtotime($right['at']) <=> strtotime($left['at']);
    });

    return response()->json([
        'data' => [
            'activities' => array_slice($activities, 0, 200),
        ],
    ]);
});
// KICAP_ACTIVITY_HISTORY_API_END


// KICAP_PARTICIPANT_PHOTO_PROXY_START
\Illuminate\Support\Facades\Route::middleware(['auth'])->get('/app/participant-photos/{participant}/photo', function (
    \Illuminate\Http\Request $request,
    \App\Models\ActivityParticipant $participant
) {
    $user = $request->user();

    if (! $user) {
        abort(403);
    }

    $participant->loadMissing(['lpj.assignedUsers']);

    $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
    $isDirektur = method_exists($user, 'isDirektur') && $user->isDirektur();

    $isAssignedUser = $participant->lpj
        && $participant->lpj->assignedUsers
            ->contains(fn ($assignment): bool => (int) $assignment->user_id === (int) $user->id);

    $isPersonInCharge = $participant->lpj
        && (int) $participant->lpj->person_in_charge_id === (int) $user->id;

    if (! $isAdmin && ! $isDirektur && ! $isAssignedUser && ! $isPersonInCharge) {
        abort(403);
    }

    if (blank($participant->photo_path)) {
        abort(404);
    }

    $disk = $participant->photo_disk ?: 'public';
    $path = $participant->photo_path;

    if (! \Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
        abort(404);
    }

    $mime = $participant->photo_mime_type
        ?: \Illuminate\Support\Facades\Storage::disk($disk)->mimeType($path)
        ?: 'image/jpeg';

    $stream = \Illuminate\Support\Facades\Storage::disk($disk)->readStream($path);

    if (! is_resource($stream)) {
        abort(404);
    }

    $fileName = $participant->photo_original_name ?: ('participant-'.$participant->id.'.jpg');

    return response()->stream(function () use ($stream): void {
        fpassthru($stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }, 200, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="'.addslashes($fileName).'"',
        'Cache-Control' => 'private, max-age=300',
        'X-Content-Type-Options' => 'nosniff',
    ]);
})->name('app.participant-photo.show');
// KICAP_PARTICIPANT_PHOTO_PROXY_END

// KICAP_ADMIN_USER_AVATAR_PROXY_V1
// Proxy avatar user untuk Admin/Filament.
// Dipakai agar avatar yang tersimpan di R2/private storage tetap bisa tampil aman di tabel/detail Admin.
Route::get('/admin/user-avatar/{user}/avatar', function (\App\Models\User $user) {
    $viewer = auth()->user();

    abort_unless($viewer, 403);

    $viewerRole = (string) ($viewer->role ?? '');
    abort_unless($viewerRole === 'admin' || (int) $viewer->id === (int) $user->id, 403);

    $attributes = $user->getAttributes();

    $candidateColumns = [
        'avatar_path',
        'profile_photo_path',
        'photo_path',
        'profile_image_path',
        'image_path',
        'avatar',
        'photo',
    ];

    $rawPath = null;

    foreach ($candidateColumns as $column) {
        $value = $attributes[$column] ?? null;

        if (is_string($value) && trim($value) !== '') {
            $rawPath = trim($value);
            break;
        }
    }

    if (! $rawPath) {
        foreach (['avatar_url', 'profile_photo_url', 'photo_url'] as $computedColumn) {
            try {
                $value = $user->{$computedColumn} ?? null;

                if (is_string($value) && trim($value) !== '' && ! str_contains($value, '/admin/user-avatar/')) {
                    $rawPath = trim($value);
                    break;
                }
            } catch (\Throwable $e) {
                // Abaikan accessor yang error.
            }
        }
    }

    $fallback = function () use ($user) {
        $name = trim((string) ($user->name ?: $user->email ?: 'User'));
        $parts = preg_split('/\s+/', $name) ?: [];
        $initials = '';

        foreach ($parts as $part) {
            if ($part !== '') {
                $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            }

            if (mb_strlen($initials) >= 2) {
                break;
            }
        }

        if ($initials === '') {
            $initials = 'U';
        }

        $escapedInitials = e($initials);
        $escapedName = e($name);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256" role="img" aria-label="{$escapedName}">
  <rect width="256" height="256" rx="128" fill="#e2e8f0"/>
  <circle cx="128" cy="96" r="48" fill="#1d4ed8"/>
  <path d="M48 226c13-49 47-77 80-77s67 28 80 77" fill="#14b8a6"/>
  <text x="128" y="235" text-anchor="middle" font-family="Arial, sans-serif" font-size="36" font-weight="700" fill="#0f172a">{$escapedInitials}</text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'private, max-age=300',
        ]);
    };

    if (! $rawPath) {
        return $fallback();
    }

    if (preg_match('/^https?:\/\//i', $rawPath)) {
        return redirect()->away($rawPath);
    }

    $cleanPath = ltrim($rawPath, '/');

    $candidates = array_values(array_unique(array_filter([
        $cleanPath,
        preg_replace('#^storage/#', '', $cleanPath),
        preg_replace('#^public/#', '', $cleanPath),
        preg_replace('#^app/public/#', '', $cleanPath),
        preg_replace('#^private/#', '', $cleanPath),
    ])));

    $diskNames = array_values(array_unique(array_filter([
        'r2',
        's3',
        config('filesystems.default'),
        'public',
        'local',
    ])));

    foreach ($diskNames as $diskName) {
        if (! is_string($diskName) || $diskName === '' || ! config("filesystems.disks.{$diskName}")) {
            continue;
        }

        try {
            $disk = \Illuminate\Support\Facades\Storage::disk($diskName);

            foreach ($candidates as $candidate) {
                if (! is_string($candidate) || trim($candidate) === '') {
                    continue;
                }

                if (! $disk->exists($candidate)) {
                    continue;
                }

                $mime = $disk->mimeType($candidate) ?: 'image/jpeg';
                $stream = $disk->readStream($candidate);

                if (is_resource($stream)) {
                    return response()->stream(function () use ($stream) {
                        fpassthru($stream);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }, 200, [
                        'Content-Type' => $mime,
                        'Cache-Control' => 'private, max-age=300',
                    ]);
                }

                return response($disk->get($candidate), 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'private, max-age=300',
                ]);
            }
        } catch (\Throwable $e) {
            continue;
        }
    }

    return $fallback();
})->middleware(['web', 'auth'])->name('admin.user-avatar.proxy');

// KICAP_ADMIN_EVENT_DETAIL_CUSTOM_V1
// Custom detail Event untuk Admin. Lebih aman daripada Filament ViewRecord karena hanya render blade sederhana.
Route::get('/admin/lpjs/{lpj}/detail', function (\App\Models\Lpj $lpj) {
    $viewer = auth()->user();

    abort_unless($viewer, 403);

    $role = strtolower((string) ($viewer->role ?? ''));
    abort_unless(in_array($role, ['admin', 'direktur', 'director'], true), 403);

    $lpjId = (int) $lpj->id;

    $hasTable = fn (string $table): bool => \Illuminate\Support\Facades\Schema::hasTable($table);
    $hasColumn = fn (string $table, string $column): bool => $hasTable($table) && \Illuminate\Support\Facades\Schema::hasColumn($table, $column);

    $safeCount = function (string $table) use ($lpjId, $hasColumn): int {
        try {
            if (! $hasColumn($table, 'lpj_id')) {
                return 0;
            }

            return (int) \Illuminate\Support\Facades\DB::table($table)->where('lpj_id', $lpjId)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    };

    $safeSum = function (array $tables, string $amountColumn = 'amount', ?string $statusColumn = null, ?string $statusValue = null) use ($lpjId, $hasColumn): float {
        foreach ($tables as $table) {
            try {
                if (! $hasColumn($table, 'lpj_id') || ! $hasColumn($table, $amountColumn)) {
                    continue;
                }

                $query = \Illuminate\Support\Facades\DB::table($table)->where('lpj_id', $lpjId);

                if ($statusColumn && $statusValue && $hasColumn($table, $statusColumn)) {
                    $query->where($statusColumn, $statusValue);
                }

                return (float) $query->sum($amountColumn);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return 0;
    };

    $typeName = '-';
    try {
        if (! empty($lpj->lpj_type_id) && $hasTable('lpj_types')) {
            $typeName = (string) (\Illuminate\Support\Facades\DB::table('lpj_types')->where('id', $lpj->lpj_type_id)->value('name') ?: '-');
        }
    } catch (\Throwable $e) {
        $typeName = '-';
    }

    $picName = '-';
    try {
        if (! empty($lpj->person_in_charge_id) && $hasTable('users')) {
            $picName = (string) (\Illuminate\Support\Facades\DB::table('users')->where('id', $lpj->person_in_charge_id)->value('name') ?: '-');
        }
    } catch (\Throwable $e) {
        $picName = '-';
    }

    $assignedUsers = collect();
    try {
        if ($hasColumn('lpj_assigned_users', 'lpj_id') && $hasColumn('lpj_assigned_users', 'user_id') && $hasTable('users')) {
            $assignedUsers = \Illuminate\Support\Facades\DB::table('lpj_assigned_users')
                ->leftJoin('users', 'users.id', '=', 'lpj_assigned_users.user_id')
                ->where('lpj_assigned_users.lpj_id', $lpjId)
                ->select([
                    'users.id',
                    'users.name',
                    'users.username',
                    'users.email',
                    'users.role',
                    'lpj_assigned_users.role_label',
                ])
                ->orderBy('users.name')
                ->get();
        }
    } catch (\Throwable $e) {
        $assignedUsers = collect();
    }

    $latestNotes = collect();
    try {
        if ($hasColumn('activity_notes', 'lpj_id')) {
            $latestNotes = \Illuminate\Support\Facades\DB::table('activity_notes')
                ->leftJoin('users', 'users.id', '=', 'activity_notes.user_id')
                ->where('activity_notes.lpj_id', $lpjId)
                ->select([
                    'activity_notes.type',
                    'activity_notes.content',
                    'activity_notes.created_at',
                    'users.name as user_name',
                ])
                ->orderByDesc('activity_notes.created_at')
                ->limit(5)
                ->get();
        }
    } catch (\Throwable $e) {
        $latestNotes = collect();
    }

    $fundsReceived = (float) ($lpj->total_funds_received ?: 0);
    if ($fundsReceived <= 0) {
        $fundsReceived = $safeSum(['lpj_fund_receipts', 'lpj_funds']);
    }

    $validExpense = (float) ($lpj->total_valid_expense ?: 0);
    if ($validExpense <= 0) {
        $validExpense = $safeSum(['lpj_financial_transactions', 'lpj_transactions'], 'amount', 'status', 'valid');
    }

    $remainingFund = $lpj->total_remaining_fund;
    if ($remainingFund === null || $remainingFund === '') {
        $remainingFund = $fundsReceived - $validExpense;
    }

    $stats = [
        'participants' => $safeCount('activity_participants'),
        'documentations' => $safeCount('activity_documentations'),
        'notes' => $safeCount('activity_notes'),
        'schedules' => $safeCount('activity_schedules'),
        'assigned_users' => $assignedUsers->count(),
        'funds_received' => $fundsReceived,
        'valid_expense' => $validExpense,
        'remaining_fund' => (float) $remainingFund,
    ];

    return view('admin.lpjs.detail', [
        'lpj' => $lpj,
        'typeName' => $typeName,
        'picName' => $picName,
        'assignedUsers' => $assignedUsers,
        'latestNotes' => $latestNotes,
        'stats' => $stats,
    ]);
})->middleware(['web', 'auth'])->name('admin.lpjs.detail');

// KICAP_LPJ_REPORT_MEDIA_ROUTE_V6_BEGIN
\Illuminate\Support\Facades\Route::get('/lpj-report-media/{path}', function (string $path) {
    $raw = rawurldecode($path);
    $raw = trim(str_replace('\\', '/', $raw));
    $raw = ltrim($raw, '/');

    if ($raw === '' || str_contains($raw, '..')) {
        abort(404);
    }

    $clean = $raw;

    foreach ([
        'storage/app/public/',
        'storage/app/private/',
        'storage/app/',
        'public/storage/',
        'storage/',
        'public/',
        'private/',
        'app/public/',
        'app/private/',
        'app/',
    ] as $prefix) {
        if (str_starts_with($clean, $prefix)) {
            $clean = substr($clean, strlen($prefix));
        }
    }

    $makeResponse = function (string $body, ?string $mime = null) {
        $mime = $mime ?: 'image/jpeg';

        return response($body, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    };

    $fileResponse = function (string $file) {
        $mime = 'image/jpeg';

        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($file);
            if ($detected) {
                $mime = $detected;
            }
        }

        return response()->file($file, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    };

    $variants = [$raw, $clean];

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('storage_settings')) {
            $setting = \Illuminate\Support\Facades\DB::table('storage_settings')->orderByDesc('id')->first();

            if ($setting) {
                $rootPrefix = trim((string) ($setting->root_prefix ?? ''), '/');

                if ($rootPrefix !== '') {
                    if ($clean === $rootPrefix) {
                        $withoutRoot = '';
                    } elseif (str_starts_with($clean, $rootPrefix . '/')) {
                        $withoutRoot = substr($clean, strlen($rootPrefix) + 1);
                    } else {
                        $withoutRoot = $clean;
                    }

                    if ($withoutRoot !== '') {
                        $variants[] = $withoutRoot;
                        $variants[] = $rootPrefix . '/' . $withoutRoot;
                    }

                    $variants[] = $rootPrefix . '/' . basename($clean);
                }
            }
        }
    } catch (\Throwable $e) {
        // Abaikan, lanjut kandidat lain.
    }

    $variants[] = 'public/' . $clean;
    $variants[] = 'private/' . $clean;
    $variants[] = basename($clean);

    $variants = array_values(array_unique(array_filter(array_map(
        fn ($item) => ltrim(str_replace('\\', '/', (string) $item), '/'),
        $variants
    ))));

    // 1) Tetap cek lokal dulu, untuk kompatibilitas.
    $localCandidates = [];

    foreach ($variants as $variant) {
        $localCandidates[] = public_path($variant);
        $localCandidates[] = public_path('storage/' . $variant);
        $localCandidates[] = storage_path('app/' . $variant);
        $localCandidates[] = storage_path('app/public/' . $variant);
        $localCandidates[] = storage_path('app/private/' . $variant);
    }

    foreach (array_unique($localCandidates) as $file) {
        if ($file && is_file($file) && is_readable($file)) {
            return $fileResponse($file);
        }
    }

    // 2) Baca Cloudflare R2 dari database storage_settings.
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('storage_settings')) {
            $setting = \Illuminate\Support\Facades\DB::table('storage_settings')->orderByDesc('id')->first();

            if (
                $setting
                && ($setting->provider ?? null) === 'r2'
                && ! empty($setting->r2_access_key_id)
                && ! empty($setting->r2_secret_access_key)
                && ! empty($setting->r2_bucket)
                && ! empty($setting->r2_endpoint)
            ) {
                $disk = \Illuminate\Support\Facades\Storage::build([
                    'driver' => 's3',
                    'key' => $setting->r2_access_key_id,
                    'secret' => $setting->r2_secret_access_key,
                    'region' => 'auto',
                    'bucket' => $setting->r2_bucket,
                    'endpoint' => rtrim($setting->r2_endpoint, '/'),
                    'use_path_style_endpoint' => true,
                    'throw' => false,
                ]);

                foreach ($variants as $variant) {
                    try {
                        if (! $disk->exists($variant)) {
                            continue;
                        }

                        $mime = 'image/jpeg';

                        try {
                            $detected = $disk->mimeType($variant);
                            if ($detected) {
                                $mime = $detected;
                            }
                        } catch (\Throwable $e) {
                            // Abaikan mime error.
                        }

                        return $makeResponse($disk->get($variant), $mime);
                    } catch (\Throwable $e) {
                        // Coba variant berikutnya.
                    }
                }

                // Fallback redirect ke public URL R2/custom domain jika tersedia.
                if (! empty($setting->r2_public_url)) {
                    $publicBase = rtrim($setting->r2_public_url, '/');

                    foreach ($variants as $variant) {
                        $encoded = collect(explode('/', $variant))
                            ->map(fn ($part) => rawurlencode($part))
                            ->implode('/');

                        // Redirect kandidat paling masuk akal pertama.
                        return redirect()->away($publicBase . '/' . $encoded);
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        // Abaikan, lanjut Laravel disk config biasa.
    }

    // 3) Fallback semua disk Laravel yang terdaftar.
    foreach (array_keys(config('filesystems.disks') ?: []) as $diskName) {
        try {
            $disk = \Illuminate\Support\Facades\Storage::disk($diskName);

            foreach ($variants as $variant) {
                try {
                    if (! $disk->exists($variant)) {
                        continue;
                    }

                    $mime = 'image/jpeg';

                    try {
                        $detected = $disk->mimeType($variant);
                        if ($detected) {
                            $mime = $detected;
                        }
                    } catch (\Throwable $e) {
                        // Abaikan.
                    }

                    return $makeResponse($disk->get($variant), $mime);
                } catch (\Throwable $e) {
                    // Coba berikutnya.
                }
            }
        } catch (\Throwable $e) {
            // Disk tidak tersedia.
        }
    }

    abort(404, 'LPJ media not found: ' . $clean);
})->where('path', '.*')->name('kicap.lpj.report.media');
// KICAP_LPJ_REPORT_MEDIA_ROUTE_V6_END


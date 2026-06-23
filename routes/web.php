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
            $proofUrl = \Illuminate\Support\Facades\Storage::disk($proofDisk)->url($transaction->proof_path);
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

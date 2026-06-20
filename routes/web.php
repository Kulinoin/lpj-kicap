<?php

use App\Models\ActivityNote;
use App\Models\Lpj;
use App\Models\LpjType;
use App\Models\User;
use App\Services\ActivityNoteService;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

Route::get('/', fn () => redirect('/app'));

Route::get('/login', fn () => redirect(Filament::getLoginUrl()))->name('login');

Route::get('/health', function () {
    try {
        DB::select('select 1 as ok');

        return response()->json([
            'ok' => true,
            'app' => 'Kicap LPJ',
            'slice' => '00',
            'database' => 'ok',
            'timezone' => config('app.timezone'),
        ]);
    } catch (Throwable $exception) {
        return response()->json([
            'ok' => false,
            'app' => 'Kicap LPJ',
            'slice' => '00',
            'database' => 'error',
            'message' => $exception->getMessage(),
        ], 500);
    }
});

Route::middleware('auth')->group(function (): void {
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
            ->with(['type:id,name', 'personInCharge:id,name'])
            ->latest()
            ->get()
            ->map(fn (Lpj $lpj): array => [
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
            ]);

        return response()->json(['data' => $lpjs]);
    });

    Route::get('/api/app/lpjs/{lpj}', function (Request $request, Lpj $lpj, ActivityNoteService $activityNoteService) {
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
                'activity_notes' => $activityNoteService->payload($activityNotes),
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
                    'include_in_report' => false,
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
                'avatar_url' => $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : null,
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
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'whatsapp' => $validated['whatsapp'] ?? null,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $payload['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
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
                'avatar_url' => $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : null,
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

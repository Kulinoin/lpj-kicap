<?php

use App\Models\Lpj;
use App\Models\LpjType;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

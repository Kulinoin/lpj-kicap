<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/app'));

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

Route::view('/app/{any?}', 'app')->where('any', '.*');
